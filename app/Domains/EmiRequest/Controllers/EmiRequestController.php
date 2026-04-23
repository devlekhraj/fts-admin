<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Controllers;

use App\Domains\EmiRequest\DTOs\EmiRequestListData;
use App\Domains\EmiRequest\Requests\ApproveEmiRequestRequest;
use App\Domains\EmiRequest\Requests\StoreEmiRequestRequest;
use App\Domains\EmiRequest\Requests\UpdateEmiRequestRequest;
use App\Domains\EmiRequest\Resources\EmiRequestListResource;
use App\Domains\EmiRequest\Services\EmiRequestService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmiRequestController extends Controller
{
    public function __construct(
        private readonly EmiRequestService $emiRequestService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = EmiRequestListData::fromArray($request->query());
        $paginator = $this->emiRequestService->list($data);

        return response()->json([
            'data' => EmiRequestListResource::collection($paginator->items()),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $record = $this->emiRequestService->show($id);

        return response()->json(new EmiRequestListResource($record));
    }

    public function store(StoreEmiRequestRequest $request): JsonResponse
    {
        // TODO: Create EMI request.
        return response()->json([], 201);
    }

    public function update(string $id, UpdateEmiRequestRequest $request): JsonResponse
    {
        // TODO: Update EMI request.
        return response()->json(['id' => $id]);
    }

    public function approve(string $id, ApproveEmiRequestRequest $request): JsonResponse
    {
        // TODO: Approve EMI request.
        return response()->json(['id' => $id]);
    }
}

