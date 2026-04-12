<?php

declare(strict_types=1);

namespace App\Foundation\Application\File\Handlers;

use App\Foundation\Application\File\DTO\ImageDto;
use App\Foundation\Shared\Application\Contracts\FileUploadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

final class ImageUploadHandler
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {}

    public function handle(ImageDto $dto): array
    {
        $fileId = $dto->imageId;
        $fileData = null;

        if ($dto->source === 'upload' && $dto->file) {
            $upload = $this->storeOriginalImage($dto);
            $fileData = $upload['file_data'];
            $fileId = $upload['file_id'];
        }

        if (! $fileId) {
            throw new RuntimeException('Image ID is required for assignment.');
        }

        $meta = $dto->meta;
        $meta['is_default'] = $dto->isDefault;

        $title = $meta['caption'] ?? $meta['title'] ?? null;
        if (is_string($title)) {
            $title = trim($title);
        }

        return DB::transaction(function () use ($fileId, $dto, $meta, $title, $fileData) {
            if ($dto->isDefault) {
                DB::table('file_usages')
                    ->where('usage_type', $dto->usageType)
                    ->where('usage_id', $dto->usageId)
                    ->update(['meta->is_default' => false]);
            }

            DB::table('file_usages')->upsert(
                [[
                    'file_id' => $fileId,
                    'usage_type' => $dto->usageType,
                    'usage_id' => $dto->usageId,
                    'title' => is_string($title) && $title !== '' ? $title : null,
                    'alt_text' => $dto->altText,
                    'meta' => json_encode($meta, JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]],
                ['file_id', 'usage_type', 'usage_id'],
                ['title', 'alt_text', 'meta', 'updated_at']
            );

            $usage = DB::table('file_usages')
                ->where('file_id', $fileId)
                ->where('usage_type', $dto->usageType)
                ->where('usage_id', $dto->usageId)
                ->first();

            return [
                'file_id' => $fileId,
                'file' => $fileData,
                'usage' => $usage ? (array) $usage : null,
            ];
        });
    }

    /**
     * Store the uploaded image as-is on the CDN disk and persist a file row.
     * Returns file_id and hydrated file_data array.
     */
    private function storeOriginalImage(ImageDto $dto): array
    {
        $disk = Storage::disk('cdn');

        $directory = $this->normalizeDirectory((string) ($dto->directory ?? 'uploads'));
        $safeName = $this->sanitizeFileName((string) $dto->file->getClientOriginalName());

        $extension = strtolower(pathinfo($safeName, PATHINFO_EXTENSION) ?: (string) $dto->file->extension());
        $extension = $extension !== '' ? $extension : 'jpg';

        $baseName = pathinfo($safeName, PATHINFO_FILENAME) ?: 'image';
        $storedFileName = $baseName.'-'.Str::uuid().'.'.$extension;
        $relativePath = $directory !== '' ? $directory.'/'.$storedFileName : $storedFileName;

        $bytes = $dto->file->get();
        if (! is_string($bytes) || $bytes === '') {
            throw new RuntimeException('Unable to read uploaded image bytes.');
        }

        $contentHash = hash('sha256', $bytes);
        $existing = DB::table('files')->where('content_hash', $contentHash)->first();
        if ($existing) {
            return [
                'file_id' => (int) $existing->id,
                'file_data' => (array) $existing,
            ];
        }

        if (! $disk->put($relativePath, $bytes)) {
            throw new RuntimeException('Failed to store uploaded image.');
        }

        $imageInfo = @getimagesizefromstring($bytes);
        $width = is_array($imageInfo) && isset($imageInfo[0]) ? (float) $imageInfo[0] : null;
        $height = is_array($imageInfo) && isset($imageInfo[1]) ? (float) $imageInfo[1] : null;

        $now = now();
        $fileId = DB::table('files')->insertGetId([
            'key' => md5($relativePath),
            'file_name' => $storedFileName,
            'file_path' => $relativePath,
            'extension' => $extension,
            'seq_no' => null,
            'mime_type' => $dto->file->getMimeType(),
            'file_size' => strlen($bytes),
            'height' => $height,
            'width' => $width,
            'disk' => 'cdn',
            'content_hash' => $contentHash,
            'meta' => json_encode([
                'directory' => $directory !== '' ? $directory : null,
                'original_name' => $dto->file->getClientOriginalName(),
            ], JSON_UNESCAPED_SLASHES),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $fileRow = DB::table('files')->where('id', $fileId)->first();
        $fileData = $fileRow ? (array) $fileRow : null;

        if (! $fileData) {
            throw new RuntimeException('Uploaded file could not be resolved.');
        }

        return [
            'file_id' => $fileId,
            'file_data' => $fileData,
        ];
    }

    private function normalizeDirectory(string $directory): string
    {
        $cleaned = preg_replace('#[^A-Za-z0-9/_-]#', '', trim($directory));
        if (! is_string($cleaned) || $cleaned === '') {
            return 'uploads';
        }

        $cleaned = preg_replace('#/+#', '/', $cleaned);
        $cleaned = trim((string) $cleaned, '/');

        return $cleaned;
    }

    private function sanitizeFileName(string $fileName): string
    {
        $fileName = trim($fileName);
        if ($fileName === '') {
            return 'image';
        }

        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $baseName = is_string($baseName) ? $baseName : '';
        $extension = is_string($extension) ? $extension : '';

        $baseName = str_replace('_', '-', $baseName);
        $baseName = preg_replace('/[\s\\/]+/', '-', $baseName) ?? $baseName;
        $baseName = preg_replace('/[^A-Za-z0-9-]+/', '', $baseName) ?? $baseName;
        $baseName = preg_replace('/-+/', '-', $baseName) ?? $baseName;
        $baseName = trim($baseName, '-');

        if ($baseName === '') {
            $baseName = 'image';
        }

        if ($extension === '') {
            return $baseName;
        }

        return $baseName.'.'.$extension;
    }
}
