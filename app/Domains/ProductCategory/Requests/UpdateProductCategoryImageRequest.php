<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateProductCategoryImageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'alt_text' => ['required', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
