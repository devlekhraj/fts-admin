<?php

declare(strict_types=1);

namespace App\Domains\ProductBrand\Controllers;

use App\Domains\ProductBrand\Models\ProductBrand;
use App\Domains\ProductBrand\Requests\StoreBrandRequest;
use App\Domains\ProductBrand\Requests\UpdateBrandRequest;
use App\Domains\Faq\Resources\FaqResource;
use App\Domains\ProductBrand\DTOs\BrandCreateData;
use App\Domains\ProductBrand\DTOs\BrandUpdateData;
use App\Domains\ProductBrand\Resources\ProductBrandResource;
use App\Domains\ProductBrand\Services\ProductBrandService;
use Illuminate\Routing\Controller;

use App\Domains\Faq\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductBrandController extends Controller
{
    public function __construct(
        private readonly ProductBrandService $productBrandService
    ) {}

    public function brandList(Request $request): JsonResponse
    {
        $perPageParam = (int) $request->query('per_page', 15);
        $search = $request->query('search');
        $result = $this->productBrandService->list(is_string($search) ? $search : null, $perPageParam);

        return response()->json([
            'data' => ProductBrandResource::collection($result['items'] ?? []),
            'meta' => $result['meta'] ?? null,
        ]);
    }

    public function brandShow(string $id): JsonResponse
    {
        $brand = $this->productBrandService->detail($id);

        $brand->load('banners', 'files','defaultFile');
        return response()->json([
            'data' => (new ProductBrandResource($brand)),
            'success' => true,
        ], 200);
    }

    public function faqs(Request $request, string $id): JsonResponse
    {
        // Ensure brand exists.
        ProductBrand::query()
            ->select(['id', 'name'])
            ->findOrFail($id);

        $query = Faq::query()
            ->where('type', 'brand')
            ->where('type_id', $id)
            ->with('brand')
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

    public function store(StoreBrandRequest $request): JsonResponse
    {
        $dto = BrandCreateData::fromArray($request->validated());
        $brand = $this->productBrandService->create($dto);

        return response()->json([
            'message' => 'Brand created successfully.',
            'data' => new ProductBrandResource($brand),
            'success' => true,
        ], 201);
    }

    public function update(UpdateBrandRequest $request, string $id): JsonResponse
    {
        $dto = BrandUpdateData::fromArray($request->validated());
        $brand = $this->productBrandService->update($id, $dto);

        return response()->json([
            'message' => 'Brand updated successfully.',
            'data' => new ProductBrandResource($brand),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->productBrandService->delete($id);

        return response()->json([
            'message' => 'Brand deleted successfully.',
            'success' => true,
        ], 200);
    }

    public function getList(Request $request)
    {
        $brands = ProductBrand::with('defaultFile')
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
