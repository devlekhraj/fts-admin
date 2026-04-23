<?php

declare(strict_types=1);

namespace App\Domains\Campaign\Actions;

use App\Domains\Campaign\DTOs\CampaignUpdateData;
use App\Domains\Campaign\Models\Campaign;

final class CampaignUpdateAction
{
    public function execute(Campaign $campaign, CampaignUpdateData $data): Campaign
    {
        $campaign->update($data->attributes);

        return $campaign;
    }
}

