<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminAccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $name,
        public readonly string $username,
        public readonly string $password,
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Your admin account is ready')
            ->view('emails.admin_account_created')
            ->with([
                'name' => $this->name,
                'username' => $this->username,
                'password' => $this->password,
            ]);
    }
}
