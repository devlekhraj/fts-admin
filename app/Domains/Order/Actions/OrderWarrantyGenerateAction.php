<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Order\Models\Order;
use Illuminate\Support\Str;

final class OrderWarrantyGenerateAction
{
    public function execute(string $id): array
    {
        $order = Order::query()->with('user')->findOrFail($id);

        if (is_string($order->warranty_token) && $order->warranty_token !== '') {
            return [
                'warranty_token' => $order->warranty_token,
                'success' => true,
                'message' => 'Warranty token already exists.',
            ];
        }

        $order->warranty_token = 'WS-' . Str::upper(Str::random(8));
        $order->save();

        return [
            'warranty_token' => $order->warranty_token,
            'success' => true,
        ];
    }
}

