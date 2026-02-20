<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Requests\Admin;



use Illuminate\Foundation\Http\FormRequest;

final class StoreAdminRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255'],
            'username' => ['required','string','max:50'],
            'role_id' => ['required','integer','exists:roles,id'],

            'password' => ['required','string','min:8'],
            'confirm_password' => ['required','same:password'],
        ];
    }
}
