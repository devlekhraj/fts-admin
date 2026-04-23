<?php

declare(strict_types=1);

namespace App\Domains\Blog\Models;

use App\Domains\BlogCategory\Models\BlogCategory;
use App\Domains\File\Models\File;
use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Blog extends BaseModel
{
    protected $table = 'blogs';

    protected $fillable = [
        'title',
        'slug',
        'author',
        'status',
        'short_desc',
        'content',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'category_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'file_usages', 'usage_id', 'file_id')
            ->wherePivot('usage_type', 'blogs')
            ->withPivot(['id', 'usage_type', 'usage_id', 'title', 'alt_text', 'meta'])
            ->withTimestamps();
    }

    public function defaultFile(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'file_usages', 'usage_id', 'file_id')
            ->wherePivot('usage_type', 'blogs')
            ->where(static function ($query) {
                $query->whereRaw("JSON_EXTRACT(file_usages.meta, '$.is_default') = true")
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(file_usages.meta, '$.is_default'))) = 'true'");
            })
            ->withPivot(['id', 'usage_type', 'usage_id', 'title', 'alt_text', 'meta'])
            ->orderByPivot('id', 'asc');
    }
}
