<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
{
    public function toArray($request): array
    {
        if ($request->route()?->getName() === 'admin.attribute.detail' || $this->relationLoaded('attributes')) {
            return $this->detailResponse();
        }

        return $this->listResponse();
    }

    private function listResponse(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'attributes_count' => (int) ($this->attributes_count ?? 0),
            'created_at' => $this->created_at,
        ];
    }

    private function detailResponse(): array
    {
        $payload = $this->listResponse();

        $payload['attributes'] = $this->relationLoaded('attributes')
            ? $this->attributes->map(static function ($attribute): array {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'type' => $attribute->type,
                    'values' => is_array($attribute->values) ? $attribute->values : [],
                    'use_for_variant' => (bool) $attribute->use_for_variant,
                    'use_in_filter' => (bool) $attribute->use_in_filter,
                    'created_at' => $attribute->created_at,
                ];
            })->values()->all()
            : [];

        return $payload;
    }
}
