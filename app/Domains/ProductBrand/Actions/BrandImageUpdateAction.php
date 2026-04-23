<?php

declare(strict_types=1);

namespace App\Domains\ProductBrand\Actions;

use App\Domains\ProductBrand\DTOs\BrandImageUpdateData;
use App\Domains\File\Models\FileUsage;
use Illuminate\Support\Facades\DB;

final class BrandImageUpdateAction
{
    public function execute(string $brandId, string $fileUsageId, BrandImageUpdateData $data): ?FileUsage
    {
        $fileUsage = FileUsage::query()
            ->where('id', $fileUsageId)
            ->where('usage_type', 'product_brands')
            ->where('usage_id', $brandId)
            ->first();

        if (! $fileUsage) {
            return null;
        }

        $meta = is_array($fileUsage->meta) ? $fileUsage->meta : [];
        $meta['is_default'] = $data->isDefault;

        DB::transaction(function () use ($fileUsage, $data, $meta): void {
            if ($data->isDefault) {
                FileUsage::query()
                    ->where('usage_type', 'product_brands')
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

