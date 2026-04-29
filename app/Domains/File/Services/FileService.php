<?php

declare(strict_types=1);

namespace App\Domains\File\Services;

use App\Domains\File\Actions\FileUploadAction;
use App\Domains\File\DTOs\ListFilesData;
use App\Domains\File\Models\File;
use App\Domains\File\Models\FileUsage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final class FileService
{
    public function __construct(
        private readonly FileUploadAction $fileUploadAction,
    ) {}

    public function upload(UploadedFile $file, ?string $directory): array
    {
        $result = $this->fileUploadAction->execute($file, $directory, null);
        $alreadyExists = (bool) ($result['already_exists'] ?? false);

        return [
            'already_exists' => $alreadyExists,
            'file' => $result['file_data'] ?? null,
        ];
    }

    public function assign(array $data, ?UploadedFile $file = null): array
    {
        $source = (string) ($data['source'] ?? '');
        $usageType = trim((string) ($data['usage_type'] ?? ''));
        $usageId = (int) ($data['usage_id'] ?? 0);

        if ($source === 'upload' && ! $file) {
            throw new RuntimeException('File is required for upload source.');
        }

        try {
            return DB::transaction(function () use ($data, $file, $source, $usageType, $usageId) {
                // Get or upload file
                $fileId = null;
                $fileData = null;

                if ($source === 'existing') {
                    $fileId = (int) ($data['image_id'] ?? 0);
                    $fileModel = File::find($fileId);
                    if (! $fileModel) {
                        throw new RuntimeException('Selected image not found.');
                    }
                    $fileData = $fileModel->toArray();
                } else {
                    // Always use usage_type/usage_id directory pattern when both are present
                    if ($usageType && $usageId) {
                        $directory = $usageType . '/' . $usageId; // Always use usage_type/{usage_id} pattern
                    } else {
                        $directory = isset($data['directory']) ? $data['directory'] : 'uploads'; // Use directory field or default uploads
                    }

                    $uploadResult = $this->fileUploadAction->execute(
                        $file,
                        $directory,
                        $data['file_name'] ?? null
                    );
                    $fileId = $uploadResult['file_id'];
                    $fileData = $uploadResult['file_data'];
                }

                // Handle default flag - unset other defaults for this usage context
                if (($data['is_default'] ?? false)) {
                    FileUsage::where('usage_type', $usageType)
                        ->where('usage_id', $usageId)
                        ->update(['meta->is_default' => false]);
                }

                // Create file usage record
                // Get incoming meta array first
                $incomingMeta = is_array($data['meta'] ?? null) ? $data['meta'] : [];

                // Build base meta from individual fields, but exclude is_default if it's in incoming meta
                $baseMeta = [
                    'caption' => isset($data['caption']) && trim((string) $data['caption']) !== '' ? trim((string) $data['caption']) : null,
                    'description' => isset($data['description']) && trim((string) $data['description']) !== '' ? trim((string) $data['description']) : null,
                ];

                // Add is_default from individual field only if not present in incoming meta
                if (!array_key_exists('is_default', $incomingMeta)) {
                    $baseMeta['is_default'] = $data['is_default'] ?? false;
                }

                // Merge incoming meta with base meta (incoming meta takes precedence)
                $finalMeta = array_merge($baseMeta, $incomingMeta);

                $usage = FileUsage::create([
                    'file_id' => $fileId,
                    'usage_type' => $usageType,
                    'usage_id' => $usageId,
                    'title' => isset($data['caption']) ? trim((string) $data['caption']) : null,
                    'alt_text' => isset($data['alt_text']) ? trim((string) $data['alt_text']) : '',
                    'meta' => array_filter($finalMeta, fn($v) => ! is_null($v)),
                ]);

                return [
                    'status' => 201,
                    'body' => [
                        'message' => 'File assigned successfully.',
                        'data' => [
                            'source' => $source,
                            'file' => $fileData,
                            'usage' => $usage?->toArray(),
                            'url' => $fileData['url'] ?? null,
                        ],
                    ],
                ];
            });
        } catch (Throwable $exception) {
            if ($source === 'existing' && $exception->getMessage() === 'Selected image not found.') {
                return [
                    'status' => 404,
                    'body' => [
                        'message' => 'Selected image not found.',
                    ],
                ];
            }

            $body = [
                'message' => 'Failed to process file assignment. -' . $exception->getMessage(),
            ];
            if ((bool) config('app.debug', false)) {
                $body['error'] = $exception->getMessage();
            }

            return [
                'status' => 500,
                'body' => $body,
            ];
        }
    }

    public function list(ListFilesData $data, bool $includeUsages): array
    {
        $query = File::query()
            ->where('file_path', 'not like', '%emi-requests%')
            ->latest('id');

        if ($includeUsages) {
            $query->with(['usages' => static function ($builder): void {
                $builder->latest('id');
            }]);
        }

        if (is_string($data->search) && $data->search !== '') {
            $search = $data->search;
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->orWhere('file_name', 'like', "%{$search}%")
                    ->orWhere('file_path', 'like', "%{$search}%");
            });
        }

        if (is_string($data->tag) && $data->tag !== '') {
            $query->where('meta->directory', $data->tag);
        }

        $tags = File::query()
            ->where('file_path', 'not like', '%emi-requests%')
            ->get(['meta'])
            ->map(function (File $file): ?string {
                $meta = is_array($file->meta) ? $file->meta : [];
                $directory = $meta['directory'] ?? null;
                if (! is_string($directory)) {
                    return null;
                }
                $normalized = trim($directory);

                return $normalized !== '' ? $normalized : null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        $paginated = $query->paginate($data->perPage);
        $items = collect($paginated->items())->map(function (File $file) use ($includeUsages): array {
            $payload = $file->toArray();
            $meta = is_array($file->meta) ? $file->meta : [];
            $directory = $meta['directory'] ?? null;

            $payload['tags'] = is_string($directory) && trim($directory) !== ''
                ? [trim($directory)]
                : [];

            if ($includeUsages && $file->relationLoaded('usages')) {
                $usages = $file->usages->map(static function ($usage): array {
                    return [
                        'id' => $usage->id,
                        'usage_type' => $usage->usage_type,
                        'usage_id' => $usage->usage_id,
                        'title' => $usage->title,
                        'alt_text' => $usage->alt_text,
                        'meta' => is_array($usage->meta) ? $usage->meta : [],
                        'created_at' => $usage->created_at,
                        'updated_at' => $usage->updated_at,
                    ];
                })->values();

                $payload['usages'] = $usages->all();
                $payload['usage_count'] = $usages->count();
                $payload['usage_types'] = $usages
                    ->pluck('usage_type')
                    ->filter(static fn($value) => is_string($value) && trim($value) !== '')
                    ->map(static fn(string $value) => trim($value))
                    ->unique()
                    ->values()
                    ->all();
            }

            return $payload;
        })->values()->all();

        return [
            'data' => $items,
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
            ],
            'tags' => $tags,
        ];
    }
}
