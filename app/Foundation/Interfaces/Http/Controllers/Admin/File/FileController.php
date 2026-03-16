<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin\File;

use App\Foundation\Application\File\Commands\AssignExistingFileCommand;
use App\Foundation\Application\File\Commands\AssignFileUsageCommand;
use App\Foundation\Application\File\Commands\AssignUploadedFileCommand;
use App\Foundation\Application\File\Handlers\AssignExistingFileHandler;
use App\Foundation\Application\File\Handlers\AssignFileUsageHandler;
use App\Foundation\Application\File\Handlers\AssignUploadedFileHandler;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\FileModel;
use App\Foundation\Interfaces\Http\Requests\Admin\StoreFileAssignRequest;
use App\Foundation\Shared\Application\Contracts\FileUploadService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class FileController extends Controller
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
        private readonly AssignExistingFileHandler $assignExistingFileHandler,
        private readonly AssignUploadedFileHandler $assignUploadedFileHandler,
        private readonly AssignFileUsageHandler $assignFileUsageHandler,
    ) {}

    public function fileUpload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'image', 'max:10240'],
            'directory' => ['nullable', 'string', 'max:120'],
        ]);

        $file = $request->file('file');
        if (! $file) {
            return response()->json([
                'message' => 'File is required.',
            ], 422);
        }

        try {
            $result = $this->fileUploadService->uploadImageAsWebp(
                $file,
                isset($validated['directory']) ? (string) $validated['directory'] : null
            );
        } catch (Throwable $exception) {
            $response = [
                'message' => 'Failed to upload file.',
            ];

            if ((bool) config('app.debug', false)) {
                $response['error'] = $exception->getMessage();
            }

            return response()->json($response, 500);
        }

        $alreadyExists = (bool) ($result['already_exists'] ?? false);

        return response()->json([
            'already_exists' => $alreadyExists,
            'file' => $result['file_data'] ?? null,
        ], $alreadyExists ? 200 : 201);
    }

    public function fileAssign(StoreFileAssignRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $source = (string) $validated['source'];
        if ($source === 'upload' && ! $request->file('file')) {
            return response()->json([
                'message' => 'File is required for upload source.',
            ], 422);
        }

        try {
            $sourceResult = $source === 'existing'
                ? $this->assignExistingFileHandler->handle(new AssignExistingFileCommand(
                    imageId: (int) $validated['image_id'],
                ))
                : $this->assignUploadedFileHandler->handle(new AssignUploadedFileCommand(
                    file: $request->file('file'),
                    directory: isset($validated['directory']) ? (string) $validated['directory'] : null,
                ));
        } catch (Throwable $exception) {
            if ($source === 'existing' && $exception->getMessage() === 'Selected image not found.') {
                return response()->json([
                    'message' => 'Selected image not found.',
                ], 404);
            }

            $response = [
                'message' => 'Failed to process file assignment.',
            ];

            if ((bool) config('app.debug', false)) {
                $response['error'] = $exception->getMessage();
            }

            return response()->json($response, 500);
        }

        $fileId = (int) ($sourceResult['file_id'] ?? 0);
        $fileData = is_array($sourceResult['file_data'] ?? null) ? $sourceResult['file_data'] : null;

        $usageType = trim((string) $validated['usage_type']);
        $usageId = (int) $validated['usage_id'];
        $altText = trim((string) $validated['alt_text']);
        $usageResult = $this->assignFileUsageHandler->handle(new AssignFileUsageCommand(
            fileId: $fileId,
            usageType: $usageType,
            usageId: $usageId,
            altText: $altText,
            // caption: isset($validated['caption']) ? (string) $validated['caption'] : null,
            // description: isset($validated['description']) ? (string) $validated['description'] : null,
        ));
        $usage = $usageResult['usage'] ?? null;

        return response()->json([
            'message' => 'File assigned successfully.',
            'data' => [
                'source' => $source,
                'file' => $fileData,
                'usage' => $usage,
            ],
        ], 201);
    }

    public function fileList(Request $request): JsonResponse
    {
        return $this->buildFileListResponse($request, false);
    }

    public function fileListWithUsages(Request $request): JsonResponse
    {
        return $this->buildFileListResponse($request, true);
    }

    private function buildFileListResponse(Request $request, bool $includeUsages): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
            'search' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', 'string', 'max:120'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 24);
        $search = isset($validated['search']) ? trim((string) $validated['search']) : '';
        $tag = isset($validated['tag']) ? trim((string) $validated['tag']) : '';

        $query = FileModel::query()->latest('id');
        if ($includeUsages) {
            $query->with(['usages' => static function ($builder): void {
                $builder->latest('id');
            }]);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->orWhere('file_name', 'like', "%{$search}%")
                    ->orWhere('file_path', 'like', "%{$search}%");
            });
        }

        if ($tag !== '') {
            $query->where('meta->directory', $tag);
        }

        $tags = FileModel::query()
            ->get(['meta'])
            ->map(function (FileModel $file): ?string {
                $meta = is_array($file->meta) ? $file->meta : [];
                $directory = $meta['directory'] ?? null;
                if (!is_string($directory)) {
                    return null;
                }
                $normalized = trim($directory);
                return $normalized !== '' ? $normalized : null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        $paginated = $query->paginate($perPage);
        $items = collect($paginated->items())->map(function (FileModel $file) use ($includeUsages): array {
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

        return response()->json([
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
        ]);
    }
}
