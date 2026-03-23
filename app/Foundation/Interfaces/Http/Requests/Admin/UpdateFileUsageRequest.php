<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateFileUsageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'alt_text' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'link' => ['nullable', 'string', 'max:2000'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'],
            'seq_no' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
