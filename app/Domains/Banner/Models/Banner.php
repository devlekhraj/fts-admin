<?php

declare(strict_types=1);

namespace App\Domains\Banner\Models;

use App\Domains\File\Models\File;
use App\Domains\File\Models\FileUsage;
use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Banner extends BaseModel
{
    use SoftDeletes;

    protected $table = 'banners';

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function bannerImages(): HasMany
    {
        return $this->hasMany(BannerImage::class, 'banner_id');
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'file_usages', 'usage_id', 'file_id')
            ->using(FileUsage::class)
            ->whereIn('file_usages.usage_type', ['banners'])
            ->withPivot(['id', 'usage_type', 'usage_id', 'title', 'alt_text', 'meta'])
            ->withTimestamps()
            ->orderByPivot('id', 'desc');
    }

    public function defaultFile(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'file_usages', 'usage_id', 'file_id')
            ->using(FileUsage::class)
            ->whereIn('file_usages.usage_type', ['banners'])
            ->withPivot(['id', 'usage_type', 'usage_id', 'title', 'alt_text', 'meta'])
            ->orderByPivot('id', 'asc');
    }
}
