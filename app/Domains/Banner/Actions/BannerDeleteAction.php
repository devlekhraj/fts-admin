<?php

declare(strict_types=1);

namespace App\Domains\Banner\Actions;

use App\Domains\Banner\Models\Banner;

final class BannerDeleteAction
{
    public function execute(Banner $banner): void
    {
        // Clean up related records/pivots if present.
        try {
            $banner->files()->detach();
            $banner->bannerImages()->delete();
        } catch (\Throwable $e) {
            // Best-effort cleanup; continue with deletion.
        }

        $banner->delete();
    }
}
