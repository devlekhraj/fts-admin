<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\Controllers;

use App\Domains\ProductCategory\Requests\UpdateProductCategoryImageRequest;
use App\Domains\ProductCategory\DTOs\CategoryImageUpdateData;
use App\Domains\ProductCategory\Services\ProductCategoryImageService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ProductCategoryImageController extends Controller
{
    public function __construct(
        private readonly ProductCategoryImageService $productCategoryImageService
    ) {}

    public function update(UpdateProductCategoryImageRequest $request, string $id, string $fileUsageId): JsonResponse
    {
        $dto = CategoryImageUpdateData::fromArray($request->validated());
        $fileUsage = $this->productCategoryImageService->update($id, $fileUsageId, $dto);

        if ($fileUsage === null) {
            return response()->json([
                'message' => 'Product category image not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Product category image updated successfully.',
            'data' => [
                'id' => $fileUsage->id,
            ],
        ]);
    }

    public function delete(string $id, string $fileUsageId): JsonResponse
    {
        $deleted = $this->productCategoryImageService->delete($id, $fileUsageId);
        if (! $deleted) {
            return response()->json([
                'message' => 'Product category image not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Product category image deleted successfully.',
        ]);
    }
}
