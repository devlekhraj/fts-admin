<?php

declare(strict_types=1);

namespace App\Domains\Admin\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class AdminEmailVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $recipientName,
        public readonly string $recipientUsername,
        public readonly string $code,
        public readonly int $expiresInMinutes,
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Verify your new admin email')
            ->view('emails.admin_email_verification_code')
            ->with([
                'name' => $this->recipientName,
                'username' => $this->recipientUsername,
                'code' => $this->code,
                'expiresInMinutes' => $this->expiresInMinutes,
            ]);
    }
}
