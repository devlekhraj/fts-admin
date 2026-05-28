<?php

declare(strict_types=1);

namespace App\Domains\Order\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class OrderCanceledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $orderNumber,
        public readonly ?string $customerName = null,
        public readonly ?string $reason = null,
        public readonly ?string $canceledAt = null,
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('Your order has been canceled')
            ->view('emails.order_canceled')
            ->with([
                'orderNumber' => $this->orderNumber,
                'customerName' => $this->customerName,
                'reason' => $this->reason,
                'canceledAt' => $this->canceledAt,
            ]);
    }
}

