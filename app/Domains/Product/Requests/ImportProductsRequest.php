<?php

declare(strict_types=1);

namespace App\Domains\Product\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportProductsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'rows' => ['required', 'array', 'min:1'],
            'rows.*' => ['array'],
        ];
    }
}
