<?php

declare(strict_types=1);

namespace App\Domains\Campaign\Actions;

use App\Domains\Campaign\Models\Campaign;

final class CampaignDetailAction
{
    public function execute(string $id): Campaign
    {
        return Campaign::query()
            ->with(['files', 'defaultFile'])
            ->findOrFail($id);
    }
}

