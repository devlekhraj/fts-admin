<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Order\DTOs\OrderListData;
use App\Domains\Order\Models\Order;
use Illuminate\Support\Facades\DB;

final class OrderListAction
{
    public function execute(OrderListData $data): array
    {
        $query = Order::query()
            ->with(['user', 'paymentMethod'])
            ->orderByDesc('created_at');

        if ($data->search !== '') {
            $search = $data->search;
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('order_no', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($data->preOrder !== null) {
            $query->where('is_pre_order', $data->preOrder);
        }

        if ($data->paymentStatus !== '') {
            $query->where('payment_status', $data->paymentStatus);
        }

        if ($data->statuses !== null) {
            $data->statuses === [] ? $query->whereRaw('1 = 0') : $query->whereIn('status', $data->statuses);
        }

        if ($data->dateFrom !== null) {
            $query->where('created_at', '>=', $data->dateFrom . ' 00:00:00');
        }
        if ($data->dateTo !== null) {
            $query->where('created_at', '<=', $data->dateTo . ' 23:59:59');
        }

        // How many orders are in each status, for the tabs (not narrowed by the filters above).
        $counts = Order::query()->select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c', 'status')->all();

        if ($data->perPage === -1) {
            $items = $query->get();

            return [
                'counts' => $counts,
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

        $perPage = max(1, min($data->perPage, 100));
        $paginator = $query->paginate($perPage);

        return [
            'counts' => $counts,
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
