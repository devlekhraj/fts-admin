<?php

declare(strict_types=1);

namespace App\Domains\File\Actions;

use App\Domains\File\DTOs\ImageAssignData;
use App\Domains\File\Services\FileUploadService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class ImageUploadAction
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {}

    public function execute(ImageAssignData $data): array
    {
        $fileId = $data->imageId;
        $fileData = null;

        if ($data->source === 'upload' && $data->file) {
            $directory = is_string($data->directory) && trim($data->directory) !== ''
                ? trim($data->directory)
                : 'uploads';

            $upload = $this->fileUploadService->upload(
                file: $data->file,
                directory: $directory,
                preferredFileName: null,
                dedupeByContentHash: true,
            );

            $fileData = is_array($upload['file_data'] ?? null) ? $upload['file_data'] : null;
            $fileId = (int) ($upload['file_id'] ?? 0);
        }

        if (! $fileId) {
            throw new RuntimeException('Image ID is required for assignment.');
        }

        $meta = $data->meta;
        $meta['is_default'] = $data->isDefault;

        $title = $meta['caption'] ?? $meta['title'] ?? null;
        if (is_string($title)) {
            $title = trim($title);
        }

        return DB::transaction(function () use ($fileId, $data, $meta, $title, $fileData) {
            if ($data->isDefault) {
                DB::table('file_usages')
                    ->where('usage_type', $data->usageType)
                    ->where('usage_id', $data->usageId)
                    ->update(['meta->is_default' => false]);
            }

            DB::table('file_usages')->upsert(
                [[
                    'file_id' => $fileId,
                    'usage_type' => $data->usageType,
                    'usage_id' => $data->usageId,
                    'title' => is_string($title) && $title !== '' ? $title : null,
                    'alt_text' => $data->altText,
                    'meta' => json_encode(array_filter($meta, fn($v) => ! is_null($v)), JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]],
                ['file_id', 'usage_type', 'usage_id'],
                ['title', 'alt_text', 'meta', 'updated_at']
            );

            $usage = DB::table('file_usages')
                ->where('file_id', $fileId)
                ->where('usage_type', $data->usageType)
                ->where('usage_id', $data->usageId)
                ->first();

            return [
                'file_id' => $fileId,
                'file' => $fileData,
                'usage' => $usage ? (array) $usage : null,
            ];
        });
    }
}
