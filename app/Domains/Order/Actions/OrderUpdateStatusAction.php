<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Order\DTOs\OrderStatusUpdateData;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Mail\OrderCanceledMail;
use App\Domains\Order\Mail\OrderCompletedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class OrderUpdateStatusAction
{
    public function execute(string $id, OrderStatusUpdateData $data): Order
    {

        /** @var \App\Models\User */
        $user = \Illuminate\Support\Facades\Auth::user();

        $order = Order::query()->findOrFail($id);
        $order->loadMissing(['items.product']);
        $oldLabel = $order->order_status;
        $order->status = $data->status;
        $order->save();

        $newLabel = $order->order_status;
        $meta = [
            'old_status_label' => $oldLabel,
            'new_status_label' => $newLabel,
            'order_no' => $order->order_no,
        ];
        if ($data->notes !== null && $data->notes !== '') {
            $meta['notes'] = $data->notes;
        }

        $description = "";
        $label = "";
        if($data->status === $order::STATUS_CONFIRMED){
            $label = "Order Confirmed";
            $description = "Order confirmed.";
        }
        if($data->status === $order::STATUS_DISPATCHED){
            $label = "Order Dispatched";
            $description = "Order has been dispatched.";
        }
        if($data->status === $order::STATUS_COMPLETED){
            $label = "Order Completed";
            $description = "Order has been completed.";
        }
        if($data->status === $order::STATUS_CANCELED){
            $label = "Order Canceled";
            $description = "Order canceled.";
        }
        $order->activities()->create([
            'action' => 'order_status_updated',
            'label' => $label,
            'description' => $description,
            'meta' => $meta,
            'actor_type' => $user->getTable(),
            'actor_id' => $user->id,
        ]);

        try {
            $customer = $order->user;
            if ($customer && $customer->email) {
                $orderNumber = $order->order_no ?? $order->invoice_number ?? $order->id;
                $timestamp = now()->timezone(config('app.timezone'))->format('Y-m-d H:i:s');

                if ((int) $data->status === (int) $order::STATUS_CANCELED) {
                    Mail::to($customer->email)->send(new OrderCanceledMail(
                        orderNumber: (string) $orderNumber,
                        customerName: $customer->name ?? null,
                        reason: $data->notes,
                        canceledAt: $timestamp,
                    ));
                } elseif ((int) $data->status === (int) $order::STATUS_COMPLETED) {
                    Mail::to($customer->email)->send(new OrderCompletedMail(
                        orderNumber: (string) $orderNumber,
                        customerName: $customer->name ?? null,
                        completedAt: $timestamp,
                        order: $order,
                    ));
                }
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
