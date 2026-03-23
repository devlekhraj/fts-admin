<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin\Product;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\ProductCategoryModel;
use App\Foundation\Interfaces\Http\Requests\Admin\UpdateProductCategoryRequest;
use App\Foundation\Interfaces\Http\Resources\ProductCategoryResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function categoryList(Request $request): JsonResponse
    {
        $query = ProductCategoryModel::query()
            ->with('defaultFile')
            ->orderByDesc('created_at');

        if ($search = $request->query('search')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%");
            });
        }

        $perPageParam = (int) $request->query('per_page', 15);
        if ($perPageParam === -1) {
            $items = $query->get();

            return response()->json([
                'data' => ProductCategoryResource::collection($items),
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
            'data' => ProductCategoryResource::collection($paginator->items()),
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

    public function categoryShow(int $id): JsonResponse
    {
        $category = ProductCategoryModel::query()
            ->with(['defaultFile', 'files'])
            ->findOrFail($id);

        return response()->json([
            'data' => (new ProductCategoryResource($category)),
            'success' => true,
        ], 200);
    }

    public function update(UpdateProductCategoryRequest $request, string $id): JsonResponse
    {
        $category = ProductCategoryModel::query()->findOrFail($id);
        $category->update($request->validated());

        return response()->json([
            'message' => 'Product category updated successfully.',
            'data' => new ProductCategoryResource($category),
        ]);
    }
    public function getList(Request $request)
    {
        $categories = ProductCategoryModel::with('defaultFile')
            ->has('products')
            ->orderBy('title', 'asc')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'title' => $category->title,
                    'slug' => $category->slug,
                    'thumb' => $category->defaultFile->first()?->url,
                ];
            });

        return response()->json($categories);
    }
}
