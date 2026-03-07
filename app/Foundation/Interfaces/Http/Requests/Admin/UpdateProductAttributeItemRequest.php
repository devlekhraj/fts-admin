<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateProductAttributeItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:text,option'],
            'use_for_variant' => ['sometimes', 'boolean'],
            'use_in_filter' => ['sometimes', 'boolean'],
        ];
    }
}
