<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin\Product;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Foundation\Interfaces\Http\Requests\Admin\StoreProductRequest;
use App\Foundation\Interfaces\Http\Requests\Admin\UpdateProductRequest;
use App\Foundation\Interfaces\Http\Resources\ProductResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ProductsController extends Controller
{
    public function productList(Request $request): JsonResponse
    {
        $query = ProductModel::query()
            ->with('defaultFile')
            ->withCount('variants')
            ->orderByDesc('created_at');

        if ($search = $request->query('search')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $perPageParam = (int) $request->query('per_page', 15);
        if ($perPageParam === -1) {
            $items = $query->get();

            return response()->json([
                'data' => ProductResource::collection($items),
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
            'data' => ProductResource::collection($paginator->items()),
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

    public function show(string $id): JsonResponse
    {
        $product = ProductModel::query()
            ->with(['defaultFile', 'files', 'variants.files', 'attribute.attributes','brand.defaultFile'])
            ->findOrFail($id);

        return response()->json([
            'data' => (new ProductResource($product)),
            'success' => true,
        ], 200);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['sku'] = $validated['slug'];

        $product = ProductModel::query()->create($validated);

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => (new ProductResource($product)),
            'success' => true,
        ], 201);
    }

    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $product = ProductModel::query()->findOrFail($id);
        $validated = $request->validated();

        if (array_key_exists('attributes', $validated)) {
            $attributes = $validated['attributes'];
            $product->attributes = is_array($attributes) ? $attributes : null;

            if (is_array($attributes) && array_key_exists('attribute_class_id', $attributes)) {
                $attributeClassId = $attributes['attribute_class_id'];
                $product->attribute_class_id = ($attributeClassId === null || $attributeClassId === '')
                    ? null
                    : (int) $attributeClassId;
            }
        }

        $product->update($validated);

        $product->save();

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => [
                'id' => $product->id,
                'attributes' => $product->attributes,
                'attribute_class_id' => $product->attribute_class_id,
            ],
            'success' => true,
        ], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        // TODO: Delete product.
        return response()->json(null, 204);
    }
}
