<?php

declare(strict_types=1);

namespace App\Domains\Campaign\Services;

use App\Domains\Campaign\Actions\CampaignImageStoreAction;
use App\Domains\Campaign\DTOs\CampaignImageStoreData;
use App\Domains\Campaign\Models\Campaign;

final class CampaignImageService
{
    public function __construct(private readonly CampaignImageStoreAction $storeAction)
    {
    }

    public function store(string $campaignId, CampaignImageStoreData $data): void
    {
        $campaign = Campaign::query()->findOrFail($campaignId);
        $this->storeAction->execute($campaign, $data);
    }
}

