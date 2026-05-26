<?php

declare(strict_types=1);

namespace App\Domains\File\Models;

use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

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
        'is_public',
        'meta',
    ];

    protected $appends = ['url'];
    protected $casts = [
        'meta' => 'array',
        'is_public' => 'boolean',
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

        $relativePath = ltrim($path, '/');
        $disk =  config('filesystems.default');

        /** @var FilesystemAdapter $storage */
        $storage = Storage::disk($disk);

        return $storage->url($relativePath);
    }
}
