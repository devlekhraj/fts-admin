<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Actions;

use App\Domains\EmiRequest\Models\EmiRequest;

final class EmiRequestShowAction
{
    public function execute(string $id): EmiRequest
    {
        return EmiRequest::query()
            ->with(['product.defaultFile', 'user', 'guarantors.files', 'creditCard.cardProvider'])
            ->where(function ($builder) {
                $builder->whereNull('is_deleted')->orWhere('is_deleted', false);
            })
            ->findOrFail($id);
    }
}
