<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'unique:products,slug,' . $id],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'max:100'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'brand_id' => ['nullable', 'integer', 'exists:product_brands,id'],
            'vendor_id' => ['nullable', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'pre_order' => ['sometimes', 'boolean'],
            'pre_order_price' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'highlights' => ['nullable', 'string'],
            'product_video_url' => ['nullable', 'url'],
            'weight' => ['nullable', 'numeric'],
            'length' => ['nullable', 'numeric'],
            'width' => ['nullable', 'numeric'],
            'height' => ['nullable', 'numeric'],
            'status' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'emi_enabled' => ['sometimes', 'boolean'],
            'attributes' => ['nullable', 'array'],
            'attribute_class_id' => ['nullable', 'integer'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string'],
            'custom_code' => ['nullable', 'string'],
            'warranty_description' => ['nullable', 'string'],
        ];
    }
}
