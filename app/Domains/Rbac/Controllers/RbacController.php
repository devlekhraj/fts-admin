<?php

declare(strict_types=1);

namespace App\Domains\Rbac\Controllers;

use App\Domains\Admin\Models\Admin;
use App\Domains\Admin\Models\Role;
use App\Domains\Rbac\Services\AccessGuard;
use App\Domains\Rbac\Support\PermissionCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

final class RbacController extends Controller
{
    public function __construct(private readonly AccessGuard $guard) {}

    /** Every role, with how many admins hold it and what the signed-in admin may do with it. */
    public function indexRoles(): JsonResponse
    {
        $actor = $this->actor();
        $counts = Admin::query()->selectRaw('role_id, count(*) as c')->groupBy('role_id')->pluck('c', 'role_id');

        $roles = Role::query()->orderBy('id')->get()->map(fn (Role $role) => $this->present($role, $actor, (int) ($counts[$role->id] ?? 0)));

        return response()->json(['data' => $roles->values()]);
    }

    /** The permissions that can be given, grouped for the role form. */
    public function indexPermissions(): JsonResponse
    {
        $actor = $this->actor();
        $mine = $actor->isSuper() ? null : $actor->permissionList();

        $groups = [];
        foreach (PermissionCatalog::groups() as $key => $group) {
            $groups[] = [
                'key' => $key,
                'label' => $group['label'],
                'permissions' => collect($group['permissions'])->map(fn (string $label, string $permission) => [
                    'key' => $permission,
                    'label' => $label,
                    // false when the signed-in admin does not hold it and so cannot give it
                    'grantable' => $mine === null || in_array($permission, $mine, true),
                ])->values()->all(),
            ];
        }

        return response()->json(['data' => $groups]);
    }

    public function store(Request $request): JsonResponse
    {
        $actor = $this->actor();
        $validated = $this->validated($request, null);
        $this->guard->assertCanGrant($actor, $validated['permissions']);

        $role = Role::query()->create(['name' => $validated['name'], 'permissions' => $validated['permissions']]);
        $this->audit('created', $actor, $role, ['permissions' => $role->permissionList()]);

        return response()->json([
            'success' => true,
            'message' => 'Role created.',
            'data' => $this->present($role->refresh(), $actor, 0),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $actor = $this->actor();

        $role = DB::transaction(function () use ($request, $id, $actor) {
            $role = Role::query()->lockForUpdate()->findOrFail($id);
            $this->guard->assertRoleEditable($actor, $role);

            $validated = $this->validated($request, $role);
            $this->guard->assertCanGrant($actor, $validated['permissions']);

            $before = ['name' => $role->name, 'permissions' => $role->permissionList()];
            $role->forceFill(['name' => $validated['name'], 'permissions' => $validated['permissions']])->save();
            $this->audit('updated', $actor, $role, [
                'renamed_from' => $before['name'] !== $validated['name'] ? $before['name'] : null,
                'permissions_added' => array_values(array_diff($validated['permissions'], $before['permissions'])),
                'permissions_removed' => array_values(array_diff($before['permissions'], $validated['permissions'])),
            ]);

            return $role->refresh();
        });

        return response()->json([
            'success' => true,
            'message' => 'Role updated. It applies to the people who hold it from their next action.',
            'data' => $this->present($role, $actor, Admin::query()->where('role_id', $role->id)->count()),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $actor = $this->actor();

        DB::transaction(function () use ($id, $actor) {
            $role = Role::query()->lockForUpdate()->findOrFail($id);
            $this->guard->assertRoleEditable($actor, $role);

            $inUse = Admin::query()->where('role_id', $role->id)->count();
            if ($inUse > 0) {
                abort(422, $inUse === 1
                    ? 'One admin still has this role. Give them another role first.'
                    : "{$inUse} admins still have this role. Give them another role first.");
            }

            $this->audit('deleted', $actor, $role, ['permissions' => $role->permissionList()]);
            $role->delete();
        });

        return response()->json(['success' => true, 'message' => 'Role deleted.', 'data' => null]);
    }

    /** @return array{name: string, permissions: string[]} */
    private function validated(Request $request, ?Role $existing): array
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'min:2', 'max:50',
                Rule::unique('roles', 'name')->ignore($existing?->id),
            ],
            'permissions' => ['present', 'array'],
            'permissions.*' => ['string', Rule::in(PermissionCatalog::all())],
        ], [
            'name.unique' => 'There is already a role with this name.',
            'permissions.*.in' => 'One of the permissions is not valid.',
        ]);

        return [
            'name' => trim($validated['name']),
            'permissions' => array_values(array_unique($validated['permissions'])),
        ];
    }

    /** Who changed which role, and how, so a change of access can always be traced. */
    private function audit(string $action, Admin $actor, Role $role, array $details): void
    {
        Log::notice("Role {$action}", array_merge([
            'role_id' => $role->id,
            'role' => $role->name,
            'by_admin_id' => $actor->id,
            'by_admin' => $actor->email,
        ], array_filter($details, fn ($value) => $value !== null && $value !== [])));
    }

    private function actor(): Admin
    {
        $actor = $this->guard->currentAdmin();
        abort_unless($actor instanceof Admin, 401, 'Unauthenticated');

        return $actor;
    }

    /** @return array<string, mixed> */
    private function present(Role $role, Admin $actor, int $adminsCount): array
    {
        $own = $actor->role_id !== null && (int) $actor->role_id === (int) $role->id;
        $outranks = $this->guard->outranks($actor, $role);
        $editable = ! $role->is_super && ! $own && $outranks;

        return [
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissionList(),
            'is_super' => (bool) $role->is_super,
            'admins_count' => $adminsCount,
            'is_own' => $own,
            // what the signed-in admin may do with this role (the server checks again on every change)
            'assignable' => $outranks,
            'editable' => $editable,
            'deletable' => $editable && $adminsCount === 0,
        ];
    }
}
