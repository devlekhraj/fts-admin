<?php

declare(strict_types=1);

namespace App\Domains\Campaign\Actions;

use App\Domains\Campaign\DTOs\CampaignProductUpdateData;
use App\Domains\Campaign\Models\CampaignProduct;

final class CampaignUpdateCampaignProductAction
{
    public function execute(string $id, CampaignProductUpdateData $data): CampaignProduct
    {
        $product = CampaignProduct::query()->findOrFail($id);
        $product->update($data->toArray());

        return $product;
    }
}

