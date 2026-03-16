<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin\File;

use App\Foundation\Application\File\Commands\UpdateFileUsageCommand;
use App\Foundation\Application\File\Handlers\UpdateFileUsageHandler;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\FileUsageModel;
use App\Foundation\Interfaces\Http\Requests\Admin\UpdateFileUsageRequest;
use App\Foundation\Shared\Domain\Exceptions\FieldValidationException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileUsageController extends Controller
{
    public function __construct(
        public readonly UpdateFileUsageHandler $updateFileUsageHandler
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'usage_type' => ['required', 'string'],
            'usage_id' => ['required', 'string'],
        ]);

        $usages = FileUsageModel::query()
            ->with('file')
            ->where('usage_type', $validated['usage_type'])
            ->where('usage_id', $validated['usage_id'])
            ->latest('id')
            ->get()
            ->map(function ($usage) {
                return [
                    'id' => $usage->id,
                    'file_id' => $usage->file_id,
                    'usage_type' => $usage->usage_type,
                    'usage_id' => $usage->usage_id,
                    'title' => $usage->title,
                    'alt_text' => $usage->alt_text,
                    'meta' => $usage->meta,
                    'url' => $usage->file?->file_path ? asset($usage->file->file_path) : null,
                    'size_info' => $usage->file ? "{$usage->file->width}x{$usage->file->height} (" . round($usage->file->file_size / 1024, 2) . " KB)" : null,
                    'created_at' => $usage->created_at,
                    'updated_at' => $usage->updated_at,
                ];
            });

        return response()->json([
            'data' => $usages,
        ]);
    }

    public function update(UpdateFileUsageRequest $request, int $fileUsageId): JsonResponse
    {
        $validated = $request->validated();

        try {
            $result = $this->updateFileUsageHandler->handle(new UpdateFileUsageCommand(
                fileUsageId: $fileUsageId,
                altText: trim((string) $validated['alt_text']),
                title: isset($validated['title']) ? trim((string) $validated['title']) : null,
                link: isset($validated['link']) ? trim((string) $validated['link']) : null,
                startDate: isset($validated['start_date']) ? trim((string) $validated['start_date']) : null,
                endDate: isset($validated['end_date']) ? trim((string) $validated['end_date']) : null,
                seqNo: isset($validated['seq_no']) ? (int) $validated['seq_no'] : 0,
                isActive: (bool) ($validated['is_active'] ?? false),
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

    public function delete(int $fileUsageId): JsonResponse
    {
        $fileUsage = FileUsageModel::query()->find($fileUsageId);
        if (! $fileUsage) {
            return response()->json([
                'message' => 'File usage not found.',
            ], 404);
        }

        $fileUsage->delete();

        return response()->json([
            'message' => 'File usage deleted successfully.',
        ]);
    }
}
