<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductBrandResource extends JsonResource
{
    public function toArray($request): array
    {
        $defaultFile = null;
        if ($this->relationLoaded('defaultFile')) {
            $defaultFile = $this->defaultFile->first();
        }

        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status,
            'total_products' => (int) ($this->products_count ?? 0),
            'created_at' => $this->created_at,
            'logo' => $defaultFile?->url,
        ];
    }
}
