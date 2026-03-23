<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use App\Foundation\Shared\Support\Formatters\ByteSizeFormatter;
use App\Foundation\Shared\Support\Formatters\FileDimensionFormatter;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    public function toArray($request): array
    {
        $current_time = now();
        $is_active = $this->is_published &&
            $this->start_date <= $current_time &&
            $this->end_date >= $current_time;

        $files = [];
        if ($this->relationLoaded('files')) {
            $files = $this->files->map(function ($file) {
                $rawMeta = is_array($file->pivot?->meta) ? $file->pivot?->meta : [];
                $meta = [
                    'is_default' => (bool) ($rawMeta['is_default'] ?? false),
                ];

                $fileSize = ByteSizeFormatter::format($file->file_size ?? $file->size ?? null);
                $fileDimension = FileDimensionFormatter::format($file->width ?? null, $file->height ?? null);

                return [
                    'id' => $file->pivot?->id,
                    'file_id' => $file->id,
                    'url' => $file->url,
                    'title' => $file->pivot?->title,
                    'alt_text' => $file->pivot?->alt_text,
                    'meta' => $meta,
                    'size_info' => "{$fileSize} | {$fileDimension}",
                ];
            })->values()->all();
        }

        $thumb = null;
        if ($this->relationLoaded('defaultFile')) {
            $defaultFile = $this->defaultFile->first();
            if ($defaultFile) {
                $thumb = [
                    'url' => $defaultFile->url,
                    'alt_text' => $defaultFile->pivot?->alt_text ?? $defaultFile->alt_text,
                ];
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_published' => (bool) $this->is_published,
            'status' => $is_active ? 'active' : 'inactive',
            'thumb' => $thumb,
            'files' => $files,
            'products_count' => $this->whenCounted('products'),
        ];
    }
}
