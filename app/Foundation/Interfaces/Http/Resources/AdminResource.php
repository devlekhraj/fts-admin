<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    public function toArray($request): array
    {
        $avatar = is_string($this->avatar) ? trim($this->avatar) : null;

        return [
            'id' => $this->id,
            'avatar_url' => $avatar !== '' && $avatar !== null ? $avatar : $this->avatar_url,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username ?? 'username',
            'role' => $this->role->name ?? null,
            'role_id' => $this->role_id ?? null,
            'created_at' => $this->created_at ?? null,
        ];
    }
}
