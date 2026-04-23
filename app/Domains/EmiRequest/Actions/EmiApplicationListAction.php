<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Actions;

use App\Domains\EmiRequest\Models\EmiApplication;
use Illuminate\Database\Eloquent\Collection;

final class EmiApplicationListAction
{
    /**
     * @return Collection<int, EmiApplication>
     */
    public function execute(string $emiRequestId): Collection
    {
        return EmiApplication::query()
            ->where('emi_request_id', (int) $emiRequestId)
            ->with('emiBank')
            ->latest('id')
            ->get();
    }
}

