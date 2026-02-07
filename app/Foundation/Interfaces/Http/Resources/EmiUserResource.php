<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmiUserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [];
    }
}
