<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

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
            'is_proceed' => (bool) $this->is_processed,
            'items_count' => (int) ($this->items_count ?? 0),
            'updated_at' => $this->updated_at,
        ];
    }
}
