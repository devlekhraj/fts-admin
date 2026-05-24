<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Mail;

use App\Domains\EmiRequest\Models\EmiRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class EmiRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly EmiRequest $emiRequest,
        public readonly string $reason,
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Your EMI request has been rejected')
            ->view('emails.emi_rejected')
            ->with([
                'emiRequest' => $this->emiRequest,
                'reason' => $this->reason,
            ]);
    }
}

