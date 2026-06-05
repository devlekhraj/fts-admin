<?php

declare(strict_types=1);

namespace App\Domains\Product\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductPriceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'price' => ['required', 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'pre_order' => ['required', 'boolean'],
            'pre_order_price' => [
                Rule::requiredIf(fn (): bool => $this->boolean('pre_order')),
                'nullable',
                'numeric',
                'min:0',
            ],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['required', 'integer', 'exists:product_variants,id'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.quantity' => ['required', 'integer', 'min:0'],
        ];
    }
}
