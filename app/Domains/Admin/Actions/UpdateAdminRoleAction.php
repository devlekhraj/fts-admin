<?php

declare(strict_types=1);

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\DTOs\UpdateAdminRoleData;
use App\Domains\Admin\Models\Admin;
use App\Domains\Admin\Mail\AdminBasicUpdatedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class UpdateAdminRoleAction
{
    public function execute(Admin $admin, UpdateAdminRoleData $data): Admin
    {
        $admin->role_id = $data->roleId;
        $admin->save();
        $admin->loadMissing('role');

        $this->sendBasicUpdatedMail($admin);

        return $admin->refresh();
    }

    private function sendBasicUpdatedMail(Admin $admin): void
    {
        $email = is_string($admin->email) ? trim($admin->email) : '';
        if ($email === '') {
            return;
        }

        $roleName = $admin->role->name ?? null;
        $timestamp = now()->timezone(config('app.timezone'))->format('Y-m-d H:i:s');

        try {
            Mail::to($email)->send(new AdminBasicUpdatedMail(
                name: (string) ($admin->name ?? $admin->username ?? 'Admin'),
                username: (string) ($admin->username ?? ''),
                email: $email,
                role: $roleName,
                updatedAt: $timestamp,
                timezone: config('app.timezone'),
            ));
        } catch (\Throwable $mailException) {
            Log::error('Failed to send admin basic updated email.', [
                'admin_id' => $admin->id,
                'email' => $email,
                'error' => $mailException->getMessage(),
            ]);
        }
    }
}
