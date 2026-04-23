<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\Controllers;

use App\Domains\ProductCategory\Models\ProductCategory;
use App\Domains\ProductCategory\DTOs\CategoryCreateData;
use App\Domains\ProductCategory\DTOs\CategoryUpdateData;
use App\Domains\ProductCategory\Requests\UpdateProductCategoryRequest;
use App\Domains\ProductCategory\Requests\StoreProductCategoryRequest;
use App\Domains\Faq\Resources\FaqResource;
use App\Domains\ProductCategory\Resources\ProductCategoryResource;
use App\Domains\ProductCategory\Services\ProductCategoryService;
use App\Http\Controllers\Controller;
use App\Domains\Faq\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function __construct(
        private readonly ProductCategoryService $productCategoryService
    ) {}

    public function categoryList(Request $request): JsonResponse
    {
        $perPageParam = (int) $request->query('per_page', 15);
        $search = $request->query('search');
        $result = $this->productCategoryService->list(is_string($search) ? $search : null, $perPageParam);

        return response()->json([
            'data' => ProductCategoryResource::collection($result['items'] ?? []),
            'meta' => $result['meta'] ?? null,
        ]);
    }

    public function categoryShow(int $id): JsonResponse
    {
        $category = $this->productCategoryService->detail((string) $id);

        return response()->json([
            'data' => (new ProductCategoryResource($category)),
            'success' => true,
        ], 200);
    }

    public function faqs(Request $request, string $id): JsonResponse
    {
        // Ensure category exists.
        ProductCategory::query()
            ->select(['id', 'title'])
            ->findOrFail($id);

        $query = Faq::query()
            ->where('type', 'category')
            ->where('type_id', $id)
            ->with('category')
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

    public function store(StoreProductCategoryRequest $request): JsonResponse
    {
        $dto = CategoryCreateData::fromArray($request->validated());
        $category = $this->productCategoryService->create($dto);

        return response()->json([
            'message' => 'Product category created successfully.',
            'data' => new ProductCategoryResource($category),
            'success' => true,
        ], 201);
    }

    public function update(UpdateProductCategoryRequest $request, string $id): JsonResponse
    {
        $dto = CategoryUpdateData::fromArray($request->validated());
        $category = $this->productCategoryService->update($id, $dto);

        return response()->json([
            'message' => 'Product category updated successfully.',
            'data' => new ProductCategoryResource($category),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->productCategoryService->delete($id);

        return response()->json([
            'message' => 'Product category deleted successfully.',
            'success' => true,
        ], 200);
    }
    public function getList(Request $request)
    {
        $categories = ProductCategory::with('defaultFile')
            ->withCount('products')
            ->has('products')
            ->orderBy('title', 'asc')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'title' => $category->title,
                    'slug' => $category->slug,
                    'thumb' => $category->defaultFile->first()?->url,
                    'products_count' => $category->products_count ?? 0,
                ];
            });

        return response()->json($categories);
    }
}
