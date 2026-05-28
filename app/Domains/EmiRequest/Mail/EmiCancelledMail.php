<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Mail;

use App\Domains\EmiRequest\Models\EmiRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class EmiCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly EmiRequest $emiRequest,
        public readonly ?string $reason = null,
        public readonly ?string $cancelledAt = null,
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Your EMI request has been cancelled')
            ->view('emails.emi_cancelled')
            ->with([
                'emiRequest' => $this->emiRequest,
                'reason' => $this->reason,
                'cancelledAt' => $this->cancelledAt,
            ]);
    }
}

