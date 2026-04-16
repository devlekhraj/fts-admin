<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use App\Models\Concerns\HasActivityLogs;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderModel extends BaseModel
{
    use HasActivityLogs;
    protected $table = 'orders';

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discounts_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'order_total' => 'decimal:2',
        'total' => 'decimal:2',
        'meta' => 'array',
        'placed_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethodModel::class, 'payment_method_id');
    }

    const STATUS_DRAFT = 0;

    const STATUS_PLACED = 1;

    const STATUS_CONFIRMED = 2;

    const STATUS_DISPATCHED = 3;

    const STATUS_COMPLETED = 4;

    const STATUS_CANCELED = 5;

    protected $appends = ['order_status'];

    public function getOrderStatusAttribute(): string
    {
        return match ((int) $this->status) {
            self::STATUS_PLACED => 'Placed',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_DISPATCHED => 'Dispatched',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELED => 'Canceled',
            default => 'Draft',
        };
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItemModel::class, 'order_id');
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(ShippingAddressModel::class, 'shipping_address_id');
    }

    public function receipent()
    {
        return $this->hasOne(OrderReceipentModel::class, 'order_id', 'id');
    }

}
