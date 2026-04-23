<?php

declare(strict_types=1);

namespace App\Domains\User\Models;

use App\Domains\EmiRequest\Models\EmiRequest;
use App\Domains\Order\Models\Order;
use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class User extends BaseModel
{
    protected $table = 'users';

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $hidden = ['password'];

    public function emiRequests(): HasMany
    {
        return $this->hasMany(EmiRequest::class, 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function shippingAddress(): HasMany
    {
        return $this->hasMany(ShippingAddress::class, 'user_id');
    }
}

