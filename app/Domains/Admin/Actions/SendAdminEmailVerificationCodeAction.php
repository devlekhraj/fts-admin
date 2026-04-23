<?php

declare(strict_types=1);

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Admin;
use App\Domains\Admin\Mail\AdminEmailVerificationCodeMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class SendAdminEmailVerificationCodeAction
{
    private const EMAIL_VERIFICATION_TTL_SECONDS = 600;

    public function execute(Admin $admin, string $newEmail): void
    {
        $code = (string) random_int(100000, 999999);
        $cacheKey = $this->emailVerificationCacheKey((int) $admin->id, $newEmail);

        Cache::put($cacheKey, $code, self::EMAIL_VERIFICATION_TTL_SECONDS);

        try {
            Mail::to($newEmail)->send(new AdminEmailVerificationCodeMail(
                recipientName: (string) ($admin->name ?: ($admin->username ?: 'Admin')),
                recipientUsername: (string) $admin->username,
                code: $code,
                expiresInMinutes: (int) (self::EMAIL_VERIFICATION_TTL_SECONDS / 60),
            ));
        } catch (\Throwable $mailException) {
            Log::error('Failed to send admin email verification code.', [
                'admin_id' => $admin->id,
                'email' => $newEmail,
                'error' => $mailException->getMessage(),
            ]);
        }
    }

    private function emailVerificationCacheKey(int $adminId, string $email): string
    {
        return 'admin_email_verification:' . $adminId . ':' . sha1(strtolower(trim($email)));
    }
}
