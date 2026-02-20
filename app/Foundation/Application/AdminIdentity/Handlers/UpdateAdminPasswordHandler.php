<?php

declare(strict_types=1);

namespace App\Foundation\Application\AdminIdentity\Handlers;

use App\Foundation\Application\AdminIdentity\Commands\UpdateAdminPasswordCommand;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\AdminModel;
use App\Foundation\Shared\Application\Contracts\PasswordHasher;
use App\Foundation\Shared\Application\DTO\ActionResult;
use App\Foundation\Shared\Domain\Exceptions\FieldValidationException;

final class UpdateAdminPasswordHandler
{
    public function __construct(
        private readonly PasswordHasher $hasher,
    ) {}

    public function handle(UpdateAdminPasswordCommand $cmd): ActionResult
    {
        $password = trim($cmd->password);
        if ($password === '' || mb_strlen($password) < 8) {
            throw new FieldValidationException('password', 'Password must be at least 8 characters.');
        }

        $admin = AdminModel::query()->find($cmd->id);
        if (!$admin) {
            throw new FieldValidationException('id', 'Admin not found.');
        }

        $admin->password = $this->hasher->hash($password);
        $admin->save();

        return new ActionResult(true, 'Password updated successfully.');
    }
}
