<?php

declare(strict_types=1);

namespace App\Domains\Product\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductVariantRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'attributes' => ['nullable', 'array'],
        ];
    }
}
