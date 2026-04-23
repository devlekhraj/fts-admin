<?php

namespace App\Models;

use App\Domains\File\Models\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmiRequestGuarantor extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'gender',
        'marital_status',
        'citizenship_number',
    ];

     public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'file_usages', 'usage_id', 'file_id')
            ->wherePivot('usage_type', 'emi_request_guarantors')
            ->withPivot(['id', 'usage_type', 'usage_id', 'title', 'alt_text', 'meta'])
            ->withTimestamps()
            ->orderByPivot('id', 'desc');
    }
}
