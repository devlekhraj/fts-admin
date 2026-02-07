<?php

declare(strict_types=1);

namespace App\Foundation\Domain\Emi\Repositories;

use App\Foundation\Domain\Emi\Entities\EmiUser;

interface EmiUserRepository
{
    public function save(EmiUser $entity): void;

    public function findById(string $id): ?EmiUser;

    public function paginate(int $perPage, int $page = 1): array;

    public function deleteById(string $id): void;
}
