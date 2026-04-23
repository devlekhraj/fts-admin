<?php

declare(strict_types=1);

namespace App\Domains\Campaign\Actions;

use App\Domains\Campaign\Models\Campaign;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class CampaignListProductsAction
{
    public function execute(Campaign $campaign, ?string $name, int $perPage): array
    {
        $productsQuery = $campaign->products()->with('product')->orderByDesc('created_at');

        if (is_string($name) && trim($name) !== '') {
            $needle = trim($name);
            $productsQuery->whereHas('product', function ($q) use ($needle): void {
                $q->where('name', 'like', '%'.$needle.'%');
            });
        }

        /** @var LengthAwarePaginator $paginated */
        $paginated = $productsQuery->paginate($perPage);

        $data = $paginated->getCollection()->transform(function ($discountProduct): array {
            $product = $discountProduct->product;

            $discountedPrice = $this->calculateDiscountedPrice(
                (float) $product->price,
                (int) $discountProduct->discount_type,
                (float) $discountProduct->discount_value
            );

            return [
                'id' => $discountProduct->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'thumb' => [
                    'url' => $product->defaultFile->first()?->url,
                    'alt_text' => $product->defaultFile->first()?->alt_text,
                ],
                'price' => [
                    'original_price' => $product->price,
                    'discounted_price' => $discountedPrice,
                ],
                'discount' => [
                    'type' => $discountProduct->discount_type_label,
                    'value' => $discountProduct->discount_value,
                ],
            ];
        });

        return [
            'data' => $data,
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
            ],
        ];
    }

    private function calculateDiscountedPrice(float $originalPrice, int $discountType, float $discountValue): float
    {
        if ($discountType === 1) {
            return max(0, $originalPrice - $discountValue);
        }

        if ($discountType === 2) {
            return max(0, $originalPrice - ($originalPrice * $discountValue / 100));
        }

        return $originalPrice;
    }
}

