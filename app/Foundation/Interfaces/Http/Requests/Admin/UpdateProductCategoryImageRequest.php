<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Requests\Admin;

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
