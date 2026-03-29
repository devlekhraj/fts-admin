<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class CartModel extends BaseModel
{
    protected $table = 'carts';

     protected $fillable = ['user_id', 'ip_address', 'is_processed', 'discount_coupon'];
     
    protected $casts = [
        'is_processed' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(CartItemModel::class,'cart_id');
    }

    public function user()
    {
        return $this->belongsTo(UserModel::class,'user_id');
    }

    public static function getCart($authCheck = true)
    {
        if ($authCheck && auth('sanctum')->check()) {
            return self::firstOrCreate([
                'user_id' => auth('sanctum')->id(),
                'is_processed' => 0,
            ]);
        }

        return self::firstOrCreate([
            'ip_address' => request()->ip(),
            'is_processed' => 0,
        ]);
    }
}
