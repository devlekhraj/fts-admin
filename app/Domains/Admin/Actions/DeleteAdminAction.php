<?php

declare(strict_types=1);

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Admin;

final class DeleteAdminAction
{
    public function execute(Admin $admin): void
    {
        $admin->delete();
    }
}
