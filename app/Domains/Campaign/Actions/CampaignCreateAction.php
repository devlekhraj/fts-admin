<?php

declare(strict_types=1);

namespace App\Domains\Campaign\Actions;

use App\Domains\Campaign\DTOs\CampaignCreateData;
use App\Domains\Campaign\Models\Campaign;

final class CampaignCreateAction
{
    public function execute(CampaignCreateData $data): Campaign
    {
        return Campaign::query()->create([
            'title' => $data->title,
            'slug' => $data->slug,
            'start_date' => $data->startDate,
            'end_date' => $data->endDate,
            'is_published' => $data->isPublished,
        ]);
    }
}

