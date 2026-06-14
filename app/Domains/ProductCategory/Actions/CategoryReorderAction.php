<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\Actions;

use App\Domains\ProductCategory\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class CategoryReorderAction
{
    /**
     * @param  array<int, int|string>  $categoryIds
     */
    public function execute(array $categoryIds): void
    {
        $ids = array_values(array_map(static fn ($id) => (int) $id, $categoryIds));

        if ($ids === []) {
            throw new InvalidArgumentException('At least one category id is required.');
        }

        $existingIds = ProductCategory::query()
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        sort($existingIds);
        $requestedIds = $ids;
        sort($requestedIds);

        if ($existingIds !== $requestedIds) {
            throw new InvalidArgumentException('One or more categories were not found.');
        }

        DB::transaction(function () use ($ids): void {
            foreach ($ids as $index => $id) {
                ProductCategory::query()
                    ->where('id', $id)
                    ->update([
                        'seq_no' => $index + 1,
                    ]);
            }
        });
    }
}
