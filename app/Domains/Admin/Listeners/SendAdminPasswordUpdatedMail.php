<?php

declare(strict_types=1);

namespace App\Domains\Admin\Listeners;

use App\Domains\Admin\Events\AdminPasswordUpdated;
use App\Domains\Admin\Mail\AdminPasswordUpdatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class SendAdminPasswordUpdatedMail implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $backoff = 60;

    public function handle(AdminPasswordUpdated $event): void
    {
        $admin = $event->admin;
        $email = is_string($admin->email) ? trim($admin->email) : '';
        if ($email === '') {
            return;
        }

        $name = (string) ($admin->name ?: ($admin->username ?: 'Admin'));
        $username = (string) ($admin->username ?? '');
        $timestamp = now()->timezone(config('app.timezone'))->format('Y-m-d H:i:s');

        try {
            Mail::to($email)->send(new AdminPasswordUpdatedMail(
                recipientName: $name,
                recipientUsername: $username,
                changedAt: $timestamp,
                timezone: config('app.timezone'),
            ));
        } catch (\Throwable $mailException) {
            Log::error('Failed to send admin password updated email.', [
                'admin_id' => $admin->id,
                'email' => $email,
                'error' => $mailException->getMessage(),
            ]);
        }
    }
}
