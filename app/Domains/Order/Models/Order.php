<?php

declare(strict_types=1);

namespace App\Domains\Order\Models;

use App\Domains\PaymentMethod\Models\PaymentMethod;
use App\Domains\Shared\Models\ActivityLog;
use App\Domains\User\Models\ShippingAddress;
use App\Domains\User\Models\User;
use App\Support\Eloquent\BaseModel;
use App\Traits\HasActivityLogs;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Order extends BaseModel
{
    use HasActivityLogs;
    protected $table = 'orders';

    protected $fillable = [
        'order_no',
        'user_id',
        'cart_id',
        'shipping_address_id',
        'invoice_number',
        'status',
        'discount_coupon',
        'shipping_cost',
        'cancel_reason',
        'discounts_total',
        'order_total',
        'total',
        'payment_type',
        'created_at',
        'updated_at'
    ];
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
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
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
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(ShippingAddress::class, 'shipping_address_id');
    }

    public function receipent(): HasOne
    {
        return $this->hasOne(OrderReceipent::class, 'order_id', 'id');
    }

    public function activities()
    {
        return $this->morphMany(ActivityLog::class, 'entity')
            ->latest('created_at');
    }
}
