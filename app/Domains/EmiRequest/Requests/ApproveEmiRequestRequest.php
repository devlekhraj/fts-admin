<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveEmiRequestRequest extends FormRequest
{
    public function rules(): array
    {
        return [];
    }
}
