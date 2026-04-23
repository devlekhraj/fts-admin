<?php

declare(strict_types=1);

namespace App\Domains\Admin\Requests;

use App\Domains\Admin\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreAdminRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique((new Admin())->getTable(), 'email'),
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
            'username' => ['required','string','max:50', Rule::unique((new Admin())->getTable(), 'username')],
            'role_id' => ['required','integer','exists:roles,id'],
        ];
    }
}
