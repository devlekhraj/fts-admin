<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileUsageModel extends BaseModel
{
    protected $table = 'file_usages';

    public function file(): BelongsTo
    {
        return $this->belongsTo(FileModel::class, 'file_id');
    }
}
