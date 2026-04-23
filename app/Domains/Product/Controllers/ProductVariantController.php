<?php

declare(strict_types=1);

namespace App\Domains\Product\Controllers;

use App\Domains\Product\Actions\ProductVariantCreateAction;
use App\Domains\Product\Actions\ProductVariantUpdateAction;
use App\Domains\Product\Requests\StoreProductVariantRequest;
use App\Domains\Product\Requests\UpdateProductVariantRequest;
use App\Support\Exceptions\FieldValidationException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ProductVariantController extends Controller
{
    public function __construct(
        private ProductVariantCreateAction $productVariantCreateAction,
        private ProductVariantUpdateAction $productVariantUpdateAction,
    ) {}

    public function store(StoreProductVariantRequest $request, string $id): JsonResponse
    {
        $validated = $request->validated();
        try {
            $result = $this->productVariantCreateAction->execute(
                productId: (int) $id,
                price: (float) $validated['price'],
                quantity: (int) $validated['quantity'],
                attributes: is_array($validated['attributes'] ?? null) ? $validated['attributes'] : [],
            );

            return response()->json($result, 201);
        } catch (FieldValidationException $e) {
            $message = $e->getMessage();
            $field = $e->field();
            $errors = $field ? [$field => [$message]] : [];

            return response()->json([
                'message' => $message,
                'errors' => $errors,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create variant.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateProductVariantRequest $request, string $id, string $item_id): JsonResponse
    {
        $validated = $request->validated();
        try {
            $result = $this->productVariantUpdateAction->execute(
                productId: (int) $id,
                variantId: (int) $item_id,
                price: (float) $validated['price'],
                quantity: (int) $validated['quantity'],
                attributes: is_array($validated['attributes'] ?? null) ? $validated['attributes'] : [],
            );

            return response()->json($result, 200);
        } catch (FieldValidationException $e) {
            $message = $e->getMessage();
            $field = $e->field();
            $errors = $field ? [$field => [$message]] : [];

            return response()->json([
                'message' => $message,
                'errors' => $errors,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update variant.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
