<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray($request): array
    {
        if ($request->route()?->getName() === 'admin.customers.detail') {
            return $this->showResponse();
        }

        return $this->listResponse();
    }

    private function listResponse(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->contact_number ?? 'n/a',
            'avatar_url' => $this->avatar,
            'total_order' => (int) ($this->total_order ?? 0),
            'total_emi' => (int) ($this->total_emi ?? 0),
            'created_at' => $this->created_at,
        ];
    }

    private function showResponse(): array
    {
        $data = $this->resource->toArray();

        $data['mobile'] = $this->contact_number ?? 'n/a';
        $data['avatar_url'] = $this->avatar;
        $data['total_order'] = (int) ($data['total_order'] ?? $this->total_order ?? 0);
        $data['total_emi'] = (int) ($data['total_emi'] ?? $this->total_emi ?? 0);
        $data['shipping_address'] = collect($data['shipping_address'] ?? [])
            ->map(function (array $address): array {
                unset($address['created_at'], $address['updated_at']);
                return $address;
            })
            ->values()
            ->all();

        return $data;
    }
}
