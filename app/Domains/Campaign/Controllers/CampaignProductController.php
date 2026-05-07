<?php

declare(strict_types=1);

namespace App\Domains\Campaign\Controllers;

use App\Domains\Campaign\DTOs\CampaignAssignProductsData;
use App\Domains\Campaign\DTOs\CampaignDiscountUpdateData;
use App\Domains\Campaign\DTOs\CampaignProductUpdateData;
use App\Domains\Campaign\Services\CampaignProductService;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CampaignProductController extends Controller
{
    public function __construct(private readonly CampaignProductService $campaignProductService) {}

    public function index(Request $request, string $id): JsonResponse
    {
        $perPage = is_numeric($request->per_page) && (int) $request->per_page > 0 ? (int) $request->per_page : 30;

        $result = $this->campaignProductService->list(
            campaignId: $id,
            name: $request->filled('name') ? (string) $request->name : null,
            perPage: $perPage,
        );

        return response()->json([
            'success' => true,
            'data' => $result['data'],
            'meta' => $result['meta'],
        ], 200);
    }

    public function store(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'discount_type' => 'nullable|numeric|in:1,2',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        $items = $this->campaignProductService->assign($id, CampaignAssignProductsData::fromArray($validated));

        return response()->json([
            'success' => true,
            'message' => 'Products assigned to campaign successfully.',
            'data' => $items,
        ], 200);
    }

    public function updateDiscount(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'discount_type' => 'nullable|numeric|in:1,2',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        try {
            $this->campaignProductService->updateAllDiscounts($id, CampaignDiscountUpdateData::fromArray($validated));

            return response()->json([
                'message' => 'Discount updated successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'discount_type' => 'nullable|numeric|in:1,2',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        $product = $this->campaignProductService->updateOne($id, CampaignProductUpdateData::fromArray($validated));

        return response()->json([
            'success' => true,
            'message' => 'Campaign product updated successfully.',
            'data' => $product,
        ], 200);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->campaignProductService->removeOne($id);

        return response()->json([
            'success' => true,
            'message' => 'Item is deleted',
        ], 200);
    }
}
