<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\EmiBankModel;
use App\Foundation\Interfaces\Http\Requests\Admin\ApproveEmiBankRequest;
use App\Foundation\Interfaces\Http\Requests\Admin\StoreEmiBankRequest;
use App\Foundation\Interfaces\Http\Requests\Admin\UpdateEmiBankRequest;
use App\Foundation\Interfaces\Http\Resources\EmiBankListResource;
use App\Foundation\Interfaces\Http\Resources\EmiBankResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmiBankController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 10);
        $paginator = EmiBankModel::query()
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => EmiBankListResource::collection($paginator->items()),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $record = EmiBankModel::query()->findOrFail($id);

        return response()->json(new EmiBankResource($record));
    }

    public function tenureList(string $id): JsonResponse
    {
        $record = EmiBankModel::query()->findOrFail($id);

        return response()->json([
            'data' => [
                'tenures' => $record->tenures ?? [],
            ],
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
