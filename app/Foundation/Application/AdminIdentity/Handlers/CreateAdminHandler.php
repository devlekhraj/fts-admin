<?php

declare(strict_types=1);

namespace App\Foundation\Application\AdminIdentity\Handlers;



use App\Foundation\Application\AdminIdentity\Commands\CreateAdminCommand;
use App\Foundation\Application\AdminIdentity\DTO\CreateAdminResult;
use App\Foundation\Domain\AdminIdentity\Entities\Admin;


use App\Foundation\Domain\Rbac\ValueObjects\RoleId;
use App\Foundation\Infrastructure\Persistence\Eloquent\Repositories\EloquentAdminRepository;
use App\Foundation\Shared\Application\Contracts\PasswordHasher;
use App\Foundation\Shared\Domain\Exceptions\FieldValidationException;
use App\Foundation\Shared\Domain\ValueObjects\Email;
use App\Foundation\Shared\Domain\ValueObjects\PasswordHash;
use App\Foundation\Shared\Domain\ValueObjects\Username;

final class CreateAdminHandler
{
    public function __construct(
        private readonly EloquentAdminRepository $adminRepository,
        private readonly PasswordHasher $hasher,
    ) {}

    public function handle(CreateAdminCommand $cmd): CreateAdminResult
    {
        $email = new Email($cmd->email);
        $username = new Username($cmd->username);

        if ($this->adminRepository->findByEmail($email)) {
            throw new FieldValidationException('email', 'Email already exists.');
        }

        if ($this->adminRepository->findByUsername($username)) {
            throw new FieldValidationException('username', 'Username already exists.');
        }

        $hash = $this->hasher->hash($cmd->password);

        $admin = new Admin(
            username: $username,
            name: $cmd->name,
            email: $email,
            password: new PasswordHash($hash),
            roleId: new RoleId($cmd->roleId),
        );

        $this->adminRepository->save($admin);

        return new CreateAdminResult(true, 'Admin created successfully.');
    }
}
