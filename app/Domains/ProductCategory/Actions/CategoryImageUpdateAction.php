<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\Actions;

use App\Domains\ProductCategory\DTOs\CategoryImageUpdateData;
use App\Domains\File\Models\FileUsage;
use Illuminate\Support\Facades\DB;

final class CategoryImageUpdateAction
{
    public function execute(string $categoryId, string $fileUsageId, CategoryImageUpdateData $data): ?FileUsage
    {
        $fileUsage = FileUsage::query()
            ->where('id', $fileUsageId)
            ->where('usage_type', 'product_categories')
            ->where('usage_id', $categoryId)
            ->first();

        if (! $fileUsage) {
            return null;
        }

        $meta = is_array($fileUsage->meta) ? $fileUsage->meta : [];
        $meta['is_default'] = $data->isDefault;

        DB::transaction(function () use ($fileUsage, $data, $meta): void {
            if ($data->isDefault) {
                FileUsage::query()
                    ->where('usage_type', 'product_categories')
                    ->where('usage_id', $fileUsage->usage_id)
                    ->where('id', '!=', $fileUsage->id)
                    ->update([
                        'meta->is_default' => false,
                    ]);
            }

            $fileUsage->alt_text = trim($data->altText);
            $fileUsage->meta = $meta;
            $fileUsage->save();
        });

        return $fileUsage;
    }
}

