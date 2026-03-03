<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Foundation\Interfaces\Http\Resources\CustomerResource;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomerController extends Controller
{
    public function customerList(Request $request): JsonResponse
    {
        $query = $this->withCountColumns(UserModel::query())
            ->orderByDesc('created_at');

        if ($search = $request->query('search')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPageParam = (int) $request->query('per_page', 15);
        if ($perPageParam === -1) {
            $items = $query->get();

            return response()->json([
                'data' => CustomerResource::collection($items),
                'meta' => [
                    'current_page' => 1,
                    'per_page' => $items->count(),
                    'total' => $items->count(),
                    'last_page' => 1,
                    'from' => $items->count() > 0 ? 1 : null,
                    'to' => $items->count() > 0 ? $items->count() : null,
                ],
            ]);
        }

        $perPage = max(1, min($perPageParam, 100));
        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => CustomerResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    public function customerDetail(string $id): JsonResponse
    {
        $customer = $this->withCountColumns(
            UserModel::query()->with('shippingAddress')
        )
            ->findOrFail($id);

        return response()->json([
            'data' => (new CustomerResource($customer)),
            'success' => true,
        ], 200);
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
