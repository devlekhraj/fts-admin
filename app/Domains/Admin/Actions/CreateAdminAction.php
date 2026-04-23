<?php

declare(strict_types=1);

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\DTOs\CreateAdminData;
use App\Domains\Admin\Events\AdminCreated;
use App\Domains\Admin\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class CreateAdminAction
{
    public function execute(CreateAdminData $data): Admin
    {
        $generatedPassword = Str::random(10);

        $createdBy = auth('admin_api')->user();
        if (! $createdBy instanceof Admin) {
            $createdBy = null;
        }

        $admin = new Admin();
        $admin->name = $data->name;
        $admin->email = trim($data->email);
        $admin->username = trim($data->username);
        $admin->role_id = $data->roleId;
        $admin->password = Hash::make($generatedPassword);
        $admin->save();
        $admin->load('role');

        
        event(new AdminCreated($admin, $generatedPassword, $createdBy));

        return $admin;
    }
}
