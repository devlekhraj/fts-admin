<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApiKeyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'host' => $this->host,
            'test_public_key' => $this->test_public_key,
            'test_secret_key' => $this->test_secret_key,
            'live_public_key' => $this->live_public_key,
            'live_secret_key' => $this->live_secret_key,
            'mode' => $this->mode,
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
