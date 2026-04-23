<?php

declare(strict_types=1);

namespace App\Domains\Faq\Actions;

use App\Domains\Faq\DTOs\FaqListData;
use App\Domains\Faq\Models\Faq;
use App\Domains\Product\Models\Product;

final class FaqListAction
{
    public function execute(FaqListData $data): array
    {
        $query = Faq::query()->orderByDesc('updated_at');

        if ($data->type === 'brand') {
            $query->with('brand');
        } elseif ($data->type === 'category') {
            $query->with('category');
        } else {
            $query->with(['brand', 'category']);
        }

        if ($data->type !== '') {
            if ($data->type === 'general') {
                $query->where(function ($builder): void {
                    $builder->whereNull('type')
                        ->orWhere('type', '');
                });
            } else {
                $query->where('type', $data->type);
            }
        }

        if ($data->typeId !== null) {
            $query->where('type_id', $data->typeId);
        }

        if (is_string($data->search) && trim($data->search) !== '') {
            $search = trim($data->search);
            $query->where(function ($builder) use ($search): void {
                $builder->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        if ($data->perPageParam === -1) {
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

        $perPage = max(1, min($data->perPageParam, 100));
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

