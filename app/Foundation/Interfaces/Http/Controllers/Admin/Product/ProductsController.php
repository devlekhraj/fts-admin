<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin\Product;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Foundation\Interfaces\Http\Requests\Admin\StoreProductRequest;
use App\Foundation\Interfaces\Http\Requests\Admin\UpdateProductRequest;
use App\Foundation\Interfaces\Http\Resources\FaqResource;
use App\Foundation\Interfaces\Http\Resources\ProductResource;
use App\Http\Controllers\Controller;
use App\Models\FaqModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function productList(Request $request): JsonResponse
    {
        $query = ProductModel::query()
            ->with('defaultFile')
            ->withCount('variants')
            ->withCount('files')
            ->orderByDesc('created_at');

        if ($name = $request->query('name')) {
            $query->where('name', 'like', "%{$name}%");
        }

        if ($categoryId = $request->query('category_id')) {
            $query->whereHas('categories', function ($builder) use ($categoryId) {
                $builder->where('product_category_id', $categoryId);
            });
        }

        if ($brandId = $request->query('brand_id')) {
            $query->where('brand_id', $brandId);
        }

        // if ($search = $request->query('search')) {
        //     $query->where(function ($builder) use ($search) {
        //         $builder->where('name', 'like', "%{$search}%")
        //             ->orWhere('slug', 'like', "%{$search}%");
        //     });
        // }
        if ($search = $request->query('search')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });

            $query->orderByRaw("
        CASE 
            WHEN name LIKE ? THEN 1   -- starts with 'iphone'
            WHEN name LIKE ? THEN 2   -- contains 'iphone'
            WHEN slug LIKE ? THEN 3
            ELSE 4
        END
    ", [
                "{$search}%",   // highest priority
                "%{$search}%",  // medium
                "%{$search}%",  // lower
            ]);
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
            ->with(['defaultFile', 'files', 'variants.files', 'attribute.attributes', 'brand.defaultFile'])
            ->findOrFail($id);

        return response()->json([
            'data' => (new ProductResource($product)),
            'success' => true,
        ], 200);
    }

    public function faqs(Request $request, string $id): JsonResponse
    {
        // Ensure product exists.
        ProductModel::query()
            ->select(['id', 'name'])
            ->findOrFail($id);

        $query = FaqModel::query()
            ->where('type_id', $id)
            ->whereIn('type', ['product', 'products', ProductModel::class])
            ->with('product')
            ->orderByDesc('updated_at');

        if ($search = $request->query('search')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        $perPageParam = (int) $request->query('per_page', 15);
        if ($perPageParam === -1) {
            $items = $query->get();

            return response()->json([
                'data' => FaqResource::collection($items),
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
            'data' => FaqResource::collection($paginator->items()),
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
