<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray($request): array
    {
        $base = [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'updated_at' => $this->updated_at,
        ];

        if ($request->route()?->getName() === 'admin.pages.show') {
            return array_merge($base, [
                'content' => $this->content,
                'excerpt' => $this->excerpt,
                'meta_title' => $this->meta_title,
                'meta_keywords' => $this->meta_keywords,
                'meta_description' => $this->meta_description,
                'meta' => [
                    'excerpt' => $this->excerpt,
                    'meta_title' => $this->meta_title,
                    'meta_keywords' => $this->meta_keywords,
                    'meta_description' => $this->meta_description,
                ],
                'created_at' => $this->created_at,
            ]);
        }

        return $base;
    }
}
