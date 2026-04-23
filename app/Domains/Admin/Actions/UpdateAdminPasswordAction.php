<?php

declare(strict_types=1);

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\DTOs\UpdateAdminPasswordData;
use App\Domains\Admin\Events\AdminPasswordUpdated;
use App\Domains\Admin\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class UpdateAdminPasswordAction
{
    public function execute(Admin $admin, UpdateAdminPasswordData $data): void
    {
        $password = trim($data->password);
        if ($password === '' || mb_strlen($password) < 8) {
            throw ValidationException::withMessages(['password' => ['Password must be at least 8 characters.']]);
        }

        $admin->password = Hash::make($password);
        $admin->save();

        event(new AdminPasswordUpdated($admin->refresh()));
    }
}
