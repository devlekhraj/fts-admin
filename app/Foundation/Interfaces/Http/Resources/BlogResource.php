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

        if ($request->route()?->getName() === 'admin.blogs.show') {
            return $this->showResponse($defaultFile, $categoryName);
        }

        return $this->listResponse($defaultFile, $categoryName);
    }

    private function listResponse($defaultFile, ?string $categoryName): array
    {
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

    private function showResponse($defaultFile, ?string $categoryName): array
    {
        $data = $this->resource->toArray();
        $files = [];

        if ($this->relationLoaded('files')) {
            $files = $this->files->map(static function ($file) {
                $meta = $file->pivot?->meta;
                if (is_string($meta)) {
                    $decoded = json_decode($meta, true);
                    $meta = json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [];
                }
                if (!is_array($meta)) {
                    $meta = [];
                }

                return [
                    'id' => $file->id,
                    'url' => $file->url,
                    'title' => $file->pivot?->title,
                    'alt_text' => $file->pivot?->alt_text,
                    'meta' => $meta,
                    'file_size' => is_numeric($file->file_size ?? null) ? (float) $file->file_size : null,
                    'size' => is_numeric($file->file_size ?? null) ? (float) $file->file_size : null,
                    'height' => is_numeric($file->height ?? null) ? (float) $file->height : null,
                    'width' => is_numeric($file->width ?? null) ? (float) $file->width : null,
                ];
            })->values()->all();
        }

        $data['thumb'] = $defaultFile?->url;
        $data['status'] = (bool) ($data['status'] ?? $this->status);
        $data['published_at'] = $this->publish_date;
        $data['category_name'] = $categoryName;
        $data['default_file'] = $defaultFile?->toArray();
        $data['files'] = $files;

        return $data;
    }
}
