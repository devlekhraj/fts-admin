<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Files\Uploads;

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
    // private const DISK = 'fatafat_cdn';

    private const DISK = 'cdn';
    private const DEFAULT_DIRECTORY = 'uploads';

    private const WEBP_QUALITY = 85;

    public function __construct(
        private readonly ImageConverter $imageConverter
    ) {}

    public function uploadImageAsWebp(UploadedFile $file, ?string $directory = null): array
    {
        $context = $this->parseDirectoryContext((string) ($directory ?? self::DEFAULT_DIRECTORY));
        $modelType = $context['model_type'];
        $modelId = $context['model_id'];

        $originalName = (string) $file->getClientOriginalName();
        $sanitizedFileName = $this->sanitizeTargetFileName($originalName);
        $nameOnly = pathinfo($sanitizedFileName, PATHINFO_FILENAME);
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?: 'jpg';

        $disk = Storage::disk(self::DISK);
        $targetFileName = $nameOnly . '.' . $extension;

        // Check for duplicates in the target directory
        $targetPathBase = $this->makeFilePath($modelType, $modelId, '');
        if ($disk->exists($targetPathBase . $targetFileName)) {
            $targetFileName = $nameOnly . '-' . strtolower(Str::random(4)) . '.' . $extension;
        }

        $key = $this->makeKey($modelType, $modelId);
        $relativePath = $this->makeFilePath($modelType, $modelId, $targetFileName);

        try {
            if (! $disk->put($relativePath, $file->get())) {
                throw new RuntimeException('Failed to store uploaded file.');
            }
        } catch (Throwable $exception) {
            throw new RuntimeException('Failed to upload file.', 0, $exception);
        }

        $result = $this->buildResult($disk, $key, $targetFileName, $relativePath, false, $extension, $file->getMimeType());
        $this->persistFileRow($result);
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
        bool $alreadyExists,
        string $extension,
        ?string $mimeType = null
    ): array {
        $contentBytes = $disk->get($relativePath);
        if (! is_string($contentBytes) || $contentBytes === '') {
            throw new RuntimeException('Failed to read uploaded file.');
        }

        $width = null;
        $height = null;

        if (str_starts_with(strtolower((string) $mimeType), 'image/')) {
            $imageInfo = @getimagesizefromstring($contentBytes);
            if (is_array($imageInfo)) {
                $width = isset($imageInfo[0]) ? (float) $imageInfo[0] : null;
                $height = isset($imageInfo[1]) ? (float) $imageInfo[1] : null;
            }
        }

        return [
            'key' => $key,
            'file_name' => $fileName,
            'file_path' => $relativePath,
            'extension' => $extension,
            'mime_type' => $mimeType ?? 'application/octet-stream',
            'file_size' => strlen($contentBytes),
            'height' => $height,
            'width' => $width,
            'meta' => [
                'disk' => self::DISK,
            ],
            'url' => $this->buildUrl($relativePath),
            'already_exists' => $alreadyExists,
        ];
    }

    private function persistFileRow(array $result): void
    {
        $now = now();
        $filePath = (string) ($result['file_path'] ?? '');
        $segments = explode('/', ltrim($filePath, '/'));
        $directory = trim((string) ($segments[0] ?? ''));
        $meta = json_encode(array_filter([
            'directory' => $directory !== '' ? $directory : null,
        ], fn($v) => ! is_null($v)), JSON_UNESCAPED_SLASHES);

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
                'meta' => $meta,
                'created_at' => $now,
                'updated_at' => $now,
            ]],
            ['key'],
            ['file_name', 'file_path', 'extension', 'seq_no', 'mime_type', 'file_size', 'height', 'width', 'disk', 'meta', 'updated_at']
        );
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
            'extension' => (string) ($result['extension'] ?? ''),
            'seq_no' => null,
            'mime_type' => (string) ($result['mime_type'] ?? 'application/octet-stream'),
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

    private function makeKey(string $type, string $typeId): string
    {
        return $type . '-' . $typeId . '-' . Str::orderedUuid()->toString();
    }

    private function makeFilePath(string $modelType, string $modelId, string $targetFileName): string
    {
        $path = trim($modelType, '/') . '/' . trim($modelId, '/');
        if ($targetFileName !== '') {
            $path .= '/' . ltrim($targetFileName, '/');
        }

        return $path;
    }

    private function parseDirectoryContext(string $directory): array
    {
        $directory = $this->normalizeDirectory($directory);
        $segments = explode('/', $directory);

        if (count($segments) >= 2) {
            return [
                'model_type' => $segments[0],
                'model_id' => $segments[1],
            ];
        }

        return [
            'model_type' => $segments[0] !== '' ? $segments[0] : self::DEFAULT_DIRECTORY,
            'model_id' => (string) Str::uuid(),
        ];
    }
}
