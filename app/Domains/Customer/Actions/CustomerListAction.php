<?php

declare(strict_types=1);

namespace App\Domains\Customer\Actions;

use App\Domains\Customer\DTOs\CustomerListData;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class CustomerListAction
{
    public function execute(CustomerListData $data): array
    {
        $query = $this->withCountColumns(User::query())
            ->orderByDesc('created_at');

        if (is_string($data->search) && trim($data->search) !== '') {
            $search = trim($data->search);
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
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

    private function withCountColumns(Builder $query): Builder
    {
        $query->select('users.*');

        if (Schema::hasTable('orders')) {
            $query->selectSub(
                DB::table('orders')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('orders.user_id', 'users.id'),
                'total_order'
            );
        } else {
            $query->selectRaw('0 as total_order');
        }

        if (Schema::hasTable('emi_requests')) {
            $query->selectSub(
                DB::table('emi_requests')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('emi_requests.user_id', 'users.id'),
                'total_emi'
            );
        } else {
            $query->selectRaw('0 as total_emi');
        }

        return $query;
    }
}
