<?php

declare(strict_types=1);

namespace App\Domains\Campaign\Actions;

use App\Domains\Campaign\Models\Campaign;

final class CampaignTogglePublishedAction
{
    public function execute(Campaign $campaign, bool $isPublished): Campaign
    {
        $campaign->is_published = $isPublished ? 1 : 0;
        $campaign->save();

        return $campaign;
    }
}

