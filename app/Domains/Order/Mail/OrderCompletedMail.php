<?php

declare(strict_types=1);

namespace App\Domains\Order\Mail;

use App\Domains\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class OrderCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $orderNumber,
        public readonly ?string $customerName = null,
        public readonly ?string $completedAt = null,
        public readonly ?Order $order = null,
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('Your order has been completed')
            ->view('emails.order_completed')
            ->with([
                'orderNumber' => $this->orderNumber,
                'customerName' => $this->customerName,
                'completedAt' => $this->completedAt,
                'order' => $this->order,
            ]);
    }
}
