<?php

declare(strict_types=1);

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\DTOs\UpdateAdminProfileData;
use App\Domains\Admin\Events\AdminBasicUpdated;
use App\Domains\Admin\Models\Admin;

final class UpdateAdminProfileAction
{
    public function execute(Admin $admin, UpdateAdminProfileData $data): Admin
    {
        $admin->name = $data->name;
        $admin->username = $data->username;

        if (is_string($data->avatar) && trim($data->avatar) !== '') {
            $admin->avatar = $data->avatar;
        }

        $shouldNotify = $admin->isDirty(['name', 'username', 'avatar']);

        $admin->save();
        $admin = $admin->refresh();

        if ($shouldNotify) {
            event(new AdminBasicUpdated($admin));
        }

        return $admin;
    }
}
