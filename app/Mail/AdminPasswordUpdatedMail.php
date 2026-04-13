<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminPasswordUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $recipientName,
        public readonly string $recipientUsername,
        public readonly string $changedAt,
        public readonly ?string $timezone = null,
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Your admin password was changed')
            ->view('emails.admin_password_updated')
            ->with([
                'name' => $this->recipientName,
                'username' => $this->recipientUsername,
                'changedAt' => $this->changedAt,
                'timezone' => $this->timezone,
            ]);
    }
}
