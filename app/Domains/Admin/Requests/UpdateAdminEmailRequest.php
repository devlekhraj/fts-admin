<?php

declare(strict_types=1);

namespace App\Domains\Admin\Requests;

use App\Domains\Admin\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateAdminEmailRequest extends FormRequest
{
    public function rules(): array
    {
        $admin = $this->route('admin');
        $adminId = $admin instanceof Admin ? (int) $admin->id : (int) $admin;

        return [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique((new Admin())->getTable(), 'email')->ignore($adminId),
                static function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || trim($value) === '') {
                        return;
                    }

                    $hasDeletedAccount = Admin::withTrashed()
                        ->where('email', trim($value))
                        ->whereNotNull('deleted_at')
                        ->exists();

                    if ($hasDeletedAccount) {
                        $fail('Account associated with this email is deleted.');
                    }
                },
            ],
            'verification_code' => ['required', 'string', 'size:6'],
        ];
    }
}
