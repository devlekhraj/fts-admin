<?php

declare(strict_types=1);

namespace App\Domains\File\Controllers;

use App\Domains\File\Requests\ImageStoreRequest;
use App\Domains\File\Services\ImageAssignService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Throwable;

final class ImageUploadController extends Controller
{
    public function __construct(
        private readonly ImageAssignService $imageAssignService,
    ) {}

    public function store(ImageStoreRequest $request): JsonResponse
    {
        try {
            $result = $this->imageAssignService->assign($request->toData());

            return response()->json([
                'message' => 'File assigned successfully.',
                'data' => $result,
            ], 201);
        } catch (Throwable $exception) {
            $response = [
                'message' => $exception->getMessage() ?: 'Failed to process file assignment.',
            ];

            if ((bool) config('app.debug', false)) {
                $response['error'] = $exception->getMessage();
                $response['trace'] = $exception->getTrace();
            }

            return response()->json($response, 500);
        }
    }
}
