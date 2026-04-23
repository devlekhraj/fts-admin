<?php

declare(strict_types=1);

namespace App\Domains\BlogCategory\Models;

use App\Domains\Blog\Models\Blog;
use App\Domains\File\Models\File;
use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class BlogCategory extends BaseModel
{
    use SoftDeletes;
    protected $table = 'blog_categories';

    protected $fillable = [
        'title',
        'slug',
        'short_desc',
        'content',
        'status',
        'meta_title',
        'meta_keywords',
        'meta_description',
    ];


    protected $casts = [
        'status' => 'boolean',
    ];

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'category_id');
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'file_usages', 'usage_id', 'file_id')
            ->wherePivot('usage_type', 'blog_categories')
            ->withPivot(['id', 'usage_type', 'usage_id', 'title', 'alt_text', 'meta'])
            ->withTimestamps();
    }

    public function defaultFile(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'file_usages', 'usage_id', 'file_id')
            ->wherePivot('usage_type', 'blog_categories')
            ->where(static function ($query) {
                $query->whereRaw("JSON_EXTRACT(file_usages.meta, '$.is_default') = true")
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(file_usages.meta, '$.is_default'))) = 'true'");
            })
            ->withPivot(['id', 'usage_type', 'usage_id', 'title', 'alt_text', 'meta'])
            ->orderByPivot('id', 'asc');
    }
}
