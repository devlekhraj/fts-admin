<?php

declare(strict_types=1);

namespace App\Domains\Campaign\Actions;

use App\Domains\Campaign\DTOs\CampaignImageStoreData;
use App\Domains\Campaign\Models\Campaign;
use App\Domains\File\Services\FileUploadService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class CampaignImageStoreAction
{
    public function __construct(private readonly FileUploadService $fileUploadService)
    {
    }

    public function execute(Campaign $campaign, CampaignImageStoreData $data): void
    {
        $uploadResult = $this->fileUploadService->uploadImageAsWebp($data->image, 'campaigns');
        $fileData = $uploadResult['file_data'] ?? null;

        if (! is_array($fileData) || ! isset($fileData['id'])) {
            throw new RuntimeException('Upload did not return a valid file.');
        }

        DB::table('file_usages')->insert([
            'file_id' => $fileData['id'],
            'usage_type' => 'campaigns',
            'usage_id' => $campaign->id,
            'alt_text' => $data->altText ?? $campaign->title,
            'meta' => json_encode(array_filter([
                'link' => $data->link ?? null,
                'start_date' => $data->startDate ?? null,
                'end_date' => $data->endDate ?? null,
                'is_default' => false,
            ], static fn ($v) => ! is_null($v))),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

