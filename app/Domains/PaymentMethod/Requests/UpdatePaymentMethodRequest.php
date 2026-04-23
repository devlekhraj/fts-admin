<?php

declare(strict_types=1);

namespace App\Domains\PaymentMethod\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|boolean',
            'test_mode' => 'sometimes|required|boolean',
            'is_international' => 'sometimes|required|boolean',
            'config' => 'sometimes|nullable|array',
        ];
    }
}
