<?php

declare(strict_types=1);

namespace App\Domains\Product\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateProductAttributeValuesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'values' => ['sometimes', 'nullable', 'array'],
            'values.*' => ['nullable', 'string', 'max:100'],
        ];
    }
}
