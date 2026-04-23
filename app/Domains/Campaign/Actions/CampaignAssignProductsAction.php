<?php

declare(strict_types=1);

namespace App\Domains\Campaign\Actions;

use App\Domains\Campaign\DTOs\CampaignAssignProductsData;
use App\Domains\Campaign\Models\Campaign;
use Illuminate\Database\Eloquent\Collection;

final class CampaignAssignProductsAction
{
    public function execute(Campaign $campaign, CampaignAssignProductsData $data): Collection
    {
        $productIds = $data->productIds;

        $existingIds = $campaign->products()->pluck('product_id')->toArray();
        $newProductIds = array_diff($productIds, $existingIds);

        if (! empty($newProductIds)) {
            $campaign->products()->createMany(
                collect($newProductIds)->map(fn (int $productId): array => [
                    'product_id' => $productId,
                    'discount_type' => $data->discountType ?? 1,
                    'discount_value' => $data->discountValue ?? 0,
                ])->toArray()
            );
        }

        return $campaign->products()->get();
    }
}

