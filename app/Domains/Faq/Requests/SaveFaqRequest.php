<?php

declare(strict_types=1);

namespace App\Domains\Faq\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'exists:faqs,id'],
            'type' => ['required', 'string', Rule::in(['brand', 'category', 'product', 'general'])],
            'type_id' => [
                'nullable',
                'integer',
                Rule::requiredIf(fn (): bool => $this->input('type') !== 'general'),
            ],
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['nullable', 'string'],
        ];
    }
}

