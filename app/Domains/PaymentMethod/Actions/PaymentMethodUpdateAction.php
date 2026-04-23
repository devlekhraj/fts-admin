<?php

declare(strict_types=1);

namespace App\Domains\PaymentMethod\Actions;

use App\Domains\PaymentMethod\DTOs\PaymentMethodUpdateData;
use App\Domains\PaymentMethod\Models\PaymentMethod;

final class PaymentMethodUpdateAction
{
    public function execute(PaymentMethod $paymentMethod, PaymentMethodUpdateData $data): PaymentMethod
    {
        $paymentMethod->update($data->attributes);

        return $paymentMethod;
    }
}

