<?php

declare(strict_types=1);

namespace App\Domains\Exchange\Models;

use Illuminate\Database\Eloquent\Model;

/** Trade-in price list: one row per model and storage size. */
final class ExchangeValuation extends Model
{
    public const CONFIDENCE = ['verified', 'estimate', 'low'];

    protected $table = 'exchange_valuations';

    protected $fillable = [
        'brand',
        'model',
        'storage_gb',
        'base_value',
        'admin_override',
        'confidence',
        'evidence_note',
        'effective_date',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'storage_gb' => 'integer',
        'base_value' => 'integer',
        'admin_override' => 'integer',
        'effective_date' => 'date:Y-m-d',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getFinalValueAttribute(): int
    {
        return $this->admin_override ?? $this->base_value;
    }
}
