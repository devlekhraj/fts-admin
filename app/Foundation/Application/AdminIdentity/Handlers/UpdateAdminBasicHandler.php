<?php

declare(strict_types=1);

namespace App\Foundation\Application\AdminIdentity\Handlers;

use App\Foundation\Application\AdminIdentity\Commands\UpdateAdminBasicCommand;
use App\Foundation\Domain\Rbac\ValueObjects\RoleId;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\AdminModel;
use App\Foundation\Shared\Application\DTO\ActionResult;
use App\Foundation\Shared\Domain\Exceptions\FieldValidationException;
use App\Foundation\Shared\Domain\ValueObjects\Username;

final class UpdateAdminBasicHandler
{
    public function handle(UpdateAdminBasicCommand $cmd): ActionResult
    {
        $username = new Username($cmd->username);
        $roleId = new RoleId($cmd->roleId);

        $admin = AdminModel::query()->find($cmd->id);
        if (!$admin) {
            throw new FieldValidationException('id', 'Admin not found.');
        }

        $usernameExists = AdminModel::query()
            ->where('username', $username->value())
            ->where('id', '!=', $cmd->id)
            ->exists();

        if ($usernameExists) {
            throw new FieldValidationException('username', 'Username already exists.');
        }

        $admin->fill([
            'name' => $cmd->name,
            'username' => $username->value(),
            'role_id' => $roleId->value(),
        ]);
        $admin->save();

        return new ActionResult(true, 'Admin updated successfully.');
    }
}
