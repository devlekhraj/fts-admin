<?php

declare(strict_types=1);

namespace App\Domains\Admin\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class AdminCreatedNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $creatorName,
        public readonly string $creatorEmail,
        public readonly string $createdAdminName,
        public readonly string $createdAdminEmail,
        public readonly string $createdAdminUsername,
        public readonly string $createdAdminRole,
        public readonly string $createdAt,
        public readonly ?string $timezone = null,
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Admin account created')
            ->view('emails.admin_created_notification')
            ->with([
                'creatorName' => $this->creatorName,
                'creatorEmail' => $this->creatorEmail,
                'createdAdminName' => $this->createdAdminName,
                'createdAdminEmail' => $this->createdAdminEmail,
                'createdAdminUsername' => $this->createdAdminUsername,
                'createdAdminRole' => $this->createdAdminRole,
                'createdAt' => $this->createdAt,
                'timezone' => $this->timezone,
            ]);
    }
}
