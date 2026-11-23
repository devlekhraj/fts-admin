<?php

declare(strict_types=1);

namespace App\Domains\Admin\Models;

use Illuminate\Database\Eloquent\Model;

final class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'name', 'permissions', 'created_at', 'updated_at',
    ];

    // is_super is deliberately not fillable: only the migration (and a developer) can mark a role as the Administrator role.


    protected $casts = [
        'permissions' => 'array',
        'is_super' => 'boolean',
    ];

    /** @return string[] */
    public function permissionList(): array
    {
        $permissions = $this->permissions;

        return is_array($permissions) ? array_values(array_filter($permissions, 'is_string')) : [];
    }
}
