<?php

declare(strict_types=1);

namespace App\Domains\File\Models;

use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class File extends BaseModel
{
    protected $table = 'files';

    protected $fillable = [
        'key',
        'file_name',
        'file_path',
        'extension',
        'seq_no',
        'mime_type',
        'file_size',
        'height',
        'width',
        'disk',
        'content_hash',
        'meta',
    ];

    protected $appends = ['url'];
    protected $casts = [
        'meta' => 'array',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(FileUsage::class, 'file_id');
    }

    public function getUrlAttribute(): ?string
    {
        $path = $this->file_path;
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $path = trim($path);
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $baseUrl = trim((string) config('filesystems.disks.cdn.url', ''), '/');
        $relativePath = ltrim($path, '/');

        if ($baseUrl === '') {
            return '/' . $relativePath;
        }

        return $baseUrl . '/' . $relativePath;
    }
}
