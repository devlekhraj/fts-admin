<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Resources;

use App\Domains\EmiRequest\Models\EmiApplication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;

class EmiApplicationResource extends JsonResource
{
    private string $disk;

    public function __construct(EmiApplication $resource)
    {
        parent::__construct($resource);
        $this->disk = (string) config('filesystems.default');
    }

    public function toArray(Request $request): array
    {
        /** @var FilesystemAdapter $storage */
        $storage = Storage::disk($this->disk);

        return [
            'id' => $this->id,
            'application_id' => $this->application_id,
            'bank_name' => $this->emiBank?->name ?? $this->emiBank?->slug ?? null,
            'file_path' => $this->file_path ?? null,
            'file_url' => $this->file_path
                ? $storage->url($this->file_path)
                : null,
            'status' => $this->status ?? null,
            'created_at' => $this->created_at
        ];
    }
}
