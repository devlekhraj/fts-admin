<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlogPostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', 'unique:blogs,title'],
            'slug' => ['required', 'string', 'max:255', 'unique:blogs,slug'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
