<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        $items = $this->items ?? [];
        $mappedItems = collect($items)->map(function ($item) {
            $qty = (int) ($item->quantity ?? 1);
            $price = (float) ($item->price ?? 0);
            $lineTotal = $qty * $price;

            return [
                'id' => $item->id,
                'description' => $item->product->name ?? '-',
                'product_attributes' => $item->product_attributes,
                'thumb' => $item->product->thumb ?? $item->product->image ?? null,
                'price' => $price,
                'quantity' => $qty,
                'line_total' => $lineTotal,
            ];
        });

        $total = $mappedItems->sum('line_total');

        return [
            'id' => $this->id,
            'customer' => [
                'name' => $this->user->name ?? $this->user->email ?? null,
                'address' => $this->user->address ?? null,
                'avatar' => $this->user->avatar_url ?? $this->user->avatar ?? null,
            ],
            'is_proceed' => (bool) $this->is_processed,
            'items_count' => (int) ($this->items_count ?? $mappedItems->count()),
            'updated_at' => $this->updated_at,
            'total' => $total,
            'items' => $mappedItems,
        ];
    }
}
