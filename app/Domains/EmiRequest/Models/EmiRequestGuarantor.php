<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Models;

use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class EmiRequestGuarantor extends BaseModel
{
    use SoftDeletes;

    protected $table = 'emi_request_guarantors';

    protected $fillable = [
        'emi_request_id',
        'name',
        'email',
        'phone',
        'gender',
        'marriage_status',
        'citizenship_number',
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
}

