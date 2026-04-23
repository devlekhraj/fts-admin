<?php

declare(strict_types=1);

namespace App\Domains\PaymentMethod\Services;

use App\Domains\File\Models\FileUsage;
use App\Domains\PaymentMethod\Actions\PaymentMethodImageDeleteAction;
use App\Domains\PaymentMethod\Actions\PaymentMethodImageUpdateAction;
use App\Domains\PaymentMethod\DTOs\PaymentMethodImageUpdateData;

final class PaymentMethodImageService
{
    public function __construct(
        private readonly PaymentMethodImageUpdateAction $updateAction,
        private readonly PaymentMethodImageDeleteAction $deleteAction,
    ) {
    }

    public function update(string $paymentMethodId, string $fileUsageId, PaymentMethodImageUpdateData $data): ?FileUsage
    {
        return $this->updateAction->execute($paymentMethodId, $fileUsageId, $data);
    }

    public function delete(string $paymentMethodId, string $fileUsageId): bool
    {
        return $this->deleteAction->execute($paymentMethodId, $fileUsageId);
    }
}

