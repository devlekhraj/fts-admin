<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use App\Foundation\Shared\Support\Formatters\ByteSizeFormatter;
use App\Foundation\Shared\Support\Formatters\FileDimensionFormatter;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public function toArray($request): array
    {
        if ($request->route()?->getName() === 'admin.payment-methods.show') {
            return $this->showResponse();
        }

        return $this->listResponse();
    }

    private function listResponse(): array
    {
        $defaultFile = $this->defaultFile->first() ?? $this->files->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => (bool) $this->status,
            'test_mode' => (bool) $this->test_mode,
            'is_international' => (bool) $this->is_international,
            'thumb' => $defaultFile?->url,
            'image_counts' => (int) $this->files_count,
            'created_at' => $this->created_at,
        ];
    }

    private function showResponse(): array
    {
        $data = $this->resource->toArray();

        $images = [];
        if ($this->relationLoaded('files')) {
            $images = $this->files->map(static function ($item): array {

                $fileSize = ByteSizeFormatter::format($item->file_size ?? null);
                $fileDimension = FileDimensionFormatter::format($item->width ?? null, $item->height ?? null);
                $meta = is_array($item->pivot->meta) ? $item->pivot->meta : json_decode($item->pivot->meta ?? '[]', true);

                return [
                    'id' => $item->pivot->id,
                    'url' => $item->url,
                    'title' => $item->pivot->title,
                    'alt_text' => $item->pivot->alt_text,
                    'status' => (bool) $item->status,
                    'meta' => [
                        'is_default' => (bool) ($meta['is_default'] ?? false),
                    ],
                    'size_info' => "{$fileSize} | {$fileDimension}",
                ];
            })->values()->all();
        }

        $defaultFile = $this->defaultFile->first();

        return [

            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => (bool) $this->status,
            'test_mode' => (bool) $this->test_mode,
            'is_international' => (bool) $this->is_international,
            'thumb' => $defaultFile?->url,
            'config' => $this->decodeConfig($data['config'] ?? null),
            'images' => $images,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function decodeConfig($config): array
    {
        if (is_array($config)) {
            return $config;
        }

        if (is_string($config)) {
            $decoded = json_decode($config, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }
}
