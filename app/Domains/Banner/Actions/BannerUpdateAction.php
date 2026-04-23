<?php

declare(strict_types=1);

namespace App\Domains\Banner\Actions;

use App\Domains\Banner\DTOs\BannerUpdateData;
use App\Domains\Banner\Models\Banner;

final class BannerUpdateAction
{
    public function execute(Banner $banner, BannerUpdateData $data): Banner
    {
        $banner->update([
            'name' => $data->name,
            'slug' => $data->slug,
            'status' => $data->status,
        ]);

        return $banner->refresh();
    }
}
