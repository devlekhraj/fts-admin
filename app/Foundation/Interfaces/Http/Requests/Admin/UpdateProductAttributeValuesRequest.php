<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Requests\Admin;

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
