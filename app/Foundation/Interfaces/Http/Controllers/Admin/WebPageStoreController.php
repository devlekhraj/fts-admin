<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use App\Foundation\Application\Page\DTO\PageDto;
use App\Foundation\Application\Page\Handlers\PageStoreHandler;
use App\Foundation\Interfaces\Http\Resources\PageResource;
use App\Foundation\Interfaces\Http\Requests\Admin\PageStoreRequest;
use App\Foundation\Shared\Domain\Exceptions\FieldValidationException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class WebPageStoreController extends Controller
{
    public function __construct(private readonly PageStoreHandler $handler)
    {
    }

    public function store(PageStoreRequest $request): JsonResponse
    {
        try {
            
            $dto = PageDto::fromRequest($request);

            $page = $this->handler->handle($dto);

            return response()->json([
                'message' => 'Page created successfully.',
                'data' => new PageResource($page),
                'success' => true,
            ], 201);
        } catch (FieldValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e, 'Failed to create page.');
        }
    }

    public function update(string $id, PageStoreRequest $request): JsonResponse
    {
        try {
            $dto = PageDto::fromRequest($request);
            
            $page = $this->handler->handle($dto, (int) $id);

            return response()->json([
                'message' => 'Page updated successfully.',
                'data' => new PageResource($page->refresh()),
                'success' => true,
            ], 200);
        } catch (FieldValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e, 'Failed to update page.');
        }
    }

    private function validationErrorResponse(FieldValidationException $e): JsonResponse
    {
        $message = $e->getMessage();
        $field = $e->field();
        $errors = $field ? [$field => [$message]] : [];

        return response()->json([
            'message' => $message,
            'errors' => $errors,
            'success' => false,
        ], 422);
    }

    private function serverErrorResponse(\Exception $e, string $fallbackMessage): JsonResponse
    {
        return response()->json([
            'message' => $fallbackMessage,
            'error' => $e->getMessage(),
            'success' => false,
        ], 500);
    }
}
