<?php

declare(strict_types=1);

namespace App\Domains\ProductBrand\Actions;

use App\Domains\ProductBrand\Models\ProductBrand;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class BrandReorderAction
{
    /**
     * @param  array<int, int|string>  $brandIds
     */
    public function execute(array $brandIds): void
    {
        $ids = array_values(array_map(static fn ($id) => (int) $id, $brandIds));

        if ($ids === []) {
            throw new InvalidArgumentException('At least one brand id is required.');
        }

        $existingIds = ProductBrand::query()
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        sort($existingIds);
        $requestedIds = $ids;
        sort($requestedIds);

        if ($existingIds !== $requestedIds) {
            throw new InvalidArgumentException('One or more brands were not found.');
        }

        DB::transaction(function () use ($ids): void {
            foreach ($ids as $index => $id) {
                ProductBrand::query()
                    ->where('id', $id)
                    ->update([
                        'seq_no' => $index + 1,
                    ]);
            }
        });
    }
}
