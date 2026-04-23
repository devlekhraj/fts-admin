<?php

declare(strict_types=1);

namespace App\Domains\PaymentMethod\Actions;

use App\Domains\PaymentMethod\Models\PaymentMethod;

final class PaymentMethodDetailAction
{
    public function execute(string $id): PaymentMethod
    {
        return PaymentMethod::query()
            ->with(['files', 'defaultFile'])
            ->findOrFail($id);
    }
}

