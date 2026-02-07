<?php

declare(strict_types=1);

namespace App\Foundation\Domain\Banner\Repositories;

use App\Foundation\Domain\Banner\Entities\Banner;

interface BannerRepository
{
    public function save(Banner $entity): void;

    public function findById(string $id): ?Banner;

    public function paginate(int $perPage, int $page = 1): array;

    public function deleteById(string $id): void;
}
