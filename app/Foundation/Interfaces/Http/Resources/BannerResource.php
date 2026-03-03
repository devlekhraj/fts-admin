<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray($request): array
    {
        $defaultFile = null;
        if ($this->relationLoaded('defaultFile')) {
            $defaultFile = $this->defaultFile->first();
        }

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

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => (bool) $this->status,
            'created_at' => $this->created_at,
            'total_images' => (int) ($this->total_images ?? 0),
            'thumb' => $defaultFile?->url,
            'files' => $this->when($this->relationLoaded('files'), $files),
        ];
    }
}
