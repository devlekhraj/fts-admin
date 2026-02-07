<?php

declare(strict_types=1);

namespace App\Foundation\Domain\Blog\Repositories;

use App\Foundation\Domain\Blog\Entities\BlogPost;

interface BlogRepository
{
    public function save(BlogPost $entity): void;

    public function findById(string $id): ?BlogPost;

    public function paginate(int $perPage, int $page = 1): array;

    public function deleteById(string $id): void;
}
