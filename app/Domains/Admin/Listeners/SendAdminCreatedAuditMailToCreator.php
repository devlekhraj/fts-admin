<?php

declare(strict_types=1);

namespace App\Domains\Admin\Listeners;

use App\Domains\Admin\Events\AdminCreated;
use App\Domains\Admin\Mail\AdminCreatedNotificationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class SendAdminCreatedAuditMailToCreator implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $backoff = 60;

    public function handle(AdminCreated $event): void
    {
        $creator = $event->createdBy;
        if (! $creator) {
            return;
        }

        $creatorEmail = is_string($creator->email) ? trim($creator->email) : '';
        if ($creatorEmail === '') {
            return;
        }

        $createdAdminEmail = is_string($event->admin->email) ? trim($event->admin->email) : '';
        if ($createdAdminEmail !== '' && strcasecmp($createdAdminEmail, $creatorEmail) === 0) {
            return;
        }

        $createdAt = now()->timezone(config('app.timezone'))->format('Y-m-d H:i:s');

     
        try {
            Mail::to($creatorEmail)->send(new AdminCreatedNotificationMail(
                creatorName: (string) ($creator->name ?: ($creator->username ?: 'Admin')),
                creatorEmail: (string) ($creator->email ?? ''),
                createdAdminName: (string) ($event->admin->name ?: ($event->admin->username ?: 'Admin')),
                createdAdminEmail: (string) ($event->admin->email ?? ''),
                createdAdminUsername: (string) ($event->admin->username ?? ''),
                createdAdminRole: (string) ($event->admin->role->name ?? ''),
                createdAt: $createdAt,
                timezone: (string) config('app.timezone'),
            ));
        } catch (\Throwable $mailException) {
            Log::error('Failed to send admin created audit email to creator.', [
                'creator_admin_id' => $creator->id,
                'creator_email' => $creatorEmail,
                'created_admin_id' => $event->admin->id,
                'error' => $mailException->getMessage(),
            ]);
        }
    }
}
