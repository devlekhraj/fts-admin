<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->fname."".($this->lname ? " ".$this->lname : ""),
            'email' => $this->email,
            'username' => $this->username ?? null,
            'role_id' => $this->role_id ?? null,
            'created_at' => $this->created_at?->format('M d, Y'),
            'role' => $this->role->name ?? null,
        ];
    }
}
