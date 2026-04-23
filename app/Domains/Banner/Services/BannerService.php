<?php

declare(strict_types=1);

namespace App\Domains\Banner\Services;

use App\Domains\Banner\Actions\BannerCreateAction;
use App\Domains\Banner\Actions\BannerDeleteAction;
use App\Domains\Banner\Actions\BannerListAction;
use App\Domains\Banner\Actions\BannerUpdateAction;
use App\Domains\Banner\DTOs\BannerCreateData;
use App\Domains\Banner\DTOs\BannerUpdateData;
use App\Domains\Banner\Models\Banner;

final class BannerService
{
    public function __construct(
        private readonly BannerCreateAction $bannerCreateAction,
        private readonly BannerUpdateAction $bannerUpdateAction,
        private readonly BannerDeleteAction $bannerDeleteAction,
        private readonly BannerListAction $bannerListAction,
    ) {}

    public function list(?string $search, int $perPageParam): array
    {
        return $this->bannerListAction->execute($search, $perPageParam);
    }

    public function create(BannerCreateData $data): Banner
    {
        return $this->bannerCreateAction->execute($data);
    }

    public function update(string $id, BannerUpdateData $data): Banner
    {
        $banner = Banner::query()->findOrFail($id);

        return $this->bannerUpdateAction->execute($banner, $data);
    }

    public function delete(string $id): void
    {
        $banner = Banner::query()->findOrFail($id);

        $this->bannerDeleteAction->execute($banner);
    }
}
