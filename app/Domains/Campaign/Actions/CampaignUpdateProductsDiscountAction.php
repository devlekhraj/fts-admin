<?php

declare(strict_types=1);

namespace App\Domains\Campaign\Actions;

use App\Domains\Campaign\DTOs\CampaignDiscountUpdateData;
use App\Domains\Campaign\Models\Campaign;

final class CampaignUpdateProductsDiscountAction
{
    public function execute(Campaign $campaign, CampaignDiscountUpdateData $data): void
    {
        $campaign->products()->update($data->toArray());
    }
}

