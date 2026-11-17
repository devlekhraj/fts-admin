<?php

declare(strict_types=1);

namespace App\Domains\Exchange\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Customer trade-in requests. The table is written by the customer API; the admin reviews and decides.
 * The customer's government ID is stored encrypted with the customer API's key, so it is never read here.
 */
final class ExchangeRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_CANCELLED,
        self::STATUS_COMPLETED,
    ];

    /** Which statuses an admin can move a request to from each status. */
    public const TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_UNDER_REVIEW, self::STATUS_APPROVED, self::STATUS_REJECTED],
        self::STATUS_UNDER_REVIEW => [self::STATUS_APPROVED, self::STATUS_REJECTED],
        self::STATUS_APPROVED => [self::STATUS_COMPLETED, self::STATUS_REJECTED],
    ];

    protected $table = 'exchange_requests';

    protected $fillable = ['status', 'admin_offer', 'admin_note', 'customer_note'];

    protected $casts = [
        'condition_answers' => 'array',
        'estimate_breakdown' => 'array',
        'pickup_info' => 'array',
        'customer_estimate' => 'float',
        'target_price' => 'float',
        'expected_amount' => 'float',
        'admin_offer' => 'float',
        'is_satisfied' => 'boolean',
    ];

    /** Columns safe to read: everything except the encrypted government ID. */
    public const READABLE = [
        'id', 'reference', 'user_id', 'status', 'customer_name', 'customer_phone', 'device_product_id', 'device_name',
        'serial_number', 'device_photo_path', 'condition_answers', 'customer_estimate', 'estimate_breakdown',
        'target_product_id', 'target_name', 'target_price', 'pickup_info', 'is_satisfied', 'expected_amount',
        'satisfaction_reason', 'admin_offer', 'admin_note', 'customer_note', 'created_at', 'updated_at',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(ExchangeRequestLog::class, 'exchange_request_id')->orderBy('id');
    }
}
