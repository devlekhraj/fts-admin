<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        if ($request->route()?->getName() === 'admin.orders.api.details') {
            return $this->detailResponse();
        }

        return $this->listResponse();
    }

    private function listResponse(): array
    {
        $itemsCount = $this->relationLoaded('items')
            ? $this->items->count()
            : $this->items()->count();

        return [
            'id' => $this->id,
            'order_number' => $this->order_number ? $this->order_number: $this->invoice_number,
            'status' => $this->order_status,
            'total' => is_numeric($this->total) ? (float) $this->total : null,
            'customer' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'avatar' => $this->user?->avatar,
            ],
            'items_count' => $itemsCount,
            'created_at' => $this->created_at,
        ];
    }

    private function detailResponse(): array
    {

        $order['summary'] = [
            'id' => $this->id,
            'order_no' => $this->order_number ?? $this->invoice_number,
            'invoice_no' => $this->invoice_number,
            'order_date' => $this->created_at,
            'status' => $this->order_status,
            'warranty_token' => $this->warranty_token,
        ];
      
        $order['customer'] = [
            'id' => $this->user?->id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'mobile' => $this->user?->contact_number,
            'avatar_url' => $this->user?->avatar,
        ];

        $order['receipent'] = [
            'id' => $this->receipent?->id,
            'name' => $this->receipent?->name,
            'phone' => $this->receipent?->phone,
            'sender_photo' => $this->receipent?->sender_photo,
            'receiver_photo' => $this->receipent?->receiver_photo,
        ];
        $order['shipping_address'] = [
            'id' => $this->shippingAddress?->add,
            'label' => $this->shippingAddress?->label,
            'district' => $this->shippingAddress?->district,
            'city' => $this->shippingAddress?->city,
            'landmark' => $this->shippingAddress?->landmark,
            'province' => $this->shippingAddress?->province,
            'geo' =>[
                'lat' => $this->shippingAddress?->lat,
                'lng' => $this->shippingAddress?->lng,
            ]
        ];
        $order['order_items'] = $this->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_name' => $item->product_name ?? $item->product?->name ?? null,
                'price' => is_numeric($item->product_price ?? $item->price) ? (float) ($item->product_price ?? $item->price) : ($item->product->price ?? null),
                'quantity' => $item->quantity ?? null,
                'sku' => $item->product_sku ?? $item->product?->sku ?? null,
                'product_thumb' => $item->product_thumb ?? $item->product?->thumbnail ?? $item->product?->thumb ?? null,
                'product_attributes' => $item->product_attributes,
            ];
        });

        $order['total_summary'] = [
            'payment_type' => $this->payment_type,
            'shipping_cost' => $this->shipping_cost,
            'discount_total' => $this->discounts_total,
            'sub_total' => $this->order_total,
            'total' => $this->paymentMethod?->total,
        ];

        return $order;
    }
}
