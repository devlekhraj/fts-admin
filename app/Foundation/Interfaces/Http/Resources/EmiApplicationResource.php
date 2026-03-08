<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EmiApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'application_id' => $this->application_id,
            'bank_name' => $this->emiBank?->name ?? $this->emiBank?->slug ?? null,
            'file_path' => $this->file_path ?? null,
            'file_url' => $this->file_path ? Storage::disk('fatafat_cdn')->url($this->file_path) : null,
            'status' => $this->status ?? null,
            'created_at' => $this->created_at
        ];
    }
}
