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

    protected $casts = [
        'permissions' => 'array',
    ];
}
