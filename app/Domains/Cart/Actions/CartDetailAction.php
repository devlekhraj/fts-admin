<?php

declare(strict_types=1);

namespace App\Domains\Cart\Actions;

use App\Domains\Cart\Models\Cart;

final class CartDetailAction
{
    public function execute(string $id): Cart
    {
        return Cart::query()
            ->with(['user', 'items.product'])
            ->withCount('items')
            ->findOrFail($id);
    }
}

