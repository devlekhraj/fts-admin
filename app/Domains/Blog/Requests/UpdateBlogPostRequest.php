<?php

declare(strict_types=1);

namespace App\Domains\Blog\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = (string) $this->route('id');

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('blogs', 'slug')->ignore($id)],
            'author' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'required', 'boolean'],
            'short_desc' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string'],
            'schema_markup' => ['sometimes', 'nullable', 'array'],
            'schema_markup.faqs' => ['nullable', 'array', 'max:30'],
            'schema_markup.faqs.*.question' => ['required', 'string', 'max:300'],
            'schema_markup.faqs.*.answer' => ['required', 'string', 'max:3000'],
            'schema_markup.custom' => ['nullable', 'string', 'max:20000', function ($attribute, $value, $fail) {
                if ($value === null || trim($value) === '') {
                    return;
                }
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                    $fail('Custom schema must be valid JSON.');
                }
            }],
            'publish_date' => ['sometimes', 'nullable', 'date'],
            'category_id' => ['sometimes', 'nullable'],
        ];
    }
}
