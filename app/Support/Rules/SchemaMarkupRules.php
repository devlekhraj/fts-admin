<?php

declare(strict_types=1);

namespace App\Support\Rules;

// Validation for the per-record schema (structured data) set from an admin SEO section:
// { "faqs": [{question, answer}], "custom": "<json-ld string>" }.
final class SchemaMarkupRules
{
    public static function rules(): array
    {
        return [
            'schema_markup' => ['sometimes', 'nullable', 'array'],
            'schema_markup.faqs' => ['nullable', 'array', 'max:30'],
            'schema_markup.faqs.*.question' => ['required', 'string', 'max:300'],
            'schema_markup.faqs.*.answer' => ['required', 'string', 'max:3000'],
            'schema_markup.custom' => ['nullable', 'string', 'max:20000', static function ($attribute, $value, $fail) {
                if ($value === null || trim($value) === '') {
                    return;
                }
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                    $fail('Custom schema must be valid JSON.');
                }
            }],
        ];
    }
}
