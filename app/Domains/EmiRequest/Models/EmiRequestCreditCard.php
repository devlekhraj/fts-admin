<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Models;

use App\Domains\EmiBank\Models\EmiBank;
use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class EmiRequestCreditCard extends BaseModel
{
    use SoftDeletes;

    protected $table = 'emi_request_credit_cards';

    protected $fillable = [
        'emi_request_id',
        'card_number',
        'card_holder',
        'card_provider',
        'expiry_date',
        'credit_limit',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function emiRequest(): BelongsTo
    {
        return $this->belongsTo(EmiRequest::class, 'emi_request_id');
    }

    public function cardProvider(): BelongsTo
    {
        return $this->belongsTo(EmiBank::class, 'card_provider');
    }
}

