<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Requests\Admin;


use App\Foundation\Infrastructure\Persistence\Eloquent\Models\AdminModel;
use Illuminate\Foundation\Http\FormRequest;

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
                static function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || trim($value) === '') {
                        return;
                    }

                    $hasDeletedAccount = AdminModel::withTrashed()
                        ->where('email', trim($value))
                        ->whereNotNull('deleted_at')
                        ->exists();

                    if ($hasDeletedAccount) {
                        $fail('Account associated with this email is deleted.');
                    }
                },
            ],
            'username' => ['required','string','max:50'],
            'role_id' => ['required','integer','exists:roles,id'],
        ];
    }
}
