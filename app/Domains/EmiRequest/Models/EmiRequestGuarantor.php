<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Models;

use App\Domains\File\Models\File;
use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'file_usages', 'usage_id', 'file_id')
            ->wherePivot('usage_type', 'emi_request_guarantors')
            ->withPivot(['title'])
            ->withTimestamps();
    }
    
}
