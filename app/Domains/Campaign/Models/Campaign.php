<?php

declare(strict_types=1);

namespace App\Domains\Campaign\Models;

use App\Domains\File\Models\File;
use App\Domains\File\Models\FileUsage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Campaign extends Model
{
    use SoftDeletes;

    protected $table = 'discount_campaigns';

    protected $fillable = [
        'title',
        'slug',
        'start_date',
        'end_date',
        'is_published',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_published' => 'boolean',
    ];

    protected $appends = ['is_active'];

    public function getIsActiveAttribute(): bool
    {
        $currentTime = Carbon::now();

        return $this->start_date < $currentTime && $this->end_date > $currentTime;
    }

    public function products(): HasMany
    {
        return $this->hasMany(CampaignProduct::class, 'campaign_id', 'id');
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'file_usages', 'usage_id', 'file_id')
            ->using(FileUsage::class)
            ->wherePivot('usage_type', 'campaigns')
            ->withPivot(['id', 'usage_type', 'usage_id', 'title', 'alt_text', 'meta'])
            ->withTimestamps()
            ->orderByPivot('id', 'desc');
    }

    public function defaultFile(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'file_usages', 'usage_id', 'file_id')
            ->using(FileUsage::class)
            ->wherePivot('usage_type', 'campaigns')
            ->wherePivot('meta->is_default', true)
            ->withPivot(['id', 'usage_type', 'usage_id', 'title', 'alt_text', 'meta'])
            ->limit(1);
    }
}
