<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Query;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\EmiRequestModel;

final class EmiRequestReadModel
{
    public function getForApplication(string $id): EmiRequestModel
    {
        return EmiRequestModel::query()
            ->with(['product.brand', 'bank'])
            ->findOrFail($id);
    }
}

