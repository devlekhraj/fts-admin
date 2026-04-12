<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Foundation\Shared\Application\Contracts\ImageConverter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CopyProductMediaToFilesCommand extends Command
{
    private const PRODUCT_MODEL_TYPE = 'Jed\Ecommerce\App\Product';
    private const TARGET_FOLDER = 'products';
    private const TARGET_USAGE_TYPE = 'products';

    protected $signature = 'media:copy-products-only
        {--chunk=500 : Chunk size for processing}
        {--dry-run : Show what would be copied without writing data}';

    protected $description = 'Copy records from media table to files table for products only';

    public function __construct(
        private readonly ImageConverter $imageConverter
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! Schema::hasTable('media') || ! Schema::hasTable('files') || ! Schema::hasTable('file_usages')) {
            $this->error('Required tables (media, files, or file_usages) do not exist.');

            return self::FAILURE;
        }

        $chunkSize = max(1, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');
        $cdnRoot = trim((string) (config('filesystems.disks.cdn.root') ?? env('CDN_ROOT', '')));

        if ($cdnRoot === '') {
            $this->error('CDN root is not configured. Set filesystems.disks.cdn.root (or CDN_ROOT in .env).');

            return self::FAILURE;
        }
        $cdnRoot = rtrim($cdnRoot, DIRECTORY_SEPARATOR);

        $baseQuery = DB::table('media')
            ->select([
                'id',
                'model_type',
                'model_id',
                'uuid',
                'collection_name',
                'name',
                'file_name',
                'mime_type',
                'disk',
                'conversions_disk',
                'size',
                'manipulations',
                'custom_properties',
                'generated_conversions',
                'responsive_images',
                'order_column',
                'created_at',
                'updated_at',
            ])
            ->where('model_type', self::PRODUCT_MODEL_TYPE)
            ->orderBy('id');

        $total = (clone $baseQuery)->count();
        if ($total === 0) {
            $this->info('No product media records found in media table.');

            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Processing %d product media rows (chunk: %d)%s',
            $total,
            $chunkSize,
            $dryRun ? ' [dry-run]' : ''
        ));

        $processed = 0;
        $created = 0;
        $updated = 0;
        $copied = 0;
        $missing = 0;
        $usageCreatedOrUpdated = 0;

        $baseQuery->chunkById($chunkSize, function ($rows) use (
            &$processed,
            &$created,
            &$updated,
            &$copied,
            &$missing,
            &$usageCreatedOrUpdated,
            $dryRun,
            $cdnRoot
        ): void {
            $fileNames = [];
            $mediaByFileName = [];
            foreach ($rows as $row) {
                $sourceFileName = $this->makeSourceFileName($row);
                $sourcePath = $this->makeSourcePath((int) $row->id, $sourceFileName);
                $convertToWebp = $this->shouldConvertToWebp($sourcePath, (string) ($row->mime_type ?? ''));
                $fileName = $this->makeFileName($row, $convertToWebp);
                $fileNames[] = $fileName;
                $mediaByFileName[$fileName] = $row;
            }

            $existingFiles = DB::table('files')
                ->select(['id', 'file_name', 'key', 'file_path'])
                ->whereIn('file_name', $fileNames)
                ->get()
                ->keyBy('file_name');

            $upserts = [];
            foreach ($rows as $row) {
                $sourceFileName = $this->makeSourceFileName($row);
                $sourcePath = $this->makeSourcePath((int) $row->id, $sourceFileName);
                $convertToWebp = $this->shouldConvertToWebp($sourcePath, (string) ($row->mime_type ?? ''));
                $fileName = $this->makeFileName($row, $convertToWebp);
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $dimensions = $this->extractDimensions($row->custom_properties, $sourcePath);
                $existingFile = $existingFiles->get($fileName);

                if ($existingFile) {
                    $key = is_string($existingFile->key ?? null) && trim((string) $existingFile->key) !== ''
                        ? trim((string) $existingFile->key)
                        : $this->makeKey();
                    $filePath = $this->makeFilePath(self::TARGET_FOLDER, $key, $fileName);
                } else {
                    $key = $this->makeKey();
                    $filePath = $this->makeFilePath(self::TARGET_FOLDER, $key, $fileName);

                    if ($this->copyToTargetDirectory($sourcePath, $key, $fileName, $cdnRoot, self::TARGET_FOLDER, $dryRun, $convertToWebp)) {
                        $copied++;
                    } else {
                        $missing++;
                    }
                }

                $metaPayload = ['directory' => self::TARGET_FOLDER];

                $upserts[] = [
                    'key' => $key,
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'extension' => $extension,
                    'seq_no' => $row->order_column,
                    'mime_type' => $this->resolveTargetMimeType((string) ($row->mime_type ?? ''), $convertToWebp),
                    'file_size' => (float) ($row->size ?? 0),
                    'height' => (float) $dimensions['height'],
                    'width' => (float) $dimensions['width'],
                    'meta' => json_encode($metaPayload, JSON_UNESCAPED_UNICODE),
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ];
            }

            if (! $dryRun && ! empty($upserts)) {
                DB::table('files')->upsert(
                    $upserts,
                    ['file_name'],
                    [
                        'key',
                        'file_path',
                        'extension',
                        'seq_no',
                        'mime_type',
                        'file_size',
                        'height',
                        'width',
                        'meta',
                        'updated_at',
                    ]
                );

                $filesByName = DB::table('files')
                    ->select(['id', 'file_name'])
                    ->whereIn('file_name', $fileNames)
                    ->get()
                    ->keyBy('file_name');

                $usageUpserts = [];
                foreach ($fileNames as $fileName) {
                    $file = $filesByName->get($fileName);
                    $media = $mediaByFileName[$fileName] ?? null;
                    if (! $file || ! $media) {
                        continue;
                    }

                    $usageId = (int) ($media->model_id ?? 0);
                    if ($usageId <= 0) {
                        continue;
                    }

                    $usageMeta = $this->decodeJson($media->custom_properties);
                    $usageUpserts[] = [
                        'file_id' => (int) $file->id,
                        'usage_type' => self::TARGET_USAGE_TYPE,
                        'usage_id' => $usageId,
                        'title' => is_string($media->name ?? null) ? trim((string) $media->name) : null,
                        'alt_text' => is_string($media->name ?? null) ? trim((string) $media->name) : null,
                        'meta' => json_encode(
                            is_array($usageMeta) ? $usageMeta : [],
                            JSON_UNESCAPED_UNICODE
                        ),
                        'created_at' => $media->created_at ?? now(),
                        'updated_at' => $media->updated_at ?? now(),
                    ];
                }

                if (! empty($usageUpserts)) {
                    DB::table('file_usages')->upsert(
                        $usageUpserts,
                        ['file_id', 'usage_type', 'usage_id'],
                        ['title', 'alt_text', 'meta', 'updated_at']
                    );
                    $usageCreatedOrUpdated += count($usageUpserts);
                }
            }

            foreach ($fileNames as $fileName) {
                if ($existingFiles->has($fileName)) {
                    $updated++;
                } else {
                    $created++;
                }
            }

            $processed += count($rows);
            $this->line(sprintf('Processed %d product rows...', $processed));
        }, 'id');

        $this->newLine();
        $this->info(sprintf(
            '%s complete. Processed: %d, Created: %d, Updated: %d, Copied: %d, Missing: %d, File usages upserted: %d',
            $dryRun ? 'Dry run' : 'Copy',
            $processed,
            $created,
            $updated,
            $copied,
            $missing,
            $usageCreatedOrUpdated
        ));

        return self::SUCCESS;
    }

    private function makeKey(): string
    {
        return bin2hex(random_bytes(16));
    }

    private function makeFileName(object $row, bool $convertToWebp): string
    {
        $fileName = is_string($row->file_name ?? null) ? trim((string) $row->file_name) : '';
        $fileName = $this->sanitizeTargetFileName($fileName);

        if ($convertToWebp) {
            if ($fileName === '') {
                $fileName = 'media-'.$row->id;
            }

            $nameWithoutExtension = $this->normalizeWebpBaseName($fileName);
            if (trim($nameWithoutExtension) === '') {
                $nameWithoutExtension = 'media-'.$row->id;
            }

            return strtolower(trim($nameWithoutExtension).'.webp');
        }

        if ($fileName !== '') {
            return strtolower($fileName);
        }

        return strtolower('media-'.$row->id);
    }

    private function sanitizeTargetFileName(string $fileName): string
    {
        $fileName = trim($fileName);
        if ($fileName === '') {
            return '';
        }

        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $baseName = is_string($baseName) ? trim($baseName) : '';
        $extension = is_string($extension) ? trim($extension) : '';

        $baseName = $this->stripIconCharacters($baseName);
        $baseName = str_replace('_', '-', $baseName);
        $baseName = preg_replace('/[\s\/\\\\]+/', '-', $baseName) ?? $baseName;
        $baseName = preg_replace('/[^\p{L}\p{N}-]+/u', '', $baseName) ?? $baseName;
        $baseName = preg_replace('/-+/', '-', $baseName) ?? $baseName;
        $baseName = trim($baseName, '-');

        if ($baseName === '') {
            return '';
        }

        if ($extension === '') {
            return $baseName;
        }

        return $baseName.'.'.$extension;
    }

    private function makeSourceFileName(object $row): string
    {
        $fileName = is_string($row->file_name ?? null) ? trim((string) $row->file_name) : '';
        if ($fileName !== '') {
            return $fileName;
        }

        return 'media-'.$row->id;
    }

    private function extractDimensions(mixed $customProperties, string $sourcePath): array
    {
        $decoded = $this->decodeJson($customProperties);
        $height = 0;
        $width = 0;

        if (is_array($decoded)) {
            $heightRaw = $decoded['height'] ?? $decoded['image_height'] ?? 0;
            $widthRaw = $decoded['width'] ?? $decoded['image_width'] ?? 0;
            $height = is_numeric($heightRaw) ? (float) $heightRaw : 0;
            $width = is_numeric($widthRaw) ? (float) $widthRaw : 0;
        }

        if (File::exists($sourcePath)) {
            $imageInfo = @getimagesize($sourcePath);
            if (is_array($imageInfo)) {
                $width = isset($imageInfo[0]) && is_numeric($imageInfo[0]) ? (float) $imageInfo[0] : $width;
                $height = isset($imageInfo[1]) && is_numeric($imageInfo[1]) ? (float) $imageInfo[1] : $height;
            }
        }

        return ['height' => $height, 'width' => $width];
    }

    private function decodeJson(mixed $value): mixed
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }
    }

    private function makeSourcePath(int $mediaId, string $sourceFileName): string
    {
        return public_path('media/'.$mediaId.'/'.$sourceFileName);
    }

    private function makeFilePath(string $targetFolder, string $key, string $targetFileName): string
    {
        return trim($targetFolder, '/').'/'.trim($key, '/').'/'.ltrim($targetFileName, '/');
    }

    private function copyToTargetDirectory(
        string $sourcePath,
        string $key,
        string $targetFileName,
        string $cdnRoot,
        string $targetFolder,
        bool $dryRun,
        bool $convertToWebp
    ): bool {
        $targetDirectory = $cdnRoot.DIRECTORY_SEPARATOR.trim($targetFolder, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.trim($key, DIRECTORY_SEPARATOR);
        $targetPath = $targetDirectory.DIRECTORY_SEPARATOR.$targetFileName;

        if (! File::exists($sourcePath)) {
            return false;
        }

        if ($dryRun) {
            return true;
        }

        if ($convertToWebp) {
            if (! $this->convertToWebpUsingService($sourcePath, $targetPath)) {
                return false;
            }

            return true;
        }

        File::ensureDirectoryExists($targetDirectory);
        File::copy($sourcePath, $targetPath);

        return File::exists($targetPath);
    }

    private function resolveTargetMimeType(string $sourceMimeType, bool $convertToWebp): string
    {
        if ($convertToWebp && str_starts_with(strtolower(trim($sourceMimeType)), 'image/')) {
            return 'image/webp';
        }

        return $sourceMimeType;
    }

    private function convertToWebpUsingService(string $sourcePath, string $targetPathOnDisk): bool
    {
        if (! File::exists($sourcePath)) {
            return false;
        }

        $tempDiskName = 'local';
        $tempDisk = Storage::disk($tempDiskName);
        $tempSourcePathOnDisk = '';
        $tempTargetPathOnDisk = '';

        try {
            $tempSourcePathOnDisk = '_tmp/webp-source/'.bin2hex(random_bytes(16)).'-'.basename($sourcePath);
            $tempTargetPathOnDisk = '_tmp/webp-target/'.bin2hex(random_bytes(16)).'.webp';
            $sourceBytes = File::get($sourcePath);
            if (! is_string($sourceBytes) || $sourceBytes === '') {
                return false;
            }

            if (! $tempDisk->put($tempSourcePathOnDisk, $sourceBytes)) {
                return false;
            }

            $this->imageConverter->toWebp($tempDiskName, $tempSourcePathOnDisk, $tempTargetPathOnDisk, 85);

            if (! $tempDisk->exists($tempTargetPathOnDisk)) {
                return false;
            }

            $webpBytes = $tempDisk->get($tempTargetPathOnDisk);
            if (! is_string($webpBytes) || $webpBytes === '') {
                return false;
            }

            $targetDirectory = dirname($targetPathOnDisk);
            if (! is_string($targetDirectory) || $targetDirectory === '') {
                return false;
            }

            File::ensureDirectoryExists($targetDirectory);
            File::put($targetPathOnDisk, $webpBytes);

            return File::exists($targetPathOnDisk);
        } catch (\Throwable) {
            return false;
        } finally {
            if ($tempSourcePathOnDisk !== '') {
                $this->deleteTemporarySourceFile($tempDisk, $tempSourcePathOnDisk);
            }
            if ($tempTargetPathOnDisk !== '') {
                $this->deleteTemporarySourceFile($tempDisk, $tempTargetPathOnDisk);
            }
        }
    }

    private function deleteTemporarySourceFile(object $cdnDisk, string $tempSourcePathOnDisk): void
    {
        try {
            $cdnDisk->delete($tempSourcePathOnDisk);
        } catch (\Throwable) {}

        try {
            if (! $cdnDisk->exists($tempSourcePathOnDisk)) {
                return;
            }
        } catch (\Throwable) { return; }

        try {
            if (method_exists($cdnDisk, 'path')) {
                $absolutePath = $cdnDisk->path($tempSourcePathOnDisk);
                if (is_string($absolutePath) && $absolutePath !== '' && File::exists($absolutePath)) {
                    File::delete($absolutePath);
                }
            }
        } catch (\Throwable) {}

        try {
            $directory = dirname($tempSourcePathOnDisk);
            if ($directory !== '.' && $directory !== DIRECTORY_SEPARATOR) {
                $cdnDisk->deleteDirectory($directory);
            }
        } catch (\Throwable) {}
    }

    private function shouldConvertToWebp(string $sourcePath, string $sourceMimeType): bool
    {
        if (! str_starts_with(strtolower(trim($sourceMimeType)), 'image/')) {
            return false;
        }

        if (! File::exists($sourcePath)) {
            return false;
        }

        if (! function_exists('imagewebp') || ! function_exists('exif_imagetype')) {
            return false;
        }

        if (! $this->hasEnoughMemoryForWebpConversion($sourcePath)) {
            return false;
        }

        $imageType = @exif_imagetype($sourcePath);
        if (! is_int($imageType)) {
            return false;
        }

        return match ($imageType) {
            IMAGETYPE_JPEG => function_exists('imagecreatefromjpeg'),
            IMAGETYPE_PNG => function_exists('imagecreatefrompng'),
            IMAGETYPE_GIF => function_exists('imagecreatefromgif'),
            IMAGETYPE_WEBP => false,
            IMAGETYPE_BMP => function_exists('imagecreatefrombmp'),
            default => false,
        };
    }

    private function hasEnoughMemoryForWebpConversion(string $sourcePath): bool
    {
        $limitBytes = $this->memoryLimitBytes();
        if ($limitBytes <= 0) {
            return true;
        }

        $imageInfo = @getimagesize($sourcePath);
        if (! is_array($imageInfo) || ! isset($imageInfo[0], $imageInfo[1])) {
            return true;
        }

        $width = (int) $imageInfo[0];
        $height = (int) $imageInfo[1];
        if ($width <= 0 || $height <= 0) {
            return true;
        }

        $currentUsage = memory_get_usage(true);
        $rgbaBytes = (float) $width * (float) $height * 4.0;
        $estimatedExtraBytes = (int) ceil(($rgbaBytes * 2.2) + 8_388_608);

        return ($currentUsage + $estimatedExtraBytes) <= $limitBytes;
    }

    private function memoryLimitBytes(): int
    {
        $rawLimit = ini_get('memory_limit');
        if (! is_string($rawLimit)) {
            return 0;
        }

        $value = strtolower(trim($rawLimit));
        if ($value === '' || $value === '-1') {
            return 0;
        }

        if (! preg_match('/^(\d+(?:\.\d+)?)([gmk])?$/', $value, $matches)) {
            return 0;
        }

        $number = (float) $matches[1];
        $unit = $matches[2] ?? '';

        return match ($unit) {
            'g' => (int) round($number * 1024 * 1024 * 1024),
            'm' => (int) round($number * 1024 * 1024),
            'k' => (int) round($number * 1024),
            default => (int) round($number),
        };
    }

    private function normalizeWebpBaseName(string $fileName): string
    {
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        if (! is_string($baseName)) {
            return '';
        }

        $current = trim($baseName);
        $pattern = '/\.(?:jpe?g|png|gif|bmp|webp|svg)$/i';

        while ($current !== '' && preg_match($pattern, $current) === 1) {
            $current = (string) preg_replace($pattern, '', $current);
            $current = trim($current);
        }

        return $current;
    }

    private function stripIconCharacters(string $value): string
    {
        $cleaned = preg_replace('/[\x{FE0E}\x{FE0F}\x{200D}\x{20E3}]/u', '', $value) ?? $value;
        $cleaned = preg_replace('/[\p{Extended_Pictographic}\p{So}]/u', '', $cleaned) ?? $cleaned;

        return trim($cleaned);
    }
}
