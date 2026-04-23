<?php

declare(strict_types=1);

namespace App\Domains\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateAdminPasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:8'],
            'confirm_password' => ['required', 'same:password'],
        ];
    }
}
