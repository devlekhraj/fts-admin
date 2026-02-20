<?php

declare(strict_types=1);

namespace App\Foundation\Domain\AdminIdentity\Entities;


use App\Foundation\Domain\Rbac\ValueObjects\RoleId;
use App\Foundation\Shared\Domain\ValueObjects\Email;
use App\Foundation\Shared\Domain\ValueObjects\PasswordHash;
use App\Foundation\Shared\Domain\ValueObjects\Username;

class Admin
{
    public function __construct(
        public readonly string $name,
        public readonly Email $email,
        public readonly Username $username,
        public readonly PasswordHash $password,
        public readonly RoleId $roleId,
    ) {}
}
