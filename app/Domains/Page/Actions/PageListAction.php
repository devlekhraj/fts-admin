<?php

declare(strict_types=1);

namespace App\Domains\Page\Actions;

use App\Domains\Page\Models\Page;

final class PageListAction
{
    public function execute(?string $search, int $perPageParam): array
    {
        $query = Page::query()->orderByDesc('updated_at');

        if (is_string($search) && trim($search) !== '') {
            $needle = trim($search);
            $query->where(function ($builder) use ($needle): void {
                $builder->where('title', 'like', "%{$needle}%")
                    ->orWhere('slug', 'like', "%{$needle}%");
            });
        }

        if ($perPageParam === -1) {
            $items = $query->get();

            return [
                'items' => $items,
                'meta' => [
                    'current_page' => 1,
                    'per_page' => $items->count(),
                    'total' => $items->count(),
                    'last_page' => 1,
                    'from' => $items->count() > 0 ? 1 : null,
                    'to' => $items->count() > 0 ? $items->count() : null,
                ],
            ];
        }

        $perPage = max(1, min($perPageParam, 100));
        $paginator = $query->paginate($perPage);

        return [
            'items' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }
}

