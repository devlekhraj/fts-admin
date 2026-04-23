<?php

declare(strict_types=1);

namespace App\Domains\File\Services;

use App\Domains\File\Actions\UpdateFileUsageAction;
use App\Domains\File\DTOs\UpdateFileUsageData;
use App\Domains\File\Models\FileUsage;
use App\DTO\ActionResultDto;

final class FileUsageService
{
    public function __construct(
        private readonly UpdateFileUsageAction $updateFileUsageAction
    ) {}

    public function index(string $usageType, string $usageId): array
    {
        $usages = FileUsage::query()
            ->with('file')
            ->where('usage_type', $usageType)
            ->where('usage_id', $usageId)
            ->latest('id')
            ->get()
            ->map(function ($usage) {
                return [
                    'id' => $usage->id,
                    'file_id' => $usage->file_id,
                    'usage_type' => $usage->usage_type,
                    'usage_id' => $usage->usage_id,
                    'title' => $usage->title,
                    'alt_text' => $usage->alt_text,
                    'meta' => $usage->meta,
                    'url' => $usage->file?->url,
                    'size_info' => $usage->file ? "{$usage->file->width}x{$usage->file->height} (" . round($usage->file->file_size / 1024, 2) . " KB)" : null,
                    'created_at' => $usage->created_at,
                    'updated_at' => $usage->updated_at,
                ];
            });

        return [
            'data' => $usages,
        ];
    }

    public function update(UpdateFileUsageData $data): ActionResultDto
    {
        return $this->updateFileUsageAction->execute($data);
    }

    public function delete(int $fileUsageId): bool
    {
        $fileUsage = FileUsage::query()->find($fileUsageId);
        if (! $fileUsage) {
            return false;
        }

        $fileUsage->delete();

        return true;
    }
}

