<?php

declare(strict_types=1);

namespace App\Domains\Exchange\Models;

use Illuminate\Database\Eloquent\Model;

/** Condition choices (display, battery, back panel) and their deduction in percent of the base value. */
final class ExchangeDamageRule extends Model
{
    protected $table = 'exchange_damage_rules';

    protected $fillable = ['category', 'label', 'deduction_percent', 'guidance', 'requires_manual_review', 'is_active', 'sort_order'];

    protected $casts = [
        'deduction_percent' => 'float',
        'requires_manual_review' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
