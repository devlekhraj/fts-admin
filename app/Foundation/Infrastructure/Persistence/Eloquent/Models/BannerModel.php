<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BannerModel extends BaseModel
{
    protected $table = 'banners';

    protected $casts = [
        'status' => 'boolean',
    ];

    public function bannerImages()
    {
        return $this->hasMany(BannerImageModel::class, 'banner_id');
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(FileModel::class, 'file_usages', 'usage_id', 'file_id')
            ->whereIn('file_usages.usage_type', ['banners', 'banner'])
            ->withPivot(['usage_type', 'usage_id', 'title', 'alt_text', 'meta'])
            ->withTimestamps();
    }

    public function defaultFile(): BelongsToMany
    {
        return $this->belongsToMany(FileModel::class, 'file_usages', 'usage_id', 'file_id')
            ->whereIn('file_usages.usage_type', ['banners', 'banner'])
            ->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(file_usages.meta, '$.collection_name'))) = 'default'")
            ->withPivot(['usage_type', 'usage_id', 'title', 'alt_text', 'meta'])
            ->orderByPivot('id', 'desc');
    }
}
