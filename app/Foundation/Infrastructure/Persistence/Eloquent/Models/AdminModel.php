<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminModel extends Authenticatable implements JWTSubject
{
    protected $table = 'admins';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'role_id',
    ];

    protected $hidden = ['password'];

    public function role(): BelongsTo
    {
        return $this->belongsTo(RoleModel::class, 'role_id');
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
