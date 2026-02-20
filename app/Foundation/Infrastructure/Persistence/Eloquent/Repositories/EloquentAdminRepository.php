<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Repositories;

use App\Foundation\Domain\AdminIdentity\Entities\Admin;

use App\Foundation\Domain\AdminIdentity\Repositories\AdminRepository;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\AdminModel;
use App\Foundation\Shared\Domain\ValueObjects\Email;
use App\Foundation\Shared\Domain\ValueObjects\PasswordHash;
use App\Foundation\Shared\Domain\ValueObjects\Username;
use App\Foundation\Domain\Rbac\ValueObjects\RoleId;

class EloquentAdminRepository implements AdminRepository
{
    public function save(Admin $entity): void
    {
        $model = new AdminModel();
        $model->fill([
            'name' => $entity->name,
            'email' => $entity->email->value(),
            'password' => $entity->password->value(),
            'username' => $entity->username->value(),
            'role_id' => $entity->roleId->value(),
        ]);
        $model->save();
    }

    public function findById(string $id): ?Admin
    {
        $model = AdminModel::query()->find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findByEmail(Email $email): ?Admin
    {
        $model = AdminModel::query()->where('email', $email->value())->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function findByUsername(Username $username): ?Admin
    {
        $model = AdminModel::query()->where('username', $username->value())->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function paginate(int $perPage, int $page = 1): array
    {
        $paginator = AdminModel::query()->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->getCollection()
                ->map(fn (AdminModel $model) => $this->toEntity($model))
                ->all(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
        ];
    }

    public function deleteById(string $id): void
    {
        AdminModel::query()->whereKey($id)->delete();
    }

    private function toEntity(AdminModel $model): Admin
    {
        return new Admin(
            username: new Username((string) $model->username),
            name: (string) $model->name,
            email: new Email((string) $model->email),
            password: new PasswordHash((string) $model->password),
            roleId: new RoleId((int) $model->role_id),
        );
    }
}
