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
            'status' => (bool) $this->status,
            'updated_at' => $this->updated_at,
        ];

        if ($request->route()?->getName() === 'admin.pages.show') {
            return array_merge($base, [
                'content' => $this->content,
                'meta' => is_array($this->meta) ? $this->meta : json_decode((string) $this->meta, true),
                'created_at' => $this->created_at,
            ]);
        }

        return $base;
    }
}
