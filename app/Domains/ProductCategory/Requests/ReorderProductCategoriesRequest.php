<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ReorderProductCategoriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['required', 'integer', 'distinct', 'exists:product_categories,id'],
        ];
    }
}
