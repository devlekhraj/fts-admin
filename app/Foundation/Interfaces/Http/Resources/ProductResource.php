<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use App\Foundation\Shared\Support\Formatters\ByteSizeFormatter;
use App\Foundation\Shared\Support\Formatters\FileDimensionFormatter;
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
        $images = [];
        $variants = [];
        $attribute = null;
        $brand = null;

        if ($this->relationLoaded('files')) {
            $images = $this->files->map(static function ($file) {
                $meta = $file->pivot?->meta;
                if (is_string($meta)) {
                    $decoded = json_decode($meta, true);
                    $meta = json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [];
                }
                if (!is_array($meta)) {
                    $meta = [];
                }

                $meta = [
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

        if ($this->relationLoaded('brand') && $this->brand) {
            $brand = [
                'id' => $this->brand->id,
                'name' => $this->brand->name,
                'slug' => $this->brand->slug,
                'thumb' => $this->brand->logo,
            ];
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

                        $meta = [
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

        if ($this->relationLoaded('attribute') && $this->attribute) {
            $attributeItems = [];
            if ($this->attribute->relationLoaded('attributes')) {
                $attributeItems = $this->attribute->attributes->map(static function ($item): array {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'type' => $item->type,
                        'values' => is_array($item->values) ? $item->values : [],
                        'use_for_variant' => (bool) $item->use_for_variant,
                        'use_in_filter' => (bool) $item->use_in_filter,
                    ];
                })->values()->all();
            }

            $attribute = [
                'id' => $this->attribute->id,
                'name' => $this->attribute->name,
                'attributes' => $attributeItems,
            ];
        }

        return [
            'overview' =>[
                "name" => $this->name,
                "slug" => $this->slug,
                'thumb' => $defaultFile?->url,
                'status' => (bool) ($data['status'] ?? $this->status),
                'emi_enabled' => (bool) ($data['emi_enabled'] ?? $this->emi_enabled),
                'sku' => $this->sku,
            ],
            'brand' => $brand,
            'meta' => [
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
            ],
            'description' => [
                'description' => $data['description'] ?? null,
                'short_desc' => $data['short_desc'] ?? null,
                'highlights' => $data['highlights'] ?? null,
                'warranty_description' => $data['warranty_description'] ?? null,
            ],
            'pre_order' => [
                'availability' => (bool) ($data['pre_order'] ?? $this->pre_order),
                'price' => is_numeric($data['pre_order_price'] ?? null) ? (float) $data['pre_order_price'] : null,
            ],
            'price' => [
                'current_price' => is_numeric($data['price'] ?? null) ? (float) $data['price'] : null,
                'compare_price' => is_numeric($data['original_price'] ?? null) ? (float) $data['original_price'] : null,
                'quantity' => is_numeric($data['quantity'] ?? null) ? (int) $data['quantity'] : null,
            ],
            'images' => $images,
            'variants' => $variants,
            'attributes' => $this->attributes,
            'schema_jsonld' => $this->custom_code,
        ];
    }
}
