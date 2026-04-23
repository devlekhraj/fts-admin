<?php

declare(strict_types=1);

namespace App\Domains\Campaign\Actions;

use App\Domains\Campaign\Models\Campaign;

final class CampaignDeleteAction
{
    public function execute(Campaign $campaign): void
    {
        $campaign->delete();
    }
}

