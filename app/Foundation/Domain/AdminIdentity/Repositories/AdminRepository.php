<?php

declare(strict_types=1);

namespace App\Foundation\Domain\AdminIdentity\Repositories;


use App\Foundation\Domain\AdminIdentity\Entities\Admin;
use App\Foundation\Shared\Domain\ValueObjects\Email;
use App\Foundation\Shared\Domain\ValueObjects\Username;



interface AdminRepository
{
    public function save(Admin $entity): void;

    public function findById(string $id): ?Admin;

    public function findByEmail(Email $email): ?Admin;

    public function findByUsername(Username $username): ?Admin;

    public function paginate(int $perPage, int $page = 1): array;

    public function deleteById(string $id): void;
}
