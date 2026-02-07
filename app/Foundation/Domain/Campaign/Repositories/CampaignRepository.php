<?php

declare(strict_types=1);

namespace App\Foundation\Domain\Campaign\Repositories;

use App\Foundation\Domain\Campaign\Entities\Campaign;

interface CampaignRepository
{
    public function save(Campaign $entity): void;

    public function findById(string $id): ?Campaign;

    public function paginate(int $perPage, int $page = 1): array;

    public function deleteById(string $id): void;
}
