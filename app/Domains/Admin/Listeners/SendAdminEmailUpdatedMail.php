<?php

declare(strict_types=1);

namespace App\Domains\Admin\Listeners;

use App\Domains\Admin\Events\AdminEmailUpdated;
use App\Domains\Admin\Mail\AdminEmailUpdatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class SendAdminEmailUpdatedMail implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $backoff = 60;

    public function handle(AdminEmailUpdated $event): void
    {
        $admin = $event->admin;

        $name = (string) ($admin->name ?: ($admin->username ?: 'Admin'));
        $username = (string) ($admin->username ?? '');
        $timestamp = now()->timezone(config('app.timezone'))->format('Y-m-d H:i:s');

        $recipients = array_values(array_unique(array_filter([
            trim($event->oldEmail),
            trim($event->newEmail),
        ], static fn ($email) => is_string($email) && $email !== '')));

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient)->send(new AdminEmailUpdatedMail(
                    recipientName: $name,
                    recipientUsername: $username,
                    oldEmail: $event->oldEmail,
                    newEmail: $event->newEmail,
                    changedAt: $timestamp,
                    timezone: config('app.timezone'),
                ));
            } catch (\Throwable $mailException) {
                Log::error('Failed to send admin email updated email.', [
                    'admin_id' => $admin->id,
                    'email' => $recipient,
                    'error' => $mailException->getMessage(),
                ]);
            }
        }
    }
}
