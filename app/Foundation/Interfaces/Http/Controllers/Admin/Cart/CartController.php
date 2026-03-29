<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin\Cart;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\CartModel;
use App\Foundation\Interfaces\Http\Resources\CartResource;
use App\Foundation\Interfaces\Http\Resources\CartDetailResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class CartController extends Controller
{
    public function cartList(Request $request): JsonResponse
    {
        $query = CartModel::query()
            ->with(['user'])
            ->withCount('items')
            ->latest('id');

        if ($search = $request->query('search')) {
            $query->where(function ($builder) use ($search) {
                $builder->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $perPageParam = (int) $request->query('per_page', 12);
        if ($perPageParam === -1) {
            $items = $query->get();

            return response()->json([
                'data' => CartResource::collection($items),
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
            'data' => CartResource::collection($paginator->items()),
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

    public function cartDetail(string $id): JsonResponse
    {
        $cart = CartModel::query()
            ->with(['user', 'items.product'])
            ->withCount('items')
            ->findOrFail($id);

        return response()->json([
            'data' => new CartDetailResource($cart),
        ]);
    }

}
