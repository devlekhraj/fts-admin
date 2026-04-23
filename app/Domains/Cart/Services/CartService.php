<?php

declare(strict_types=1);

namespace App\Domains\Cart\Services;

use App\Domains\Cart\Actions\CartDetailAction;
use App\Domains\Cart\Actions\CartListAction;
use App\Domains\Cart\DTOs\CartListData;
use App\Domains\Cart\Models\Cart;

final class CartService
{
    public function __construct(
        private readonly CartListAction $listAction,
        private readonly CartDetailAction $detailAction,
    ) {
    }

    public function list(CartListData $data): array
    {
        return $this->listAction->execute($data);
    }

    public function detail(string $id): Cart
    {
        return $this->detailAction->execute($id);
    }
}

