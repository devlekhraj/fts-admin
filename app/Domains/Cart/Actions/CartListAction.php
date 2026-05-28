<?php

declare(strict_types=1);

namespace App\Domains\Cart\Actions;

use App\Domains\Cart\DTOs\CartListData;
use App\Domains\Cart\Models\Cart;

final class CartListAction
{
    public function execute(CartListData $data): array
    {
        $query = Cart::query()
            ->with(['user'])
            ->withCount('items')
            ->has('items')
            ->latest('id');

        if (is_string($data->search) && trim($data->search) !== '') {
            $search = trim($data->search);
            $query->where(function ($builder) use ($search): void {
                $builder->whereHas('user', function ($userQuery) use ($search): void {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $activeTotal = (clone $query)
            ->where('is_processed', false)
            ->count();

        if ($data->perPageParam === -1) {
            $items = $query->get();

            return [
                'items' => $items,
                'meta' => [
                    'current_page' => 1,
                    'per_page' => $items->count(),
                    'total' => $items->count(),
                    'active_total' => $activeTotal,
                    'last_page' => 1,
                    'from' => $items->count() > 0 ? 1 : null,
                    'to' => $items->count() > 0 ? $items->count() : null,
                ],
            ];
        }

        $perPage = max(1, min($data->perPageParam, 100));
        $paginator = $query->paginate($perPage);

        return [
            'items' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'active_total' => $activeTotal,
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }
}
