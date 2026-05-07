<?php

declare(strict_types=1);

namespace App\Domains\ProductBrand\Controllers;

use App\Domains\ProductBrand\Requests\UpdateProductBrandImageRequest;
use App\Domains\ProductBrand\DTOs\BrandImageUpdateData;
use App\Domains\ProductBrand\Services\ProductBrandImageService;
use Illuminate\Routing\Controller;

use Illuminate\Http\JsonResponse;

class ProductBrandImageController extends Controller
{
    public function __construct(
        private readonly ProductBrandImageService $productBrandImageService
    ) {}

    public function update(UpdateProductBrandImageRequest $request, string $id, string $fileUsageId): JsonResponse
    {
        $dto = BrandImageUpdateData::fromArray($request->validated());
        $fileUsage = $this->productBrandImageService->update($id, $fileUsageId, $dto);

        if ($fileUsage === null) {
            return response()->json([
                'message' => 'Brand image not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Brand image updated successfully.',
            'data' => [
                'id' => $fileUsage->id,
            ],
        ]);
    }

    public function delete(string $id, string $fileUsageId): JsonResponse
    {
        $deleted = $this->productBrandImageService->delete($id, $fileUsageId);
        if (! $deleted) {
            return response()->json([
                'message' => 'Brand image not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Brand image deleted successfully.',
        ]);
    }
}
