<?php

declare(strict_types=1);

namespace App\Domains\Product\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class ProductGiftResource extends JsonResource
{
    public function toArray($request): array
    {
        $defaultFile = null;
        if ($this->relationLoaded('defaultFile')) {
            $defaultFile = $this->defaultFile->first();
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'thumb' => $defaultFile?->url,
            'price' => is_numeric($this->price ?? null) ? (float) $this->price : null,
            'status' => (bool) ($this->status ?? false),
        ];
    }
}
