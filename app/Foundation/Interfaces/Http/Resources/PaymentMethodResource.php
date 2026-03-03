<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public function toArray($request): array
    {
        if ($request->route()?->getName() === 'admin.payment-methods.show') {
            return $this->showResponse();
        }

        return $this->listResponse();
    }

    private function listResponse(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => (bool) $this->status,
            'test_mode' => (bool) $this->test_mode,
            'is_international' => (bool) $this->is_international,
            'logo_url' => $this->logo_url,
            'created_at' => $this->created_at,
        ];
    }

    private function showResponse(): array
    {
        $data = $this->resource->toArray();

        $data['status'] = (bool) ($data['status'] ?? $this->status);
        $data['test_mode'] = (bool) ($data['test_mode'] ?? $this->test_mode);
        $data['is_international'] = (bool) ($data['is_international'] ?? $this->is_international);
        $data['config'] = $this->decodeConfig($data['config'] ?? null);

        return $data;
    }

    private function decodeConfig($config): array
    {
        if (is_array($config)) {
            return $config;
        }

        if (is_string($config)) {
            $decoded = json_decode($config, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }
}
