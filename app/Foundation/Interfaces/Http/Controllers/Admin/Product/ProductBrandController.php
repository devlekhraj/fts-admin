<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin\Product;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\ProductBrandModel;
use App\Foundation\Interfaces\Http\Requests\Admin\UpdateBrandRequest;
use App\Foundation\Interfaces\Http\Requests\Admin\StoreBrandRequest;
use App\Foundation\Interfaces\Http\Resources\ProductBrandResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductBrandController extends Controller
{
    public function brandList(Request $request): JsonResponse
    {
        $query = ProductBrandModel::query()
            ->with('defaultFile')
            ->withCount('products')
            ->orderByDesc('created_at');

        if ($search = $request->query('search')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%");
            });
        }

        $perPageParam = (int) $request->query('per_page', 15);
        if ($perPageParam === -1) {
            $items = $query->get();

            return response()->json([
                'data' => ProductBrandResource::collection($items),
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
            'data' => ProductBrandResource::collection($paginator->items()),
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

    public function brandShow(string $id): JsonResponse
    {
        $brand = ProductBrandModel::query()
            ->with(['defaultFile', 'files'])
            ->withCount('products')
            ->findOrFail($id);

        return response()->json([
            'data' => (new ProductBrandResource($brand)),
            'success' => true,
        ], 200);
    }

    public function store(StoreBrandRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $brand = ProductBrandModel::query()->create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'status' => array_key_exists('status', $validated) ? (bool) $validated['status'] : true,
        ]);

        return response()->json([
            'message' => 'Brand created successfully.',
            'data' => new ProductBrandResource($brand),
            'success' => true,
        ], 201);
    }

    public function update(UpdateBrandRequest $request, string $id): JsonResponse
    {
        $brand = ProductBrandModel::query()->findOrFail($id);
        $brand->update($request->validated());

        return response()->json([
            'message' => 'Brand updated successfully.',
            'data' => new ProductBrandResource($brand),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $brand = ProductBrandModel::query()->findOrFail($id);
        $brand->delete(); // Soft delete

        return response()->json([
            'message' => 'Brand deleted successfully.',
            'success' => true,
        ], 200);
    }

    public function getList(Request $request)
    {
        $brands = ProductBrandModel::with('defaultFile')
            ->has('products') // Only brands with at least one product
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'thumb' => $brand->defaultFile->first()?->url,
                ];
            });

        return response()->json($brands);
    }
}
