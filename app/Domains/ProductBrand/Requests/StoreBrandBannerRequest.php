<?php

declare(strict_types=1);

namespace App\Domains\ProductBrand\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreBrandBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'max:10240'],
            'redirect_url' => ['nullable', 'url', 'max:2048'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }
}
