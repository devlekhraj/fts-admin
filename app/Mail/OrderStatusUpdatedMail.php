<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $orderNumber,
        public readonly string $status,
        public readonly ?string $customerName = null,
        public readonly ?string $updatedAt = null,
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Your order status has been updated')
            ->view('emails.order_status_updated')
            ->with([
                'orderNumber' => $this->orderNumber,
                'status' => $this->status,
                'customerName' => $this->customerName,
                'updatedAt' => $this->updatedAt,
            ]);
    }
}
