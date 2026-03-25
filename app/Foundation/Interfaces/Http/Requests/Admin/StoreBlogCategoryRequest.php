<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlogCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', 'unique:blog_categories,title'],
            'slug' => ['required', 'string', 'max:255', 'unique:blog_categories,slug'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
