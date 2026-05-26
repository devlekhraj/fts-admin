<?php

declare(strict_types=1);

namespace App\Domains\File\Models;

use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Throwable;

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
        $defaultDisk = (string) config('filesystems.default');
        $fallbackDisk = 'cdn';

        $storedDisk = is_string($this->disk) && trim($this->disk) !== '' ? trim($this->disk) : null;

        // Prefer the disk where the file actually exists, to avoid broken URLs when disk config changes.
        if (is_string($storedDisk) && $this->diskHasFile($storedDisk, $relativePath)) {
            $disk = $storedDisk;
        } elseif ($this->diskHasFile($defaultDisk, $relativePath)) {
            $disk = $defaultDisk;
        } elseif ($this->diskHasFile($fallbackDisk, $relativePath)) {
            $disk = $fallbackDisk;
        } else {
            // Last resort: keep existing behaviour so we still return some URL.
            $disk = $storedDisk ?? $defaultDisk;
        }

        /** @var FilesystemAdapter $storage */
        $storage = Storage::disk($disk);

        return $storage->url($relativePath);
    }

    private function diskHasFile(string $disk, string $relativePath): bool
    {
        try {
            return Storage::disk($disk)->exists($relativePath);
        } catch (Throwable) {
            return false;
        }
    }
}
