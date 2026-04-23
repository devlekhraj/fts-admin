<?php

declare(strict_types=1);

namespace App\Domains\Order\Services;

use App\Domains\Order\Actions\OrderListAction;
use App\Domains\Order\Actions\OrderShowAction;
use App\Domains\Order\Actions\OrderUpdateStatusAction;
use App\Domains\Order\Actions\OrderWarrantyGenerateAction;
use App\Domains\Order\DTOs\OrderListData;
use App\Domains\Order\DTOs\OrderStatusUpdateData;
use App\Domains\Order\Models\Order;

final class OrderService
{
    public function __construct(
        private readonly OrderListAction $orderListAction,
        private readonly OrderShowAction $orderShowAction,
        private readonly OrderUpdateStatusAction $orderUpdateStatusAction,
        private readonly OrderWarrantyGenerateAction $orderWarrantyGenerateAction,
    ) {}

    public function list(OrderListData $data): array
    {
        return $this->orderListAction->execute($data);
    }

    public function show(string $id): Order
    {
        return $this->orderShowAction->execute($id);
    }

    public function updateStatus(string $id, OrderStatusUpdateData $data): Order
    {
        return $this->orderUpdateStatusAction->execute($id, $data);
    }

    public function warranty(string $id): array
    {
        return $this->orderWarrantyGenerateAction->execute($id);
    }
}

