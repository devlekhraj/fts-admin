<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin\File;

use App\Foundation\Application\File\Handlers\ImageUploadHandler;
use App\Foundation\Interfaces\Http\Requests\Admin\ImageStoreRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Throwable;

final class ImageUploadController extends Controller
{
    public function __construct(
        private readonly ImageUploadHandler $imageUploadHandler,
    ) {}

    public function uploadImage(ImageStoreRequest $request): JsonResponse
    {

        try {
            $result = $this->imageUploadHandler->handle($request->toDto());

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
