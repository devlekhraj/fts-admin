<?php

declare(strict_types=1);

namespace App\Domains\Wishlist\Services;

use App\Domains\Wishlist\Actions\WishlistListAction;
use App\Domains\Wishlist\DTOs\WishlistListData;

final class WishlistService
{
    public function __construct(
        private readonly WishlistListAction $listAction,
    ) {
    }

    public function list(WishlistListData $data): array
    {
        return $this->listAction->execute($data);
    }
}
