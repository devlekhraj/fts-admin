<?php

declare(strict_types=1);

namespace App\Domains\Admin\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class AdminEmailUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $recipientName,
        public readonly string $recipientUsername,
        public readonly string $oldEmail,
        public readonly string $newEmail,
        public readonly string $changedAt,
        public readonly ?string $timezone = null,
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Your admin email was changed')
            ->view('emails.admin_email_updated')
            ->with([
                'name' => $this->recipientName,
                'username' => $this->recipientUsername,
                'oldEmail' => $this->oldEmail,
                'newEmail' => $this->newEmail,
                'changedAt' => $this->changedAt,
                'timezone' => $this->timezone,
            ]);
    }
}
