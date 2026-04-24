<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Order\DTOs\OrderStatusUpdateData;
use App\Domains\Order\Models\Order;
use App\Mail\OrderStatusUpdatedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class OrderUpdateStatusAction
{
    public function execute(string $id, OrderStatusUpdateData $data): Order
    {
        $order = Order::query()->findOrFail($id);
        $oldLabel = $order->order_status;
        $order->status = $data->status;
        $order->save();

        $newLabel = $order->order_status;
        $order->logActivity(
            action: 'order_status_updated',
            label: "Order moved to {$newLabel}",
            description: "Order has been {$newLabel}",
            oldStatus: (string) $oldLabel,
            newStatus: (string) $oldLabel,
            meta: [
                'old_status_label' => $oldLabel,
                'new_status_label' => $newLabel,
                'order_no' => $order->order_no,
            ],
            actor: auth()->user()
        );

        try {
            $customer = $order->user;
            if ($customer && $customer->email) {
                $orderNumber = $order->order_no ?? $order->invoice_number ?? $order->id;
                $statusLabel = $order->order_status;
                $timestamp = now()->timezone(config('app.timezone'))->format('Y-m-d H:i:s');
                Mail::to($customer->email)->send(new OrderStatusUpdatedMail(orderNumber: (string) $orderNumber, status: $statusLabel, customerName: $customer->name ?? null, updatedAt: $timestamp));
            }
        } catch (\Throwable $mailException) {
            Log::error('Failed to send order status update email.', [
                'order_id' => $order->id,
                'email' => $order->user->email ?? null,
                'error' => $mailException->getMessage(),
            ]);
        }

        return $order;
    }
}
