<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class GenerateEmiApplicationPdfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
