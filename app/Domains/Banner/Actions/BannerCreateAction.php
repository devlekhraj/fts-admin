<?php

declare(strict_types=1);

namespace App\Domains\Banner\Actions;

use App\Domains\Banner\DTOs\BannerCreateData;
use App\Domains\Banner\Models\Banner;

final class BannerCreateAction
{
    public function execute(BannerCreateData $data): Banner
    {
        return Banner::create([
            'name' => $data->name,
            'slug' => $data->slug,
            'status' => false,
        ]);
    }
}
