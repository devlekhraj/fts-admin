<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin\Blog;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\FileUsageModel;
use App\Foundation\Interfaces\Http\Requests\Admin\UpdateBlogImageRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BlogImageController extends Controller
{
    public function update(UpdateBlogImageRequest $request, string $id, string $fileUsageId): JsonResponse
    {
        $validated = $request->validated();

        $fileUsage = FileUsageModel::query()
            ->where('id', $fileUsageId)
            ->where('usage_type', 'blogs')
            ->where('usage_id', $id)
            ->first();

        if (! $fileUsage) {
            return response()->json([
                'message' => 'Blog image not found.',
            ], 404);
        }

        $isDefault = (bool) ($validated['is_default'] ?? false);
        $meta = is_array($fileUsage->meta) ? $fileUsage->meta : [];
        $meta['is_default'] = $isDefault;

        DB::transaction(function () use ($fileUsage, $validated, $meta, $isDefault): void {
            if ($isDefault) {
                FileUsageModel::query()
                    ->where('usage_type', 'blogs')
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
            'message' => 'Blog image updated successfully.',
            'data' => [
                'id' => $fileUsage->id,
            ],
        ]);
    }

    public function delete(string $id, string $fileUsageId): JsonResponse
    {
        $fileUsage = FileUsageModel::query()
            ->where('id', $fileUsageId)
            ->where('usage_type', 'blogs')
            ->where('usage_id', $id)
            ->first();

        if (! $fileUsage) {
            return response()->json([
                'message' => 'Blog image not found.',
            ], 404);
        }

        $fileUsage->delete();

        return response()->json([
            'message' => 'Blog image deleted successfully.',
        ]);
    }
}
