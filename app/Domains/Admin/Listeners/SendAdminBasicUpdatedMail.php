<?php

declare(strict_types=1);

namespace App\Domains\Admin\Listeners;

use App\Domains\Admin\Events\AdminBasicUpdated;
use App\Domains\Admin\Mail\AdminBasicUpdatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class SendAdminBasicUpdatedMail implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $backoff = 60;

    public function handle(AdminBasicUpdated $event): void
    {
        $admin = $event->admin->refresh()->loadMissing('role');
        $email = is_string($admin->email) ? trim($admin->email) : '';
        if ($email === '') {
            return;
        }

        $name = (string) ($admin->name ?: ($admin->username ?: 'Admin'));
        $username = (string) ($admin->username ?? '');
        $roleName = $admin->role->name ?? null;
        $timestamp = now()->timezone(config('app.timezone'))->format('Y-m-d H:i:s');

        try {
            Mail::to($email)->send(new AdminBasicUpdatedMail(
                name: $name,
                username: $username,
                email: $email,
                role: $roleName,
                updatedAt: $timestamp,
                timezone: config('app.timezone'),
            ));
        } catch (\Throwable $mailException) {
            Log::error('Failed to send admin basic updated email.', [
                'admin_id' => $admin->id,
                'email' => $email,
                'error' => $mailException->getMessage(),
            ]);
        }
    }
}

