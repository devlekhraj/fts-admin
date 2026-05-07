<?php

declare(strict_types=1);

namespace App\Domains\File\Controllers;

use App\Domains\File\Requests\UpdateFileUsageRequest;
use App\Domains\File\DTOs\UpdateFileUsageData;
use App\Domains\File\Services\FileUsageService;
use App\Support\Exceptions\FieldValidationException;
use Illuminate\Routing\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class FileUsageController extends Controller
{
    public function __construct(
        private readonly FileUsageService $fileUsageService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'usage_type' => ['required', 'string'],
            'usage_id' => ['required', 'string'],
        ]);

        $payload = $this->fileUsageService->index(
            (string) $validated['usage_type'],
            (string) $validated['usage_id'],
        );

        return response()->json($payload);
    }

    public function update(UpdateFileUsageRequest $request, int $fileUsageId): JsonResponse
    {
        $validated = $request->validated();

        try {
            $result = $this->fileUsageService->update(new UpdateFileUsageData(
                fileUsageId: $fileUsageId,
                altText: trim((string) $validated['alt_text']),
                title: isset($validated['title']) ? trim((string) $validated['title']) : null,
                link: isset($validated['link']) ? trim((string) $validated['link']) : null,
                startDate: isset($validated['start_date']) ? trim((string) $validated['start_date']) : null,
                endDate: isset($validated['end_date']) ? trim((string) $validated['end_date']) : null,
                seqNo: isset($validated['seq_no']) ? (int) $validated['seq_no'] : 0,
                isActive: (bool) ($validated['is_active'] ?? false),
                isDefault: (bool) ($validated['is_default'] ?? false),
            ));

            return response()->json([
                'message' => $result->message,
                'data' => $result,
            ]);
        } catch (FieldValidationException $e) {
            $message = $e->getMessage();
            $field = $e->field();
            $errors = $field ? [$field => [$message]] : [];

            return response()->json([
                'message' => $message,
                'errors' => $errors,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update file usage.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $fileUsageId): JsonResponse
    {
        $deleted = $this->fileUsageService->delete($fileUsageId);
        if (! $deleted) {
            return response()->json([
                'message' => 'File usage not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'File usage deleted successfully.',
        ]);
    }
}
