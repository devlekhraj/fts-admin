<?php

declare(strict_types=1);

namespace App\Domains\File\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class FileUploadService
{
    private string $disk;
    private const DEFAULT_DIRECTORY = 'uploads';

    public function __construct()
    {
        $this->disk = (string) config('filesystems.default');
    }

    public function uploadFile(UploadedFile $file, ?string $directory = null): array
    {
        $originalName = (string) $file->getClientOriginalName();
        $sanitizedFileName = $this->sanitizeTargetFileName($originalName);

        $nameOnly = pathinfo($sanitizedFileName, PATHINFO_FILENAME);
        $nameOnly = is_string($nameOnly) ? trim($nameOnly) : '';
        if ($nameOnly === '') {
            $nameOnly = 'upload';
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?: 'jpg';

        $targetDirectory = $this->normalizeDirectory((string) ($directory ?? self::DEFAULT_DIRECTORY));
        $disk = Storage::disk($this->disk);

        $targetFileName = $nameOnly . '.' . $extension;
        $alreadyExists = false;

        $targetPathBase = rtrim($targetDirectory, '/') . '/';
        if ($disk->exists($targetPathBase . $targetFileName)) {
            $alreadyExists = true;
            $targetFileName = $nameOnly . '-' . strtolower(Str::random(4)) . '.' . $extension;
        }

        try {
            $storedPath = $file->storeAs($targetDirectory, $targetFileName, $this->disk);
        } catch (Throwable $exception) {
            throw new RuntimeException('Failed to upload file.', 0, $exception);
        }

        if (! is_string($storedPath) || $storedPath === '') {
            throw new RuntimeException('Failed to store uploaded file.');
        }

        $mimeType = $file->getMimeType();
        $size = $file->getSize();
        if (! is_int($size) || $size <= 0) {
            $size = $disk->size($storedPath);
        }

        return [
            'disk' => $this->disk,
            'directory' => $targetDirectory,
            'file_name' => $targetFileName,
            'file_path' => $storedPath,
            'extension' => $extension,
            'mime_type' => is_string($mimeType) && $mimeType !== '' ? $mimeType : 'application/octet-stream',
            'file_size' => (int) $size,
            'url' => $this->buildUrl($storedPath),
            'already_exists' => $alreadyExists,
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
        $baseName = preg_replace('/[\\s\\/\\\\]+/', '-', $baseName) ?? $baseName;
        $baseName = preg_replace('/[^\\p{L}\\p{N}-]+/u', '', $baseName) ?? $baseName;
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
        $cleaned = preg_replace('/[\\x{FE0E}\\x{FE0F}\\x{200D}\\x{20E3}]/u', '', $value) ?? $value;
        $cleaned = preg_replace('/[\\p{Extended_Pictographic}\\p{So}]/u', '', $cleaned) ?? $cleaned;

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
        $baseUrl = trim((string) config('filesystems.disks.' . $this->disk . '.url', ''), '/');

        return $baseUrl !== ''
            ? $baseUrl . '/' . ltrim($relativePath, '/')
            : '/' . ltrim($relativePath, '/');
    }
}
