<?php

declare(strict_types=1);

namespace App\Domains\Admin\Services;

use App\Domains\Admin\Actions\CreateAdminAction;
use App\Domains\Admin\Actions\DeleteAdminAction;
use App\Domains\Admin\Actions\SendAdminEmailVerificationCodeAction;
use App\Domains\Admin\Actions\UpdateAdminEmailAction;
use App\Domains\Admin\Actions\UpdateAdminPasswordAction;
use App\Domains\Admin\Actions\UpdateAdminProfileAction;
use App\Domains\Admin\Actions\UpdateAdminRoleAction;
use App\Domains\Admin\DTOs\CreateAdminData;
use App\Domains\Admin\DTOs\UpdateAdminEmailData;
use App\Domains\Admin\DTOs\UpdateAdminPasswordData;
use App\Domains\Admin\DTOs\UpdateAdminProfileData;
use App\Domains\Admin\DTOs\UpdateAdminRoleData;
use App\Domains\Admin\Models\Admin;

final class AdminService
{
    public function __construct(
        private readonly CreateAdminAction $createAdminAction,
        private readonly UpdateAdminProfileAction $updateAdminProfileAction,
        private readonly UpdateAdminEmailAction $updateAdminEmailAction,
        private readonly UpdateAdminPasswordAction $updateAdminPasswordAction,
        private readonly UpdateAdminRoleAction $updateAdminRoleAction,
        private readonly DeleteAdminAction $deleteAdminAction,
        private readonly SendAdminEmailVerificationCodeAction $sendAdminEmailVerificationCodeAction,
    ) {}

    public function create(CreateAdminData $data): Admin
    {
        return $this->createAdminAction->execute($data);
    }

    public function updateProfile(Admin $admin, UpdateAdminProfileData $data): Admin
    {
        return $this->updateAdminProfileAction->execute($admin, $data);
    }

    public function sendEmailVerificationCode(Admin $admin, string $newEmail): void
    {
        $this->sendAdminEmailVerificationCodeAction->execute($admin, $newEmail);
    }

    public function updateEmail(Admin $admin, UpdateAdminEmailData $data): Admin
    {
        return $this->updateAdminEmailAction->execute($admin, $data);
    }

    public function updatePassword(Admin $admin, UpdateAdminPasswordData $data): void
    {
        $this->updateAdminPasswordAction->execute($admin, $data);
    }

    public function updateRole(Admin $admin, UpdateAdminRoleData $data): Admin
    {
        return $this->updateAdminRoleAction->execute($admin, $data);
    }

    public function delete(Admin $admin): void
    {
        $this->deleteAdminAction->execute($admin);
    }
}

