<?php

declare(strict_types=1);

namespace App\Domains\Banner\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateBannerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:banners,slug,' . $this->route('id')],
            'status' => ['required', 'boolean'],
        ];
    }
}

