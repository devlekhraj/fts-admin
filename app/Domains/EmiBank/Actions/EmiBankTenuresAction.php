<?php

declare(strict_types=1);

namespace App\Domains\EmiBank\Actions;

use App\Domains\EmiBank\Models\EmiBank;

final class EmiBankTenuresAction
{
    public function execute(string $id): array
    {
        $record = EmiBank::query()->findOrFail($id);

        return [
            'tenures' => $record->tenures ?? [],
        ];
    }
}

