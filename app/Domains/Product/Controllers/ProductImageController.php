<?php

declare(strict_types=1);

namespace App\Domains\Product\Controllers;

use App\Domains\File\Models\FileUsage;
use App\Domains\Product\Requests\UpdateProductImageRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProductImageController extends Controller
{
    public function update(UpdateProductImageRequest $request, string $id, string $fileUsageId): JsonResponse
    {
        $validated = $request->validated();

        $fileUsage = FileUsage::query()
            ->where('id', $fileUsageId)
            ->where('usage_type', 'products')
            ->where('usage_id', $id)
            ->first();

        if (! $fileUsage) {
            return response()->json([
                'message' => 'Product image not found.',
            ], 404);
        }

        $isDefault = (bool) ($validated['is_default'] ?? false);
        $meta = is_array($fileUsage->meta) ? $fileUsage->meta : [];
        $meta['is_default'] = $isDefault;

        DB::transaction(function () use ($fileUsage, $validated, $meta, $isDefault): void {
            if ($isDefault) {
                FileUsage::query()
                    ->where('usage_type', 'products')
                    ->where('usage_id', $fileUsage->usage_id)
                    ->where('id', '!=', $fileUsage->id)
                    ->update([
                        'meta->is_default' => false,
                    ]);
            }

            $fileUsage->alt_text = trim((string) $validated['alt_text']);
            $fileUsage->meta = $meta;
            $fileUsage->save();
        });

        return response()->json([
            'message' => 'Product image updated successfully.',
            'data' => [
                'id' => $fileUsage->id,
            ],
        ]);
    }

    public function delete(string $id, string $fileUsageId): JsonResponse
    {
        $fileUsage = FileUsage::query()
            ->where('id', $fileUsageId)
            ->where('usage_type', 'products')
            ->where('usage_id', $id)
            ->first();

        if (! $fileUsage) {
            return response()->json([
                'message' => 'Product image not found.',
            ], 404);
        }

        $fileUsage->delete();

        return response()->json([
            'message' => 'Product image deleted successfully.',
        ]);
    }
}
