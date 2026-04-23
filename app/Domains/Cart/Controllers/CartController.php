<?php

declare(strict_types=1);

namespace App\Domains\Cart\Controllers;

use App\Domains\Cart\DTOs\CartListData;
use App\Domains\Cart\Resources\CartDetailResource;
use App\Domains\Cart\Resources\CartResource;
use App\Domains\Cart\Services\CartService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $result = $this->cartService->list(CartListData::fromArray($request->query()));

        return response()->json([
            'data' => CartResource::collection($result['items']),
            'meta' => $result['meta'],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $cart = $this->cartService->detail($id);

        return response()->json([
            'data' => new CartDetailResource($cart),
        ]);
    }
}

