<?php

declare(strict_types=1);

namespace App\Domains\File\Services;

use App\Domains\File\Actions\AssignExistingFileAction;
use App\Domains\File\Actions\AssignFileUsageAction;
use App\Domains\File\Actions\AssignUploadedFileAction;
use App\Domains\File\DTOs\AssignExistingFileData;
use App\Domains\File\DTOs\AssignFileUsageData;
use App\Domains\File\DTOs\AssignUploadedFileData;
use App\Domains\File\DTOs\FileAssignData;
use App\Domains\File\DTOs\ListFilesData;
use App\Domains\File\Models\File;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Throwable;

final class FileService
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
        private readonly AssignExistingFileAction $assignExistingFileAction,
        private readonly AssignUploadedFileAction $assignUploadedFileAction,
        private readonly AssignFileUsageAction $assignFileUsageAction,
    ) {}

    public function upload(UploadedFile $file, ?string $directory): array
    {
        $result = $this->fileUploadService->uploadImageAsWebp($file, $directory);
        $alreadyExists = (bool) ($result['already_exists'] ?? false);

        return [
            'already_exists' => $alreadyExists,
            'file' => $result['file_data'] ?? null,
        ];
    }

    public function assign(FileAssignData $data): array
    {
        $source = $data->source;
        if ($source === 'upload' && ! $data->file) {
            throw new RuntimeException('File is required for upload source.');
        }

        try {
            $sourceResult = $source === 'existing'
                ? $this->assignExistingFileAction->execute(new AssignExistingFileData(
                    imageId: (int) $data->imageId,
                ))
                : $this->assignUploadedFileAction->execute(new AssignUploadedFileData(
                    file: $data->file,
                    usageType: $data->usageType,
                    usageId: $data->usageId,
                    fileName: $data->fileName,
                ));
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
                'message' => 'Failed to process file assignment. -'.$exception->getMessage(),
            ];
            if ((bool) config('app.debug', false)) {
                $body['error'] = $exception->getMessage();
            }

            return [
                'status' => 500,
                'body' => $body,
            ];
        }

        $fileId = (int) ($sourceResult['file_id'] ?? 0);
        $fileData = is_array($sourceResult['file_data'] ?? null) ? $sourceResult['file_data'] : null;

        $usageResult = $this->assignFileUsageAction->execute(new AssignFileUsageData(
            fileId: $fileId,
            usageType: $data->usageType,
            usageId: $data->usageId,
            altText: $data->altText,
            isDefault: $data->isDefault,
            caption: $data->caption,
            description: $data->description,
        ));

        $usage = $usageResult['usage'] ?? null;

        return [
            'status' => 201,
            'body' => [
                'message' => 'File assigned successfully.',
                'data' => [
                    'source' => $source,
                    'file' => $fileData,
                    'usage' => $usage,
                    'url' => $fileData['url'] ?? null,
                ],
            ],
        ];
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
                    ->filter(static fn ($value) => is_string($value) && trim($value) !== '')
                    ->map(static fn (string $value) => trim($value))
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

