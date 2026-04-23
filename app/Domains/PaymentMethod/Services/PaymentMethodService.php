<?php

declare(strict_types=1);

namespace App\Domains\PaymentMethod\Services;

use App\Domains\PaymentMethod\Actions\PaymentMethodDetailAction;
use App\Domains\PaymentMethod\Actions\PaymentMethodListAction;
use App\Domains\PaymentMethod\Actions\PaymentMethodUpdateAction;
use App\Domains\PaymentMethod\DTOs\PaymentMethodListData;
use App\Domains\PaymentMethod\DTOs\PaymentMethodUpdateData;
use App\Domains\PaymentMethod\Models\PaymentMethod;

final class PaymentMethodService
{
    public function __construct(
        private readonly PaymentMethodListAction $listAction,
        private readonly PaymentMethodDetailAction $detailAction,
        private readonly PaymentMethodUpdateAction $updateAction,
    ) {
    }

    public function list(PaymentMethodListData $data): array
    {
        return $this->listAction->execute($data);
    }

    public function detail(string $id): PaymentMethod
    {
        return $this->detailAction->execute($id);
    }

    public function update(string $id, PaymentMethodUpdateData $data): PaymentMethod
    {
        $paymentMethod = PaymentMethod::query()->findOrFail($id);

        return $this->updateAction->execute($paymentMethod, $data);
    }
}

