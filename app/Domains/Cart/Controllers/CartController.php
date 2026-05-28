<?php

declare(strict_types=1);

namespace App\Domains\Cart\Controllers;

use App\Domains\Cart\Actions\CartDetailAction;
use App\Domains\Cart\Actions\CartListAction;
use App\Domains\Cart\DTOs\CartListData;
use App\Domains\Cart\Resources\CartDetailResource;
use App\Domains\Cart\Resources\CartResource;
use Illuminate\Routing\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CartController extends Controller
{
    public function __construct(
        private readonly CartListAction $listAction,
        private readonly CartDetailAction $detailAction,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $result = $this->listAction->execute(CartListData::fromArray($request->query()));

        return response()->json([
            'data' => CartResource::collection($result['items']),
            'meta' => $result['meta'],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $cart = $this->detailAction->execute($id);

        return response()->json([
            'data' => new CartDetailResource($cart),
        ]);
    }
}
