<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use App\Foundation\Shared\Support\Formatters\ByteSizeFormatter;
use App\Foundation\Shared\Support\Formatters\FileDimensionFormatter;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogCategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        $defaultFile = null;
        if ($this->relationLoaded('defaultFile')) {
            $defaultFile = $this->defaultFile->first();
        }

        if ($request->route()?->getName() === 'admin.blog-categories.show') {
            return $this->showResponse($defaultFile);
        }

        return $this->listResponse($defaultFile);
    }

    private function listResponse($defaultFile): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'thumb' => $defaultFile?->url,
            'created_at' => $this->created_at,
            'status' => (bool) $this->status,
        ];
    }

    private function showResponse($defaultFile): array
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
                if (! is_array($meta)) {
                    $meta = [];
                }
                $meta = [
                    'is_active' => in_array($meta['is_active'] ?? false, [true, 1, '1', 'true'], true),
                    'is_default' => in_array($meta['is_default'] ?? false, [true, 1, '1', 'true'], true),
                ];

                $fileSize = ByteSizeFormatter::format($file->file_size ?? null);
                $fileDimension = FileDimensionFormatter::format($file->width ?? null, $file->height ?? null);

                return [
                    'id' => $file->pivot?->id,
                    'url' => $file->url,
                    'alt_text' => $file->pivot?->alt_text,
                    'meta' => $meta,
                    'size_info' => "{$fileSize} | {$fileDimension}",
                ];
            })->values()->all();
        }

        $data['thumb'] = $defaultFile?->url;
        $data['status'] = (bool) ($data['status'] ?? $this->status);
        $data['default_file'] = $defaultFile?->toArray();
        $data['files'] = $files;

        return $data;
    }
}
