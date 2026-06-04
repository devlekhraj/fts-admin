<?php

declare(strict_types=1);

namespace App\Domains\ProductBrand\Controllers;

use App\Domains\File\Services\FileService;
use App\Domains\ProductBrand\Models\ProductBrand;
use App\Domains\ProductBrand\Requests\StoreBrandBannerRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class ProductBrandBannerController extends Controller
{
    private const USAGE_TYPE = 'product_brands';

    public function __construct(
        private readonly FileService $fileService,
    ) {}

    public function store(StoreBrandBannerRequest $request, string $id): JsonResponse
    {
        $brand = ProductBrand::query()->find($id);

        if (! $brand) {
            return response()->json([
                'message' => 'Brand not found.',
            ], 404);
        }

        $validated = $request->validated();

        $result = $this->fileService->assign([
            'source' => 'upload',
            'usage_type' => $brand->getTable(),
            'usage_id' => $brand->id,
            'alt_text' => '',
            'meta' => [
                'type' => 'banner',
                'redirect_url' => trim((string) ($validated['redirect_url'] ?? '')),
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'status' => (string) $validated['status'],
            ],
        ], $request->file('image'));

        return response()->json($result['body'], (int) $result['status']);
    }
}
