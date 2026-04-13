<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminBasicUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $name,
        public readonly string $username,
        public readonly string $email,
        public readonly ?string $role,
        public readonly string $updatedAt,
        public readonly ?string $timezone = null,
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Your admin profile was updated')
            ->view('emails.admin_basic_updated')
            ->with([
                'name' => $this->name,
                'username' => $this->username,
                'email' => $this->email,
                'role' => $this->role,
                'updatedAt' => $this->updatedAt,
                'timezone' => $this->timezone,
            ]);
    }
}
