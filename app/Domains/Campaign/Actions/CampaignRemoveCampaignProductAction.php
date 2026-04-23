<?php

declare(strict_types=1);

namespace App\Domains\Campaign\Actions;

use App\Domains\Campaign\Models\CampaignProduct;

final class CampaignRemoveCampaignProductAction
{
    public function execute(string $id): void
    {
        $product = CampaignProduct::query()->findOrFail($id);
        $product->delete();
    }
}

