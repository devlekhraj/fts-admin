<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @method static Builder|FileUsageModel query()
 * @method static Builder|FileUsageModel where($column, $operator = null, $value = null, $boolean = 'and')
 * @mixin Builder
 */
class FileUsageModel extends Pivot
{
    protected $table = 'file_usages';

    protected $casts = [
        'meta' => 'array',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(FileModel::class, 'file_id');
    }

    public function usable()
    {
        return $this->morphTo();
    }
}
