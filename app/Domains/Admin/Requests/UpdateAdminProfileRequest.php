<?php

declare(strict_types=1);

namespace App\Domains\Admin\Requests;

use App\Domains\Admin\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateAdminProfileRequest extends FormRequest
{
    public function rules(): array
    {
        $admin = $this->route('admin');
        $adminId = $admin instanceof Admin ? (int) $admin->id : (int) $admin;

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique((new Admin())->getTable(), 'username')->ignore($adminId),
            ],
            'avatar' => ['nullable', 'string', 'max:2048'],
        ];
    }
}
