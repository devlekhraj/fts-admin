<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\EmiRequestModel;

class EmiApplicationListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'application_code' => $this->application_code ?? $this->id,
            'user' => [
                'name' => $this->user?->name ?? $this->name ?? $this->email,
                'avatar' => $this->user?->avatar ?? 'https://placehold.co/32',
            ],
            'product' => [
                'name' => $this->product?->name ?? null,
                'thumb' => 'https://placehold.co/32',
            ],
            'time' => $this->created_at,
            'emi_per_month' => $this->emi_per_month,
            'emi_type' => $this->emi_type ?? null,
            'emi_mode' => $this->emi_mode . " months" ?? 'n/a',
            'status' => $this->status,
            'status_label' => EmiRequestModel::getStatusLabels()[$this->status] ?? 'Unknown',
            'created_at' =>  $this->created_at,
        ];
    }
}
