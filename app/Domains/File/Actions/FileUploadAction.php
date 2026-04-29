<?php

declare(strict_types=1);

namespace App\Domains\File\Actions;

use App\Domains\File\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class FileUploadAction
{
    private string $disk;

    public function __construct()
    {
        $this->disk = env('APP_ENV') === 'local' ? 'media' : 'cdn';
    }
    private const DEFAULT_DIRECTORY = 'uploads';

    public function execute(UploadedFile $file, string $directory, ?string $preferredFileName = null): array
    {
        $directory = $this->normalizeDirectory($directory);

        $bytes = $file->get();
        if (! is_string($bytes) || $bytes === '') {
            throw new RuntimeException('Unable to read uploaded image bytes.');
        }

        $candidateName = is_string($preferredFileName) ? trim($preferredFileName) : '';
        if ($candidateName === '') {
            $candidateName = (string) $file->getClientOriginalName();
        }
        $candidateName = $this->sanitizeTargetFileName($candidateName);

        $extension = pathinfo($candidateName, PATHINFO_EXTENSION);
        $nameOnly = pathinfo($candidateName, PATHINFO_FILENAME);

        $fallbackExtension = strtolower((string) $file->getClientOriginalExtension());
        $fallbackExtension = preg_replace('/[^a-z0-9]/', '', $fallbackExtension) ?: 'jpg';

        $extension = is_string($extension) && trim($extension) !== '' ? strtolower(trim($extension)) : $fallbackExtension;
        $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?: $fallbackExtension;
        $nameOnly = is_string($nameOnly) && trim($nameOnly) !== '' ? trim($nameOnly) : 'image';

        $disk = Storage::disk($this->disk);

        $attempts = 0;
        do {
            $suffix = $attempts === 0 ? '' : '-' . strtolower(Str::random(6));
            $targetFileName = $nameOnly . $suffix . '.' . $extension;
            $relativePath = trim($directory, '/') . '/' . $targetFileName;
            $attempts++;
            if ($attempts > 25) {
                throw new RuntimeException('Unable to generate a unique file name.');
            }
        } while (\App\Domains\File\Models\File::where('file_name', $targetFileName)->exists() || $disk->exists($relativePath));

        try {
            if (! $disk->put($relativePath, $bytes)) {
                throw new RuntimeException('Failed to store uploaded file.');
            }
        } catch (Throwable $exception) {
            throw new RuntimeException('Failed to upload file.', 0, $exception);
        }

        $imageInfo = @getimagesizefromstring($bytes);
        $width = is_array($imageInfo) && isset($imageInfo[0]) ? (float) $imageInfo[0] : null;
        $height = is_array($imageInfo) && isset($imageInfo[1]) ? (float) $imageInfo[1] : null;

        $now = now();
        $key = $this->makeKey($directory, (string) Str::uuid());

        $meta = json_encode(array_filter([
            'directory' => $directory !== '' ? $directory : null,
            'original_name' => $file->getClientOriginalName(),
        ], static fn($v) => ! is_null($v)), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (! is_string($meta) || $meta === '') {
            $meta = '{}';
        }

        $fileId = \App\Domains\File\Models\File::insertGetId([
            'key' => $key,
            'file_name' => $targetFileName,
            'file_path' => $relativePath,
            'extension' => $extension,
            'seq_no' => null,
            'mime_type' => $file->getMimeType(),
            'file_size' => strlen($bytes),
            'height' => $height,
            'width' => $width,
            'disk' => $this->disk,
            'meta' => $meta,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $fileModel = \App\Domains\File\Models\File::query()->find((int) $fileId);
        if (! $fileModel) {
            throw new RuntimeException('Uploaded file could not be resolved.');
        }

        return [
            'file_id' => (int) $fileModel->id,
            'file_data' => $fileModel->toArray(),
        ];
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

        return $baseName . '.' . $extension;
    }

    private function stripIconCharacters(string $value): string
    {
        $cleaned = preg_replace('/[\x{FE0E}\x{FE0F}\x{200D}\x{20E3}\x{20E3}]/u', '', $value) ?? $value;
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

    private function makeKey(string $type, string $typeId): string
    {
        return $type . '-' . $typeId . '-' . Str::orderedUuid()->toString();
    }
}
