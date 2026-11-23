<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Actions;

use App\Domains\EmiRequest\DTOs\EmiRequestListData;
use App\Domains\EmiRequest\Models\EmiRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class EmiRequestListAction
{
    public function execute(EmiRequestListData $data): LengthAwarePaginator
    {
        $query = EmiRequest::query()
            ->with(['product.defaultFile', 'user']);

        // Treat NULL as "not deleted" since the field is nullable.
        $query->where(function ($builder) {
            $builder->whereNull('is_deleted')->orWhere('is_deleted', false);
        });

        if ($data->search !== '') {
            $search = $data->search;
            $query->where(function ($builder) use ($search) {
                $builder->whereHas('user', function ($userQuery) use ($search): void {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                    ->orWhereHas('product', function ($productQuery) use ($search): void {
                        $productQuery->where('name', 'like', "%{$search}%");
                    });

                if (is_numeric($search)) {
                    $builder->orWhere('id', (int) $search);
                }
            });
        }

        if ($data->emiType !== null && $data->emiType !== '') {
            $query->where('emi_type', $data->emiType);
        }

        if ($data->status !== null && $data->status !== '') {
            $query->where('status', (int) $data->status);
        }

        if ($data->dateFrom !== null) {
            $query->where('created_at', '>=', $data->dateFrom . ' 00:00:00');
        }
        if ($data->dateTo !== null) {
            $query->where('created_at', '<=', $data->dateTo . ' 23:59:59');
        }

        return $query->orderByDesc('created_at')->paginate($data->perPage);
    }

    /** How many requests are in each status, for the tabs (deleted requests are not counted). */
    public function counts(): array
    {
        return EmiRequest::query()
            ->where(function ($builder) {
                $builder->whereNull('is_deleted')->orWhere('is_deleted', false);
            })
            ->select('status', DB::raw('count(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status')
            ->all();
    }
}
