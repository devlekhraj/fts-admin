<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ApproveEmiBankRequest extends FormRequest
{
    public function rules(): array
    {
        return [];
    }
}
