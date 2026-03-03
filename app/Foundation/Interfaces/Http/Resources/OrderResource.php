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
        $data = $this->resource->toArray();

        $data['total'] = is_numeric($data['total'] ?? null) ? (float) $data['total'] : null;
        $data['subtotal'] = is_numeric($data['subtotal'] ?? null) ? (float) $data['subtotal'] : null;
        $data['discount_total'] = is_numeric($data['discount_total'] ?? null) ? (float) $data['discount_total'] : null;
        $data['tax_total'] = is_numeric($data['tax_total'] ?? null) ? (float) $data['tax_total'] : null;
        $data['shipping_total'] = is_numeric($data['shipping_total'] ?? null) ? (float) $data['shipping_total'] : null;

        $data['customer'] = [
            'id' => $this->user?->id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'mobile' => $this->user?->contact_number,
            'avatar_url' => $this->user?->avatar,
        ];

        $data['payment_method'] = [
            'id' => $this->paymentMethod?->id,
            'name' => $this->paymentMethod?->name,
            'slug' => $this->paymentMethod?->slug,
        ];

        return $data;
    }
}
