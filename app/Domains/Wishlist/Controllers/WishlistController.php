<?php

declare(strict_types=1);

namespace App\Domains\Wishlist\Controllers;

use App\Domains\Wishlist\DTOs\WishlistListData;
use App\Domains\Wishlist\Resources\WishlistResource;
use App\Domains\Wishlist\Services\WishlistService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WishlistController extends Controller
{
    public function __construct(private readonly WishlistService $wishlistService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $result = $this->wishlistService->list(WishlistListData::fromArray($request->query()));

        return response()->json([
            'data' => WishlistResource::collection($result['items']),
            'meta' => $result['meta'],
        ]);
    }
}

