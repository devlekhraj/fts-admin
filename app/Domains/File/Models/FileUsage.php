<?php

declare(strict_types=1);

namespace App\Domains\File\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @method static Builder|FileUsage query()
 * @method static Builder|FileUsage where($column, $operator = null, $value = null, $boolean = 'and')
 * @mixin Builder
 */
final class FileUsage extends Pivot
{
    protected $table = 'file_usages';

    protected $fillable = [
        'file_id',
        'usage_type',
        'usage_id',
        'title',
        'alt_text',
        'meta',
    ];
    protected $casts = [
        'meta' => 'array',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function usable()
    {
        return $this->morphTo();
    }
}
