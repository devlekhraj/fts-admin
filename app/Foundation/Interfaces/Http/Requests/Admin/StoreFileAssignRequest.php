<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class StoreFileAssignRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'usage_type' => ['required', 'string', 'max:120'],
            'usage_id' => ['required', 'integer', 'min:1'],
            'source' => ['required', 'string', 'in:existing,upload'],
            'image_id' => ['nullable', 'integer', 'exists:files,id', 'required_if:source,existing'],
            'file' => ['nullable', 'image', 'max:10240', 'required_if:source,upload'],
            'file_name' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'directory' => ['nullable', 'string', 'max:120'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
