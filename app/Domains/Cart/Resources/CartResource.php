<?php

declare(strict_types=1);

namespace App\Domains\Cart\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'customer' => [
                'name' => $this->user->name ?? $this->user->email ?? null,
                'avatar' => $this->user->avatar_url ?? $this->user->avatar ?? null,
            ],
            'amount' => (float) $this->items()->sum('price'),
            'is_proceed' => (bool) $this->is_processed,
            'items_count' => (int) ($this->items_count ?? 0),
            'updated_at' => $this->updated_at,
        ];
    }
}
