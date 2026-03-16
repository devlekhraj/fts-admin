<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = (string) $this->route('id');

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('product_categories', 'slug')->ignore($id)],
            'status' => ['sometimes', 'required', 'boolean'],
            'description' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string'],
        ];
    }
}
