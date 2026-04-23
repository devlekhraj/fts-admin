<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\ReadModels;

use App\Domains\EmiRequest\Models\EmiRequest;

final class EmiRequestReadModel
{
    public function getForApplication(string $id): EmiRequest
    {
        return EmiRequest::query()
            ->with(['product.brand', 'bank'])
            ->findOrFail($id);
    }
}

