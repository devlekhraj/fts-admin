<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\Controllers;

use App\Domains\File\Actions\FileUploadAction;
use App\Domains\File\Models\File;
use App\Domains\File\Models\FileUsage;
use App\Domains\File\Services\FileService;
use App\Domains\ProductCategory\Models\ProductCategory;
use App\Domains\ProductCategory\Requests\StoreProductCategoryBannerRequest;
use App\Domains\ProductCategory\Requests\UpdateProductCategoryBannerRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

final class ProductCategoryBannerController extends Controller
{
    private const USAGE_TYPE = 'product_categories';

    public function __construct(
        private readonly FileService $fileService,
        private readonly FileUploadAction $fileUploadAction,
    ) {}

    public function store(StoreProductCategoryBannerRequest $request, string $id): JsonResponse
    {
        $category = ProductCategory::query()->find($id);

        if (! $category) {
            return response()->json([
                'message' => 'Product category not found.',
            ], 404);
        }

        $validated = $request->validated();
        $image = $request->file('image');

        $result = $this->fileService->assign([
            'source' => 'upload',
            'usage_type' => $category->getTable(),
            'usage_id' => $category->id,
            'alt_text' => '',
            'meta' => [
                'type' => 'banner',
                'redirect_url' => trim((string) ($validated['redirect_url'] ?? '')),
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'status' => (string) $validated['status'],
            ],
        ], $image);

        return response()->json($result['body'], (int) $result['status']);
    }

    public function update(UpdateProductCategoryBannerRequest $request, string $id, string $fileUsageId): JsonResponse
    {
        $category = ProductCategory::query()->find($id);

        if (! $category) {
            return response()->json([
                'message' => 'Product category not found.',
            ], 404);
        }

        $validated = $request->validated();
        $image = $request->file('image');

        $fileUsage = FileUsage::query()
            ->where('id', $fileUsageId)
            ->where('usage_type', self::USAGE_TYPE)
            ->where('usage_id', $category->id)
            ->first();

        if (! $fileUsage) {
            return response()->json([
                'message' => 'Product category banner not found.',
            ], 404);
        }

        $meta = is_array($fileUsage->meta) ? $fileUsage->meta : [];
        $meta['type'] = 'banner';
        $meta['redirect_url'] = trim((string) ($validated['redirect_url'] ?? ''));
        $meta['start_date'] = $validated['start_date'] ?? null;
        $meta['end_date'] = $validated['end_date'] ?? null;
        $meta['status'] = (string) $validated['status'];

        DB::transaction(function () use ($fileUsage, $image, $meta): void {
            if ($image) {
                $uploadResult = $this->fileUploadAction->execute($image, self::USAGE_TYPE);
                $newFile = $uploadResult['fileModel'] ?? null;
                if ($newFile instanceof File) {
                    $newFile->update([
                        'is_public' => true,
                    ]);
                    $fileUsage->file_id = (int) $newFile->id;
                }
            }

            $fileUsage->title = null;
            $fileUsage->alt_text = '';
            $fileUsage->meta = $meta;
            $fileUsage->save();
        });

        return response()->json([
            'message' => 'Product category banner updated successfully.',
            'data' => [
                'id' => $fileUsage->id,
            ],
        ]);
    }

    public function delete(string $id, string $fileUsageId): JsonResponse
    {
        $category = ProductCategory::query()->find($id);

        if (! $category) {
            return response()->json([
                'message' => 'Product category not found.',
            ], 404);
        }

        $fileUsage = FileUsage::query()
            ->where('id', $fileUsageId)
            ->where('usage_type', self::USAGE_TYPE)
            ->where('usage_id', $category->id)
            ->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.type'))) = 'banner'")
            ->first();

        if (! $fileUsage) {
            return response()->json([
                'message' => 'Product category banner not found.',
            ], 404);
        }

        $fileUsage->delete();

        return response()->json([
            'message' => 'Product category banner deleted successfully.',
            'data' => [
                'id' => $fileUsage->id,
            ],
        ]);
    }
}
