<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Files\Uploads;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\FileModel;
use App\Foundation\Shared\Application\Contracts\FileUploadService;
use App\Foundation\Shared\Application\Contracts\ImageConverter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class FatafatCdnFileUploadService implements FileUploadService
{
    private const DISK = 'fatafat_cdn';
    private const DEFAULT_DIRECTORY = 'uploads';
    private const WEBP_QUALITY = 85;

    public function __construct(
        private readonly ImageConverter $imageConverter
    ) {}

    public function uploadImageAsWebp(UploadedFile $file, ?string $directory = null): array
    {
        $targetDirectory = $this->normalizeDirectory((string) ($directory ?? self::DEFAULT_DIRECTORY));
        $sanitizedFileName = $this->sanitizeTargetFileName((string) $file->getClientOriginalName());
        $safeBaseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);
        $safeBaseName = is_string($safeBaseName) ? trim($safeBaseName) : '';
        if ($safeBaseName === '') {
            $safeBaseName = 'image';
        }

        $sourceBytes = $file->get();
        if (! is_string($sourceBytes) || $sourceBytes === '') {
            throw new RuntimeException('Unable to read uploaded image bytes.');
        }

        $contentHash = hash('sha256', $sourceBytes);
        $existing = $this->findExistingByContentHash($contentHash);
        if ($existing !== null) {
            return $existing;
        }

        $storedFileName = $safeBaseName.'.webp';
        $key = md5($targetDirectory.'/'.$storedFileName);
        $relativePath = $targetDirectory.'/'.$key.'/'.$storedFileName;

        $disk = Storage::disk(self::DISK);
        if ($disk->exists($relativePath)) {
            $result = $this->buildResult($disk, $key, $storedFileName, $relativePath, true);
            $this->persistFileRow($result, $contentHash);
            $result['file_data'] = $this->findFileDataByKey($key) ?? $this->fallbackFileData($result);
            return $result;
        }

        $sourceExtension = strtolower((string) $file->getClientOriginalExtension());
        $sourceExtension = preg_replace('/[^a-z0-9]/', '', $sourceExtension) ?: 'jpg';
        $tempSourcePath = $targetDirectory.'/tmp/'.$safeBaseName.'_'.Str::uuid().'.'.$sourceExtension;

        try {
            if (! $disk->put($tempSourcePath, $sourceBytes)) {
                throw new RuntimeException('Failed to stage source image before conversion.');
            }

            $this->imageConverter->toWebp(self::DISK, $tempSourcePath, $relativePath, self::WEBP_QUALITY);
        } catch (Throwable $exception) {
            throw new RuntimeException('Failed to upload file.', 0, $exception);
        } finally {
            $disk->delete($tempSourcePath);
        }

        $result = $this->buildResult($disk, $key, $storedFileName, $relativePath, false);
        $this->persistFileRow($result, $contentHash);
        $result['file_data'] = $this->findFileDataByKey($key) ?? $this->fallbackFileData($result);

        return $result;
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

    private function stripIconCharacters(string $value): string
    {
        $cleaned = preg_replace('/[\x{FE0E}\x{FE0F}\x{200D}\x{20E3}]/u', '', $value) ?? $value;
        $cleaned = preg_replace('/[\p{Extended_Pictographic}\p{So}]/u', '', $cleaned) ?? $cleaned;

        return trim($cleaned);
    }

    private function normalizeDirectory(string $directory): string
    {
        $cleaned = preg_replace('#[^A-Za-z0-9/_-]#', '', trim($directory));
        if (! is_string($cleaned) || $cleaned === '') {
            return self::DEFAULT_DIRECTORY;
        }

        $cleaned = preg_replace('#/+#', '/', $cleaned);
        $cleaned = trim((string) $cleaned, '/');

        return $cleaned !== '' ? $cleaned : self::DEFAULT_DIRECTORY;
    }

    private function buildUrl(string $relativePath): string
    {
        $baseUrl = trim((string) config('filesystems.disks.'.self::DISK.'.url', ''), '/');

        return $baseUrl !== ''
            ? $baseUrl.'/'.ltrim($relativePath, '/')
            : '/'.ltrim($relativePath, '/');
    }

    private function buildResult(
        mixed $disk,
        string $key,
        string $fileName,
        string $relativePath,
        bool $alreadyExists
    ): array {
        $webpBytes = $disk->get($relativePath);
        if (! is_string($webpBytes) || $webpBytes === '') {
            throw new RuntimeException('Failed to read uploaded WebP file.');
        }

        $imageInfo = @getimagesizefromstring($webpBytes);
        $width = null;
        $height = null;
        if (is_array($imageInfo)) {
            $width = isset($imageInfo[0]) ? (float) $imageInfo[0] : null;
            $height = isset($imageInfo[1]) ? (float) $imageInfo[1] : null;
        }

        return [
            'key' => $key,
            'file_name' => $fileName,
            'file_path' => $relativePath,
            'extension' => 'webp',
            'mime_type' => 'image/webp',
            'file_size' => strlen($webpBytes),
            'height' => $height,
            'width' => $width,
            'meta' => [
                'disk' => self::DISK,
            ],
            'url' => $this->buildUrl($relativePath),
            'already_exists' => $alreadyExists,
        ];
    }

    private function persistFileRow(array $result, string $contentHash): void
    {
        $now = now();
        $filePath = (string) ($result['file_path'] ?? '');
        $segments = explode('/', ltrim($filePath, '/'));
        $directory = trim((string) ($segments[0] ?? ''));
        $meta = json_encode([
            'directory' => $directory !== '' ? $directory : null,
        ], JSON_UNESCAPED_SLASHES);

        DB::table('files')->upsert(
            [[
                'key' => (string) $result['key'],
                'file_name' => (string) $result['file_name'],
                'file_path' => $filePath,
                'extension' => (string) $result['extension'],
                'seq_no' => null,
                'mime_type' => (string) $result['mime_type'],
                'file_size' => (float) $result['file_size'],
                'height' => isset($result['height']) ? (float) $result['height'] : null,
                'width' => isset($result['width']) ? (float) $result['width'] : null,
                'disk' => self::DISK,
                'content_hash' => $contentHash,
                'meta' => $meta,
                'created_at' => $now,
                'updated_at' => $now,
            ]],
            ['key'],
            ['file_name', 'file_path', 'extension', 'seq_no', 'mime_type', 'file_size', 'height', 'width', 'disk', 'content_hash', 'meta', 'updated_at']
        );
    }

    private function findExistingByContentHash(string $contentHash): ?array
    {
        $existing = FileModel::where('content_hash', $contentHash)
            ->first();

        if (! $existing) {
            return null;
        }

        // $filePath = trim((string) ($existing->file_path ?? ''), '/');

        return [
            // 'key' => (string) ($existing->key ?? ''),
            // 'file_name' => (string) ($existing->file_name ?? ''),
            // 'file_path' => $filePath,
            // 'extension' => (string) ($existing->extension ?? 'webp'),
            // 'mime_type' => (string) ($existing->mime_type ?? 'image/webp'),
            // 'file_size' => (float) ($existing->file_size ?? 0),
            // 'height' => isset($existing->height) ? (float) $existing->height : null,
            // 'width' => isset($existing->width) ? (float) $existing->width : null,
            // 'meta' => $existing->meta ?? null,
            // 'url' => $this->buildUrl($filePath),
            'file_data' => $this->normalizeFileData($existing->toArray()),
            'already_exists' => true,
        ];
    }

    private function findFileDataByKey(string $key): ?array
    {
        $row = DB::table('files')
            ->where('key', $key)
            ->first();

        if (! $row) {
            return null;
        }

        return $this->normalizeFileData((array) $row);
    }

    private function normalizeFileData(array $row): array
    {
        $meta = $row['meta'] ?? null;
        if (is_string($meta) && $meta !== '') {
            $decoded = json_decode($meta, true);
            $meta = is_array($decoded) ? $decoded : $meta;
        }

        $filePath = trim((string) ($row['file_path'] ?? ''), '/');

        return [
            'id' => isset($row['id']) ? (int) $row['id'] : null,
            'key' => (string) ($row['key'] ?? ''),
            'file_name' => (string) ($row['file_name'] ?? ''),
            'file_path' => $filePath,
            'extension' => (string) ($row['extension'] ?? ''),
            'seq_no' => $row['seq_no'] ?? null,
            'mime_type' => (string) ($row['mime_type'] ?? ''),
            'file_size' => isset($row['file_size']) ? (float) $row['file_size'] : null,
            'height' => isset($row['height']) ? (float) $row['height'] : null,
            'width' => isset($row['width']) ? (float) $row['width'] : null,
            'disk' => (string) ($row['disk'] ?? self::DISK),
            'meta' => $meta,
            'url' => $this->buildUrl($filePath),
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
        ];
    }

    private function fallbackFileData(array $result): array
    {
        $meta = $result['meta'] ?? null;
        $filePath = trim((string) ($result['file_path'] ?? ''), '/');

        return [
            'id' => null,
            'key' => (string) ($result['key'] ?? ''),
            'file_name' => (string) ($result['file_name'] ?? ''),
            'file_path' => $filePath,
            'extension' => (string) ($result['extension'] ?? 'webp'),
            'seq_no' => null,
            'mime_type' => (string) ($result['mime_type'] ?? 'image/webp'),
            'file_size' => isset($result['file_size']) ? (float) $result['file_size'] : null,
            'height' => isset($result['height']) ? (float) $result['height'] : null,
            'width' => isset($result['width']) ? (float) $result['width'] : null,
            'disk' => self::DISK,
            'meta' => is_array($meta) ? $meta : $meta,
            'url' => (string) ($result['url'] ?? $this->buildUrl($filePath)),
            'created_at' => null,
            'updated_at' => null,
        ];
    }
}
