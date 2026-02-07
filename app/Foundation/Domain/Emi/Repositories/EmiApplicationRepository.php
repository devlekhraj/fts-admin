<?php

declare(strict_types=1);

namespace App\Foundation\Domain\Emi\Repositories;

use App\Foundation\Domain\Emi\Entities\EmiApplication;

interface EmiApplicationRepository
{
    public function save(EmiApplication $entity): void;

    public function findById(string $id): ?EmiApplication;

    public function paginate(int $perPage, int $page = 1): array;

    public function deleteById(string $id): void;
}
