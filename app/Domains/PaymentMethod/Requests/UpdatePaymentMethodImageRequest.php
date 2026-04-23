<?php

declare(strict_types=1);

namespace App\Domains\PaymentMethod\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePaymentMethodImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'alt_text' => ['required', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
