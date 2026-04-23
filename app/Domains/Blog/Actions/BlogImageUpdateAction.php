<?php

declare(strict_types=1);

namespace App\Domains\Blog\Actions;

use App\Domains\Blog\DTOs\BlogImageUpdateData;
use App\Domains\File\Models\FileUsage;
use Illuminate\Support\Facades\DB;

final class BlogImageUpdateAction
{
    public function execute(string $blogId, string $fileUsageId, BlogImageUpdateData $data): ?FileUsage
    {
        $fileUsage = FileUsage::query()
            ->where('id', $fileUsageId)
            ->where('usage_type', 'blogs')
            ->where('usage_id', $blogId)
            ->first();

        if (! $fileUsage) {
            return null;
        }

        $meta = is_array($fileUsage->meta) ? $fileUsage->meta : [];
        $meta['is_default'] = $data->isDefault;

        DB::transaction(function () use ($fileUsage, $data, $meta): void {
            if ($data->isDefault) {
                FileUsage::query()
                    ->where('usage_type', 'blogs')
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

