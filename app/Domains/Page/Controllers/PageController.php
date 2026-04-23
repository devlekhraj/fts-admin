<?php

declare(strict_types=1);

namespace App\Domains\Page\Controllers;

use App\Domains\Page\DTOs\PageUpsertData;
use App\Domains\Page\Requests\PageStoreRequest;
use App\Domains\Page\Resources\PageResource;
use App\Domains\Page\Services\PageService;
use App\Support\Exceptions\FieldValidationException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PageController extends Controller
{
    public function __construct(private readonly PageService $pageService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $perPageParam = (int) $request->query('per_page', 15);

        $result = $this->pageService->list(is_string($search) ? $search : null, $perPageParam);

        return response()->json([
            'data' => PageResource::collection($result['items']),
            'meta' => $result['meta'],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $page = $this->pageService->show($id);

        return response()->json([
            'data' => new PageResource($page),
            'success' => true,
        ], 200);
    }

    public function store(PageStoreRequest $request): JsonResponse
    {
        try {
            $dto = PageUpsertData::fromArray($request->validated());
            $page = $this->pageService->upsert($dto);

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
            $dto = PageUpsertData::fromArray($request->validated());
            $page = $this->pageService->upsert($dto, (int) $id);

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

    public function destroy(string $id): JsonResponse
    {
        $this->pageService->delete($id);

        return response()->json([
            'message' => 'Page deleted successfully.',
            'success' => true,
        ], 200);
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
