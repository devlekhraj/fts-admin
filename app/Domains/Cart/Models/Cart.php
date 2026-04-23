<?php

declare(strict_types=1);

namespace App\Domains\Cart\Models;

use App\Domains\User\Models\User;
use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Cart extends BaseModel
{
    protected $table = 'carts';

     protected $fillable = ['user_id', 'ip_address', 'is_processed', 'discount_coupon'];
     
    protected $casts = [
        'is_processed' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class, 'cart_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
