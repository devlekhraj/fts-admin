<?php

namespace App\Domains\Coupon\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponDiscount extends Model
{
    use HasFactory;

    protected $table = 'coupon_discounts';

    protected $fillable = [
        'title',
        'code',
        'start_date',
        'end_date',
        'discount_type',
        'discount_value',
        'minimum_value',
        'usage_per_user',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'discount_value' => 'decimal:2',
        'minimum_value' => 'decimal:2',
        'usage_per_user' => 'integer',
    ];
}
