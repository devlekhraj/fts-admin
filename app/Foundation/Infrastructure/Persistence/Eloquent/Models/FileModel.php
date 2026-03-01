<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Illuminate\Support\Facades\Storage;

class FileModel extends BaseModel
{
    protected $table = 'files';

    protected $appends = ['url'];

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

        return Storage::disk('cdn')->url(ltrim($path, '/'));
    }
}
