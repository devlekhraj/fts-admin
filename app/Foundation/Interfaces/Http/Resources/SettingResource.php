<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => str_replace('_', ' ', (string) ($this->module ?? '')),
            'module' => $this->module ?? null,
            'settings' => $this->settings ?? [],
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
