<?php

declare(strict_types=1);

namespace App\Domains\Product\Controllers;

use App\Domains\Product\Models\Product;
use App\Domains\Product\Resources\ProductGiftResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class ProductGiftController extends Controller
{
    public function index(string $id): JsonResponse
    {
        $product = Product::query()
            ->select(['id'])
            ->with(['giftItems.defaultFile'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => ProductGiftResource::collection($product->giftItems),
        ], 200);
    }

    public function sync(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'gift_ids' => 'required|array',
            'gift_ids.*' => 'exists:products,id',
        ]);

        $giftIds = array_values(array_unique(array_map('strval', $validated['gift_ids'] ?? [])));
        $giftIds = array_values(array_filter($giftIds, static fn ($giftId) => $giftId !== '' && $giftId !== null));

        if (in_array((string) $id, $giftIds, true)) {
            return response()->json([
                'success' => false,
                'message' => 'A product cannot be a gift item of itself.',
                'errors' => [
                    'gift_ids' => ['A product cannot be a gift item of itself.'],
                ],
            ], 422);
        }

        $product = Product::query()->select(['id'])->findOrFail($id);
        $syncPayload = [];
        foreach ($giftIds as $giftId) {
            $syncPayload[$giftId] = ['is_active' => true];
        }
        $product->giftItems()->sync($syncPayload);
        $product->load('giftItems.defaultFile');

        return response()->json([
            'success' => true,
            'message' => 'Gift items updated successfully.',
            'data' => ProductGiftResource::collection($product->giftItems),
        ], 200);
    }

    public function destroy(string $id, string $giftId): JsonResponse
    {
        if ((string) $id === (string) $giftId) {
            return response()->json([
                'success' => false,
                'message' => 'A product cannot remove itself as a gift item.',
            ], 422);
        }

        $product = Product::query()->select(['id'])->findOrFail($id);
        $product->giftItems()->detach([(string) $giftId]);

        return response()->json([
            'success' => true,
            'message' => 'Gift item removed successfully.',
        ], 200);
    }
}
