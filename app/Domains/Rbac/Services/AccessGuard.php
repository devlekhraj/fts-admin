<?php

declare(strict_types=1);

namespace App\Domains\Rbac\Services;

use App\Domains\Admin\Models\Admin;
use App\Domains\Admin\Models\Role;

/**
 * The rules that keep team and role management safe, so nobody can lock the team out or give themselves more access:
 *  - you can only give a role (or a permission) you could hold yourself; the Administrator role only an Administrator gives;
 *  - you cannot manage an admin whose role has more access than yours;
 *  - you cannot change your own role or delete your own account;
 *  - the last Administrator can never be demoted or deleted;
 *  - the Administrator role cannot be edited or deleted, and neither can the role you are assigned to.
 * Every refusal is a plain-language message the screens show as it is.
 */
final class AccessGuard
{
    public function currentAdmin(): ?Admin
    {
        $admin = auth('admin_api')->user();

        return $admin instanceof Admin ? $admin->loadMissing('role') : null;
    }

    /** True when the actor holds everything the role holds (an Administrator holds everything, and no one else can hold the Administrator role). */
    public function outranks(Admin $actor, ?Role $role): bool
    {
        $actor->loadMissing('role');
        if ($actor->isSuper()) {
            return true;
        }
        if ($role === null) {
            return true;
        }
        if ($role->is_super) {
            return false;
        }

        return array_diff($role->permissionList(), $actor->permissionList()) === [];
    }

    public function assertCanAssign(Admin $actor, Role $role): void
    {
        if ($this->outranks($actor, $role)) {
            return;
        }

        abort(403, $role->is_super
            ? 'Only an Administrator can give the Administrator role.'
            : 'You can only give a role that has permissions you have yourself.');
    }

    /** Editing someone else's account (name, email, password). Your own account is always yours to edit. */
    public function assertCanManage(Admin $actor, Admin $target): void
    {
        if ($actor->id === $target->id) {
            return;
        }

        $target->loadMissing('role');
        if (! $this->outranks($actor, $target->role)) {
            abort(403, 'You cannot manage an admin whose role has more access than yours.');
        }
    }

    public function assertCanChangeRole(Admin $actor, Admin $target, Role $newRole): void
    {
        if ($actor->id === $target->id) {
            abort(403, 'You cannot change your own role. Ask another administrator to do it.');
        }

        $this->assertCanManage($actor, $target);
        $this->assertCanAssign($actor, $newRole);

        $target->loadMissing('role');
        if ($target->isSuper() && ! $newRole->is_super && $this->superAdminCount() <= 1) {
            abort(422, 'At least one Administrator must remain. Make another admin an Administrator first.');
        }
    }

    public function assertCanDelete(Admin $actor, Admin $target): void
    {
        if ($actor->id === $target->id) {
            abort(403, 'You cannot delete your own account.');
        }

        $this->assertCanManage($actor, $target);

        $target->loadMissing('role');
        if ($target->isSuper() && $this->superAdminCount() <= 1) {
            abort(422, 'The last Administrator cannot be deleted.');
        }
    }

    public function assertRoleEditable(Admin $actor, Role $role): void
    {
        if ($role->is_super) {
            abort(403, 'The Administrator role cannot be changed or deleted.');
        }
        if ($actor->role_id !== null && (int) $actor->role_id === (int) $role->id) {
            abort(403, 'You cannot change or delete the role you are assigned to. Ask another administrator.');
        }
        if (! $this->outranks($actor, $role)) {
            abort(403, 'You cannot change a role that has more access than yours.');
        }
    }

    /** @param string[] $permissions */
    public function assertCanGrant(Admin $actor, array $permissions): void
    {
        $actor->loadMissing('role');
        if ($actor->isSuper()) {
            return;
        }

        $beyond = array_values(array_diff($permissions, $actor->permissionList()));
        if ($beyond !== []) {
            abort(403, 'You can only give permissions you have yourself. Not allowed for you: ' . implode(', ', array_slice($beyond, 0, 5)) . (count($beyond) > 5 ? ', ...' : '') . '.');
        }
    }

    public function superAdminCount(): int
    {
        return Admin::query()->whereHas('role', fn ($query) => $query->where('is_super', true))->count();
    }
}
