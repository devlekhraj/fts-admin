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

        if ($request->route()?->getName() === 'admin.banners.show') {
            return $this->showResponse($defaultFile);
        }

        return $this->listResponse($defaultFile);
    }

    private function listResponse($defaultFile): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => (bool) $this->status,
            'created_at' => $this->created_at,
            'total_images' => (int) ($this->total_images ?? 0),
            'thumb' => $defaultFile?->url,
        ];
    }

    private function showResponse($defaultFile): array
    {
        $files = [];
        if ($this->relationLoaded('files')) {
            $files = $this->files->map(static function ($file) {
                $meta = is_array($file->pivot?->meta) ? $file->pivot?->meta : [];

                return [
                    'id' => $file->pivot?->id,
                    'file_id' => $file->id,
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
            'files' => $files,
        ];
    }
}
