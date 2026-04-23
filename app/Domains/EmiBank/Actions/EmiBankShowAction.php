<?php

declare(strict_types=1);

namespace App\Domains\EmiBank\Actions;

use App\Domains\EmiBank\Models\EmiBank;

final class EmiBankShowAction
{
    public function execute(string $id): EmiBank
    {
        return EmiBank::query()->findOrFail($id);
    }
}

