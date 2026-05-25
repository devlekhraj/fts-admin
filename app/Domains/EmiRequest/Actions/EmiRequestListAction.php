<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Actions;

use App\Domains\EmiRequest\DTOs\EmiRequestListData;
use App\Domains\EmiRequest\Models\EmiRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
                $builder->where('application_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product', function ($productQuery) use ($search) {
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

        return $query->orderByDesc('created_at')->paginate($data->perPage);
    }
}
