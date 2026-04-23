<?php

declare(strict_types=1);

namespace App\Domains\Admin\Listeners;

use App\Domains\Admin\Events\AdminCreated;
use App\Domains\Admin\Mail\AdminAccountCreatedMail;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class SendAdminCreatedMail implements ShouldQueue, ShouldBeEncrypted
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $backoff = 60;

    public function handle(AdminCreated $event): void
    {
        $admin = $event->admin;
        $email = is_string($admin->email) ? trim($admin->email) : '';
        if ($email === '') {
            return;
        }

        try {
            Mail::to($email)->send(new AdminAccountCreatedMail(
                name: (string) ($admin->name ?? ''),
                username: (string) ($admin->username ?? ''),
                password: $event->plaintextPassword,
            ));
        } catch (\Throwable $mailException) {
            Log::error('Failed to send admin account created email.', [
                'admin_id' => $admin->id,
                'email' => $email,
                'error' => $mailException->getMessage(),
            ]);
        }
    }
}
