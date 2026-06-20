<?php

declare(strict_types=1);

namespace App\Domains\ProductBrand\Controllers;

use App\Domains\File\Models\FileUsage;
use App\Domains\File\Services\FileService;
use App\Domains\File\Actions\FileUploadAction;
use App\Domains\File\Models\File;
use App\Domains\ProductBrand\Models\ProductBrand;
use App\Domains\ProductBrand\Requests\StoreBrandBannerRequest;
use App\Domains\ProductBrand\Requests\UpdateBrandBannerRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

final class ProductBrandBannerController extends Controller
{
    private const USAGE_TYPE = 'product_brands';

    public function __construct(
        private readonly FileService $fileService,
        private readonly FileUploadAction $fileUploadAction,
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
        $image = $request->file('image');

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
        ], $image);

        return response()->json($result['body'], (int) $result['status']);
    }

    public function update(UpdateBrandBannerRequest $request, string $id, string $fileUsageId): JsonResponse
    {
        $brand = ProductBrand::query()->find($id);

        if (! $brand) {
            return response()->json([
                'message' => 'Brand not found.',
            ], 404);
        }

        $validated = $request->validated();
        $image = $request->file('image');

        $fileUsage = FileUsage::query()
            ->where('id', $fileUsageId)
            ->where('usage_type', self::USAGE_TYPE)
            ->where('usage_id', $brand->id)
            ->first();

        if (! $fileUsage) {
            return response()->json([
                'message' => 'Brand banner not found.',
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
            'message' => 'Brand banner updated successfully.',
            'data' => [
                'id' => $fileUsage->id,
            ],
        ]);
    }

    public function delete(string $id, string $fileUsageId): JsonResponse
    {
        $brand = ProductBrand::query()->find($id);

        if (! $brand) {
            return response()->json([
                'message' => 'Brand not found.',
            ], 404);
        }

        $fileUsage = FileUsage::query()
            ->where('id', $fileUsageId)
            ->where('usage_type', self::USAGE_TYPE)
            ->where('usage_id', $brand->id)
            ->whereRaw("LOWER(REPLACE(CAST(JSON_EXTRACT(meta, '$.type') AS CHAR), '\"', '')) = 'banner'")
            ->first();

        if (! $fileUsage) {
            return response()->json([
                'message' => 'Brand banner not found.',
            ], 404);
        }

        $fileUsage->delete();

        return response()->json([
            'message' => 'Brand banner deleted successfully.',
            'data' => [
                'id' => $fileUsage->id,
            ],
        ]);
    }
}
