<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
{
    public function toArray($request): array
    {
        $defaultFile = null;
        $categoryName = null;

        if ($this->relationLoaded('defaultFile')) {
            $defaultFile = $this->defaultFile->first();
        }
        if ($this->relationLoaded('category')) {
            $categoryName = $this->category?->title;
        }

        return [
            'id' => $this->id,
            'title' => $this->title ?? $this->name,
            'slug' => $this->slug,
            'status' => (bool) $this->status,
            'published_at' => $this->publish_date,
            'category_name' => $categoryName,
            'thumb' => $defaultFile?->url,
        ];
    }
}
