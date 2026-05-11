<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Models;

use App\Domains\EmiBank\Models\EmiBank;
use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class EmiRequestBank extends BaseModel
{
    use SoftDeletes;

    protected $table = 'emi_request_banks';

    protected $fillable = [
        'emi_request_id',
        'bank_id',
        'account_number',
        'branch',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function emiRequest(): BelongsTo
    {
        return $this->belongsTo(EmiRequest::class, 'emi_request_id');
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(EmiBank::class, 'bank_id');
    }
}

