<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class CopyBannerImageToFilesCommand extends Command
{
    private const DEFAULT_MODEL_TYPES = [
        "Jed\\Banners\\App\\BannerImage",
    ];

    private const MODEL_TYPE_TABLE_MAP = [
        'Jed\\Banners\\App\\BannerImage' => 'banners',
    ];

    private const MODEL_TYPE_FOLDER_MAP = [
        'Jed\\Banners\\App\\BannerImage' => 'banners',
    ];

    protected $signature = 'media:copy-banner-images-to-files
        {--chunk=500 : Chunk size for processing}
        {--dry-run : Show what would be copied without writing data}';

    protected $description = 'Copy BannerImage records from media table to files table';

    public function handle(): int
    {
        if (!Schema::hasTable('media')) {
            $this->error('Table "media" does not exist.');
            return self::FAILURE;
        }

        if (!Schema::hasTable('files')) {
            $this->error('Table "files" does not exist.');
            return self::FAILURE;
        }

        if (!Schema::hasTable('file_usages')) {
            $this->error('Table "file_usages" does not exist.');
            return self::FAILURE;
        }
        if (!Schema::hasTable('banner_images')) {
            $this->error('Table "banner_images" does not exist.');
            return self::FAILURE;
        }

        $chunkSize = max(1, (int) $this->option('chunk'));
        $modelTypes = self::DEFAULT_MODEL_TYPES;
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
            ->whereIn('model_type', $modelTypes)
            ->orderBy('id');

        $total = (clone $baseQuery)->count();
        if ($total === 0) {
            $this->info('No records found in media table.');
            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Processing %d media rows for model_type "%s" (chunk: %d)%s',
            $total,
            implode(', ', $modelTypes),
            $chunkSize,
            $dryRun ? ' [dry-run]' : ''
        ));

        $processed = 0;
        $created = 0;
        $updated = 0;
        $copied = 0;
        $missing = 0;
        $usageCreatedOrUpdated = 0;
        $missingBannerImageMapping = 0;

        $baseQuery->chunkById($chunkSize, function ($rows) use (
            &$processed,
            &$created,
            &$updated,
            &$copied,
            &$missing,
            &$usageCreatedOrUpdated,
            &$missingBannerImageMapping,
            $dryRun,
            $cdnRoot
        ): void {
            $fileNames = [];
            $mediaByFileName = [];
            $bannerImageIds = [];
            foreach ($rows as $row) {
                $fileName = $this->makeFileName($row);
                $fileNames[] = $fileName;
                $mediaByFileName[$fileName] = $row;
                $bannerImageId = (int) ($row->model_id ?? 0);
                if ($bannerImageId > 0) {
                    $bannerImageIds[] = $bannerImageId;
                }
            }
            $bannerImageIds = array_values(array_unique($bannerImageIds));

            $bannerUsageMap = [];
            $bannerImageMetaMap = [];
            if (!empty($bannerImageIds)) {
                $bannerImages = DB::table('banner_images')
                    ->select(['id', 'banner_id', 'link', 'start_date', 'end_date'])
                    ->whereIn('id', $bannerImageIds)
                    ->get();

                foreach ($bannerImages as $bannerImage) {
                    $bannerImageId = (int) ($bannerImage->id ?? 0);
                    if ($bannerImageId <= 0) {
                        continue;
                    }

                    $bannerUsageMap[$bannerImageId] = (int) ($bannerImage->banner_id ?? 0);
                    $bannerImageMetaMap[$bannerImageId] = [
                        'link' => $bannerImage->link ?? null,
                        'start_date' => $bannerImage->start_date ?? null,
                        'end_date' => $bannerImage->end_date ?? null,
                    ];
                }
            }

            $existingFiles = DB::table('files')
                ->select(['id', 'file_name', 'key', 'file_path'])
                ->whereIn('file_name', $fileNames)
                ->get()
                ->keyBy('file_name');

            $upserts = [];
            foreach ($rows as $row) {
                $fileName = $this->makeFileName($row);
                $sourceFileName = $this->makeSourceFileName($row);
                $sourcePath = $this->makeSourcePath((int) $row->id, $sourceFileName);
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $dimensions = $this->extractDimensions($row->custom_properties, $sourcePath);
                $targetFolder = $this->resolveTargetFolder((string) $row->model_type);
                $existingFile = $existingFiles->get($fileName);

                if ($existingFile) {
                    $key = is_string($existingFile->key ?? null) && trim((string) $existingFile->key) !== ''
                        ? trim((string) $existingFile->key)
                        : $this->makeKey();
                    $filePath = is_string($existingFile->file_path ?? null) && trim((string) $existingFile->file_path) !== ''
                        ? trim((string) $existingFile->file_path)
                        : $targetFolder.'/'.$key.'/'.$fileName;
                } else {
                    $key = $this->makeKey();
                    $filePath = $targetFolder.'/'.$key.'/'.$fileName;

                    if ($this->copyToTargetDirectory($sourcePath, $key, $fileName, $cdnRoot, $targetFolder, $dryRun)) {
                        $copied++;
                    } else {
                        $missing++;
                    }
                }

                $metaPayload = [
                    // 'media_id' => $row->id,
                    // 'model_type' => $this->resolveModelType((string) $row->model_type),
                    // 'model_id' => $row->model_id,
                    // 'name' => $row->name,
                    // 'disk' => $row->disk,
                    // 'conversions_disk' => $row->conversions_disk,
                    // 'manipulations' => $this->decodeJson($row->manipulations),
                    // 'custom_properties' => $this->decodeJson($row->custom_properties),
                    // 'generated_conversions' => $this->decodeJson($row->generated_conversions),
                    // 'responsive_images' => $this->decodeJson($row->responsive_images),
                ];

                $upserts[] = [
                    'key' => $key,
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'extension' => $extension,
                    'seq_no' => $row->order_column,
                    'mime_type' => $row->mime_type,
                    'file_size' => (float) ($row->size ?? 0),
                    'height' => (float) $dimensions['height'],
                    'width' => (float) $dimensions['width'],
                    'meta' => json_encode($metaPayload, JSON_UNESCAPED_UNICODE),
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ];
            }

            if (!$dryRun && !empty($upserts)) {
                DB::table('files')->upsert(
                    $upserts,
                    ['file_name'],
                    [
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
                    if (!$file || !$media) {
                        continue;
                    }

                    $bannerImageId = (int) ($media->model_id ?? 0);
                    $usageId = (int) ($bannerUsageMap[$bannerImageId] ?? 0);
                    if ($usageId <= 0) {
                        $missingBannerImageMapping++;
                        continue;
                    }
                    $bannerImageMeta = $bannerImageMetaMap[$bannerImageId] ?? [
                        'link' => null,
                        'start_date' => null,
                        'end_date' => null,
                    ];

                    $usageUpserts[] = [
                        'file_id' => (int) $file->id,
                        'usage_type' => $this->resolveModelType((string) $media->model_type),
                        'usage_id' => $usageId,
                        'title' => is_string($media->name ?? null) ? trim((string) $media->name) : null,
                        'alt_text' => is_string($media->name ?? null) ? trim((string) $media->name) : null,
                        'meta' => json_encode([
                            'link' => $bannerImageMeta['link'],
                            'end_date' => $bannerImageMeta['end_date'],
                            'start_date' => $bannerImageMeta['start_date'],
                            'collection_name' => $media->collection_name,
                        ], JSON_UNESCAPED_UNICODE),
                        'created_at' => $media->created_at ?? now(),
                        'updated_at' => $media->updated_at ?? now(),
                    ];
                }

                if (!empty($usageUpserts)) {
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
            $this->line(sprintf('Processed %d rows...', $processed));
        }, 'id');

        $this->newLine();
        $this->info(sprintf(
            '%s complete. Processed: %d, Created: %d, Updated: %d, Copied: %d, Missing: %d, File usages upserted: %d, Missing banner_images->banner_id mappings: %d',
            $dryRun ? 'Dry run' : 'Copy',
            $processed,
            $created,
            $updated,
            $copied,
            $missing,
            $usageCreatedOrUpdated,
            $missingBannerImageMapping
        ));

        return self::SUCCESS;
    }

    private function makeKey(): string
    {
        return bin2hex(random_bytes(16));
    }

    private function makeFileName(object $row): string
    {
        $fileName = is_string($row->file_name ?? null) ? trim((string) $row->file_name) : '';
        if ($fileName !== '') {
            return strtolower($fileName);
        }

        return strtolower('media-'.$row->id);
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

        // Prefer actual file dimensions when file exists and is a readable image.
        if (File::exists($sourcePath)) {
            $imageInfo = @getimagesize($sourcePath);
            if (is_array($imageInfo)) {
                $width = isset($imageInfo[0]) && is_numeric($imageInfo[0]) ? (float) $imageInfo[0] : $width;
                $height = isset($imageInfo[1]) && is_numeric($imageInfo[1]) ? (float) $imageInfo[1] : $height;
            }
        }

        return [
            'height' => $height,
            'width' => $width,
        ];
    }

    private function decodeJson(mixed $value): mixed
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolveModelType(string $modelType): string
    {
        return self::MODEL_TYPE_TABLE_MAP[$modelType] ?? $modelType;
    }

    private function makeSourcePath(int $mediaId, string $sourceFileName): string
    {
        return public_path('media/'.$mediaId.'/'.$sourceFileName);
    }

    private function resolveTargetFolder(string $modelType): string
    {
        return self::MODEL_TYPE_FOLDER_MAP[$modelType] ?? 'misc';
    }

    private function copyToTargetDirectory(
        string $sourcePath,
        string $key,
        string $targetFileName,
        string $cdnRoot,
        string $targetFolder,
        bool $dryRun
    ): bool {
        $targetDirectory = $cdnRoot.DIRECTORY_SEPARATOR.$targetFolder.DIRECTORY_SEPARATOR.$key;
        $targetPath = $targetDirectory.DIRECTORY_SEPARATOR.$targetFileName;

        if (!File::exists($sourcePath)) {
            return false;
        }

        if ($dryRun) {
            return true;
        }

        File::ensureDirectoryExists($targetDirectory);
        File::copy($sourcePath, $targetPath);
        return true;
    }
}
