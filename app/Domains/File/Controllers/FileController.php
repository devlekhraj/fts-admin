<?php

declare(strict_types=1);

namespace App\Domains\File\Controllers;

use App\Domains\File\Requests\StoreFileAssignRequest;
use App\Domains\File\DTOs\FileAssignData;
use App\Domains\File\DTOs\ListFilesData;
use App\Domains\File\Services\FileService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

final class FileController extends Controller
{
    public function __construct(
        private readonly FileService $fileService,
    ) {}

    public function upload(Request $request): JsonResponse
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
            $payload = $this->fileService->upload(
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

        $alreadyExists = (bool) ($payload['already_exists'] ?? false);

        return response()->json($payload, $alreadyExists ? 200 : 201);
    }

    public function assign(StoreFileAssignRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $source = (string) ($validated['source'] ?? '');
        if ($source === 'upload' && ! $request->file('file')) {
            return response()->json([
                'message' => 'File is required for upload source.',
            ], 422);
        }

        $payload = $this->fileService->assign(FileAssignData::fromArray(
            $validated,
            $request->file('file')
        ));

        $status = (int) ($payload['status'] ?? 500);
        $body = is_array($payload['body'] ?? null) ? $payload['body'] : [];

        return response()->json($body, $status);
    }

    public function index(Request $request): JsonResponse
    {
        return $this->buildFileListResponse($request, false);
    }

    public function indexWithUsages(Request $request): JsonResponse
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

        $payload = $this->fileService->list(ListFilesData::fromArray($validated), $includeUsages);

        return response()->json($payload);
    }
}
