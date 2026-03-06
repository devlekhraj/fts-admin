<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        $defaultFile = null;
        if ($this->relationLoaded('defaultFile')) {
            $defaultFile = $this->defaultFile->first();
        }

        if ($request->route()?->getName() === 'admin.products.show') {
            return $this->showResponse($defaultFile);
        }

        return $this->listResponse($defaultFile);
    }

    private function listResponse($defaultFile): array
    {
        $variantsCount = is_numeric($this->variants_count ?? null)
            ? (int) $this->variants_count
            : ($this->relationLoaded('variants') ? $this->variants->count() : 0);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => (bool) $this->status,
            'emi_enabled' => (bool) $this->emi_enabled,
            'variants_count' => $variantsCount,
            'created_at' => $this->created_at,
            'thumb' => $defaultFile?->url,
        ];
    }

    private function showResponse($defaultFile): array
    {
        $data = $this->resource->toArray();
        $files = [];
        $variants = [];

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

        if ($this->relationLoaded('variants')) {
            $variants = $this->variants->map(static function ($variant) {
                $variantFiles = [];
                if ($variant->relationLoaded('files')) {
                    $variantFiles = $variant->files->map(static function ($file) {
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
                    'id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'quantity' => is_numeric($variant->quantity ?? null) ? (int) $variant->quantity : null,
                    'price' => is_numeric($variant->price ?? null) ? (float) $variant->price : null,
                    'attributes' => is_array($variant->attributes) ? $variant->attributes : [],
                    'images' => $variantFiles,
                    'created_at' => $variant->created_at,
                    'updated_at' => $variant->updated_at,
                ];
            })->values()->all();
        }

        $data['thumb'] = $defaultFile?->url;
        $data['status'] = (bool) ($data['status'] ?? $this->status);
        $data['emi_enabled'] = (bool) ($data['emi_enabled'] ?? $this->emi_enabled);
        $data['default_file'] = $defaultFile?->toArray();
        $data['files'] = $files;
        $data['variants'] = $variants;

        return $data;
    }
}
