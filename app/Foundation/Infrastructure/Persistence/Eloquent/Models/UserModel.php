<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserModel extends BaseModel
{
    protected $table = 'users';

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $hidden = ['password'];

    public function emiRequests(): HasMany
    {
        return $this->hasMany(EmiRequestModel::class, 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(OrderModel::class, 'user_id');
    }

    public function shippingAddress(): HasMany
    {
        return $this->hasMany(ShippingAddressModel::class, 'user_id');
    }
}
