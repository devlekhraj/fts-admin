<?php

declare(strict_types=1);

namespace App\Domains\EmiBank\Controllers;

use App\Domains\EmiBank\DTOs\EmiBankListData;
use App\Domains\EmiBank\Requests\ApproveEmiBankRequest;
use App\Domains\EmiBank\Requests\StoreEmiBankRequest;
use App\Domains\EmiBank\Requests\UpdateEmiBankRequest;
use App\Domains\EmiBank\Resources\EmiBankListResource;
use App\Domains\EmiBank\Resources\EmiBankResource;
use App\Domains\EmiBank\Services\EmiBankService;
use Illuminate\Routing\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmiBankController extends Controller
{
    public function __construct(
        private readonly EmiBankService $emiBankService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = EmiBankListData::fromArray($request->query());
        $paginator = $this->emiBankService->list($data);

        return response()->json([
            'data' => EmiBankListResource::collection($paginator->items()),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $record = $this->emiBankService->show($id);

        return response()->json(new EmiBankResource($record));
    }

    public function tenures(string $id): JsonResponse
    {
        return response()->json([
            'data' => $this->emiBankService->tenures($id),
        ]);
    }

    public function store(StoreEmiBankRequest $request): JsonResponse
    {
        // TODO: Create EMI bank.
        return response()->json([], 201);
    }

    public function update(string $id, UpdateEmiBankRequest $request): JsonResponse
    {
        // TODO: Update EMI bank.
        return response()->json(['id' => $id]);
    }

    public function approve(string $id, ApproveEmiBankRequest $request): JsonResponse
    {
        // TODO: Approve EMI bank.
        return response()->json(['id' => $id]);
    }
}
