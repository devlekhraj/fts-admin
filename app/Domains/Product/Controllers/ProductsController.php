<?php

declare(strict_types=1);

namespace App\Domains\Product\Controllers;

use App\Domains\Product\Models\ProductVariant;
use App\Domains\Product\Models\Product;
use App\Domains\Product\Requests\StoreProductRequest;
use App\Domains\Product\Requests\UpdateProductPriceRequest;
use App\Domains\Product\Requests\UpdateProductRequest;
use App\Domains\Faq\Resources\FaqResource;
use App\Domains\Product\Resources\ProductResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

use App\Domains\Faq\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function productList(Request $request): JsonResponse
    {
        $query = Product::query()
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
        $product = Product::query()
            ->with(['defaultFile', 'files', 'variants.files', 'attribute.attributes', 'brand.defaultFile', 'categories', 'giftItems.defaultFile'])
            ->findOrFail($id);

        return response()->json([
            'data' => (new ProductResource($product)),
            'success' => true,
        ], 200);
    }

    public function faqs(Request $request, string $id): JsonResponse
    {
        // Ensure product exists.
        Product::query()
            ->select(['id', 'name'])
            ->findOrFail($id);

        $query = Faq::query()
            ->where('type_id', $id)
            ->whereIn('type', ['product', 'products', Product::class])
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

        $product = Product::query()->create($validated);

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => (new ProductResource($product)),
            'success' => true,
        ], 201);
    }

    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $product = Product::query()->findOrFail($id);
        $validated = $request->validated();
        $categoryIds = $validated['category_ids'] ?? null;
        unset($validated['category_ids']);

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

        if (is_array($categoryIds)) {
            $product->categories()->sync($categoryIds);
        }

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

    public function updatePrice(UpdateProductPriceRequest $request, string $id): JsonResponse
    {
        $product = Product::query()->findOrFail($id);
        $validated = $request->validated();
        $variants = is_array($validated['variants'] ?? null) ? $validated['variants'] : [];
        unset($validated['variants']);

        DB::transaction(function () use ($product, $validated, $variants): void {
            $product->update([
                'price' => $validated['price'],
                'original_price' => $validated['original_price'] ?? null,
                'quantity' => $validated['quantity'] ?? null,
                'pre_order' => (bool) $validated['pre_order'],
                'pre_order_price' => $validated['pre_order_price'] ?? null,
            ]);

            foreach ($variants as $variantPayload) {
                $variantId = (int) ($variantPayload['id'] ?? 0);
                if ($variantId <= 0) {
                    continue;
                }

                $variant = ProductVariant::query()
                    ->where('product_id', $product->id)
                    ->where('id', $variantId)
                    ->firstOrFail();

                $variant->update([
                    'price' => $variantPayload['price'],
                    'quantity' => $variantPayload['quantity'],
                ]);
            }
        });

        $freshProduct = Product::query()
            ->with(['defaultFile', 'files', 'variants.files', 'attribute.attributes', 'brand.defaultFile', 'categories', 'giftItems.defaultFile'])
            ->findOrFail($product->id);

        return response()->json([
            'message' => 'Product price updated successfully.',
            'data' => new ProductResource($freshProduct),
            'success' => true,
        ], 200);
    }

    public function deleteProduct(string $id): JsonResponse
    {
        $product = Product::query()->findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
            'success' => true,
        ], 200);
    }
}
