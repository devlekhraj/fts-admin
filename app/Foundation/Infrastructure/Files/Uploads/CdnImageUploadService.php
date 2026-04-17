<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Files\Uploads;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\FileModel;
use App\Foundation\Shared\Application\Contracts\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

final class CdnImageUploadService implements ImageUploadService
{
    private const DISK = 'cdn';

    public function upload(UploadedFile $file, string $usageType, int $usageId, ?string $fileName = null): array
    {
        $safeUsageType = $this->normalizeUsageType($usageType);
        if ($safeUsageType === '') {
            throw new RuntimeException('Invalid usage_type for upload path.');
        }
        if ($usageId < 1) {
            throw new RuntimeException('Invalid usage_id for upload path.');
        }

        $bytes = $file->get();
        if (! is_string($bytes) || $bytes === '') {
            throw new RuntimeException('Unable to read uploaded image bytes.');
        }

        $disk = Storage::disk(self::DISK);

        $originalName = (string) $file->getClientOriginalName();
        $requestedName = is_string($fileName) ? trim($fileName) : '';
        $candidateName = $requestedName !== '' ? $requestedName : $originalName;
        $candidateName = $this->sanitizeTargetFileName($candidateName);

        $extension = pathinfo($candidateName, PATHINFO_EXTENSION);
        $nameOnly = pathinfo($candidateName, PATHINFO_FILENAME);

        $fallbackExtension = strtolower((string) $file->getClientOriginalExtension());
        $fallbackExtension = preg_replace('/[^a-z0-9]/', '', $fallbackExtension) ?: 'jpg';

        $extension = is_string($extension) && trim($extension) !== '' ? strtolower(trim($extension)) : $fallbackExtension;
        $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?: $fallbackExtension;

        $nameOnly = is_string($nameOnly) && trim($nameOnly) !== '' ? trim($nameOnly) : 'image';

        $finalFileName = $nameOnly.'.'.$extension;
        $relativePath = $this->makeFilePath($safeUsageType, (string) $usageId, $finalFileName);

        $attempts = 0;
        while ($this->fileNameExistsInDatabase($finalFileName) || $disk->exists($relativePath)) {
            $attempts++;
            if ($attempts > 25) {
                throw new RuntimeException('Unable to generate a unique file name.');
            }

            $suffix = bin2hex(random_bytes(3));
            $finalFileName = $nameOnly.'-'.$suffix.'.'.$extension;
            $relativePath = $this->makeFilePath($safeUsageType, (string) $usageId, $finalFileName);
        }

        if (! $disk->put($relativePath, $bytes)) {
            throw new RuntimeException('Failed to store uploaded image.');
        }

        $imageInfo = @getimagesizefromstring($bytes);
        $width = is_array($imageInfo) && isset($imageInfo[0]) ? (float) $imageInfo[0] : null;
        $height = is_array($imageInfo) && isset($imageInfo[1]) ? (float) $imageInfo[1] : null;

        $meta = json_encode(array_filter([
            'directory' => $safeUsageType,
        ], static fn ($v) => ! is_null($v)), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (! is_string($meta) || $meta === '') {
            $meta = '{}';
        }

        $now = now();
        $key = $safeUsageType.'-'.$usageId.'-'.Str::orderedUuid()->toString();
        $contentHash = hash('sha256', $bytes);

        $fileId = DB::table('files')->insertGetId([
            'key' => $key,
            'file_name' => $finalFileName,
            'file_path' => $relativePath,
            'extension' => $extension,
            'seq_no' => null,
            'mime_type' => $file->getMimeType(),
            'file_size' => strlen($bytes),
            'height' => $height,
            'width' => $width,
            'disk' => self::DISK,
            'content_hash' => $contentHash,
            'meta' => $meta,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $fileModel = FileModel::query()->find($fileId);
        if (! $fileModel) {
            throw new RuntimeException('Uploaded file could not be resolved.');
        }

        return [
            'file_id' => (int) $fileModel->id,
            'file_data' => $fileModel->toArray(),
        ];
    }

    private function fileNameExistsInDatabase(string $fileName): bool
    {
        return DB::table('files')->where('file_name', $fileName)->exists();
    }

    private function makeFilePath(string $usageType, string $usageId, string $fileName): string
    {
        return trim($usageType, '/').'/'.trim($usageId, '/').'/'.ltrim($fileName, '/');
    }

    private function normalizeUsageType(string $usageType): string
    {
        $usageType = trim($usageType);
        $usageType = preg_replace('#[^A-Za-z0-9_-]#', '', $usageType) ?? '';

        return trim((string) $usageType);
    }

    private function sanitizeTargetFileName(string $fileName): string
    {
        $fileName = trim($fileName);
        if ($fileName === '') {
            return '';
        }

        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $baseName = is_string($baseName) ? trim((string) $baseName) : '';
        $extension = is_string($extension) ? trim((string) $extension) : '';

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

        return trim((string) $cleaned);
    }
}
