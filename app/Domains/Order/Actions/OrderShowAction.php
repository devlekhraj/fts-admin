<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Order\Models\Order;

final class OrderShowAction
{
    public function execute(string $id): Order
    {
        return Order::query()
            ->with(['user', 'paymentMethod', 'receipent', 'shippingAddress'])
            ->findOrFail($id);
    }
}

