<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin\Product;

use App\Foundation\Application\Product\Commands\CreateProductVariantCommand;
use App\Foundation\Application\Product\Commands\UpdateProductVariantCommand;
use App\Foundation\Application\Product\Handlers\CreateProductVariantHandler;
use App\Foundation\Application\Product\Handlers\UpdateProductVariantHandler;
use App\Foundation\Interfaces\Http\Requests\Admin\StoreProductVariantRequest;
use App\Foundation\Interfaces\Http\Requests\Admin\UpdateProductVariantRequest;
use App\Foundation\Shared\Domain\Exceptions\FieldValidationException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ProductVariantController extends Controller
{
    public function __construct(
        private CreateProductVariantHandler $createProductVariantHandler,
        private UpdateProductVariantHandler $updateProductVariantHandler,
    ) {}

    public function store(StoreProductVariantRequest $request, string $id): JsonResponse
    {
        $validated = $request->validated();
        try {
            $result = $this->createProductVariantHandler->handle(new CreateProductVariantCommand(
                productId: (int) $id,
                price: (float) $validated['price'],
                quantity: (int) $validated['quantity'],
                attributes: is_array($validated['attributes'] ?? null) ? $validated['attributes'] : [],
            ));

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
            $result = $this->updateProductVariantHandler->handle(new UpdateProductVariantCommand(
                productId: (int) $id,
                variantId: (int) $item_id,
                price: (float) $validated['price'],
                quantity: (int) $validated['quantity'],
                attributes: is_array($validated['attributes'] ?? null) ? $validated['attributes'] : [],
            ));

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
