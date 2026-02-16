<?php

declare(strict_types=1);

namespace App\Foundation\Domain\Emi\Repositories;

use App\Foundation\Domain\Emi\Entities\EmiRequest;

interface EmiRequestRepository
{
    public function save(EmiRequest $entity): void;

    public function findById(string $id): ?EmiRequest;

    public function paginate(int $perPage, int $page = 1): array;

    public function deleteById(string $id): void;
}
