<?php

declare(strict_types=1);

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\DTOs\UpdateAdminEmailData;
use App\Domains\Admin\Events\AdminEmailUpdated;
use App\Domains\Admin\Models\Admin;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

final class UpdateAdminEmailAction
{
    public function execute(Admin $admin, UpdateAdminEmailData $data): Admin
    {
        $newEmail = trim($data->email);
        $code = trim((string) ($data->verificationCode ?? ''));

        if ($code === '') {
            throw ValidationException::withMessages(['verification_code' => ['Verification code is required.']]);
        }

        $cacheKey = $this->emailVerificationCacheKey((int) $admin->id, $newEmail);
        $cached = Cache::get($cacheKey);
        if (! is_string($cached) || trim($cached) === '' || ! hash_equals(trim($cached), $code)) {
            throw ValidationException::withMessages(['verification_code' => ['Invalid or expired verification code.']]);
        }

        $oldEmail = (string) $admin->email;

        $admin->email = $newEmail;
        $admin->save();
        $admin->loadMissing('role');

        Cache::forget($cacheKey);

        event(new AdminEmailUpdated($admin->refresh(), $oldEmail, $newEmail));

        return $admin->refresh();
    }

    private function emailVerificationCacheKey(int $adminId, string $email): string
    {
        return 'admin_email_verification:' . $adminId . ':' . sha1(strtolower(trim($email)));
    }
}
