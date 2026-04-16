<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FileModel extends BaseModel
{
    protected $table = 'files';
    protected $guarded = [];

    protected $appends = ['url'];
    protected $casts = [
        'meta' => 'array',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(FileUsageModel::class, 'file_id');
    }

    public function getUrlAttribute(): ?string
    {
        $path = $this->file_path;
        if (!is_string($path) || trim($path) === '') {
            return null;
        }

        $path = trim($path);
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // if($this->disk == 'cdn' || config('APP_ENV') === 'local'){
            $baseUrl = trim((string) config('filesystems.disks.cdn.url', ''), '/');
        // }else{
        //     $baseUrl = trim((string) config('filesystems.disks.fatafat_cdn.url', ''), '/');
        // }
        $relativePath = ltrim($path, '/');

        if ($baseUrl === '') {
            return '/' . $relativePath;
        }

        return $baseUrl . '/' . $relativePath;
    }
}
