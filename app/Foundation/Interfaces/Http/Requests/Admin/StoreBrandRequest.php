<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:product_brands,name'],
            'slug' => ['required', 'string', 'max:255', 'unique:product_brands,slug'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
