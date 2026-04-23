<?php

declare(strict_types=1);

namespace App\Domains\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateAdminRoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ];
    }
}

