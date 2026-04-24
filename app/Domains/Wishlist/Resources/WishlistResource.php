<?php

declare(strict_types=1);

namespace App\Domains\Wishlist\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    public function toArray($request): array
    {
        $userName = null;
        $userAvatar = null;
        if ($this->relationLoaded('user') && $this->user) {
            $userName = $this->user->name ?? $this->user->email ?? null;
            $userAvatar = $this->user->avatar_url ?? $this->user->avatar ?? null;
        }

        $productName = null;
        $productSku = null;
        $productThumb = null;
        if ($this->relationLoaded('product') && $this->product) {
            $productName = $this->product->name ?? null;
            $productSku = $this->product->sku ?? null;
            $productThumb = $this->product->thumb ?? $this->product->image ?? null;
        }

        return [
            'id' => (string) ($this->id ?? "{$this->user_id}-{$this->product_id}"),
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'customer' => [
                'name' => $userName,
                'avatar' => $userAvatar,
            ],
            'product' => [
                'id' => $this->product_id,
                'name' => $productName,
                'sku' => $productSku,
                'thumb' => $productThumb,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
