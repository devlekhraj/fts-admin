<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class EmiRequestRejectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:500'],
        ];
    }
}

