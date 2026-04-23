<?php

declare(strict_types=1);

namespace App\Domains\PaymentMethod\Actions;

use App\Domains\File\Models\FileUsage;

final class PaymentMethodImageDeleteAction
{
    public function execute(string $paymentMethodId, string $fileUsageId): bool
    {
        $fileUsage = FileUsage::query()
            ->where('id', $fileUsageId)
            ->where('usage_type', 'payment_methods')
            ->where('usage_id', $paymentMethodId)
            ->first();

        if (! $fileUsage) {
            return false;
        }

        $fileUsage->delete();

        return true;
    }
}

