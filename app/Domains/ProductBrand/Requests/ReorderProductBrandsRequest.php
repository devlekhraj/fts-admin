<?php

declare(strict_types=1);

namespace App\Domains\ProductBrand\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ReorderProductBrandsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'brand_ids' => ['required', 'array', 'min:1'],
            'brand_ids.*' => ['required', 'integer', 'distinct', 'exists:product_brands,id'],
        ];
    }
}
