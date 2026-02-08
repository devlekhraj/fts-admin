<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\EmiRequestModel;
use App\Foundation\Interfaces\Http\Requests\Admin\ApproveEmiApplicationRequest;
use App\Foundation\Interfaces\Http\Requests\Admin\StoreEmiApplicationRequest;
use App\Foundation\Interfaces\Http\Requests\Admin\UpdateEmiApplicationRequest;
use App\Foundation\Interfaces\Http\Resources\EmiApplicationListResource;
use App\Foundation\Interfaces\Http\Resources\EmiApplicationResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmiApplicationsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 10);
        $paginator = EmiRequestModel::query()
            ->with(['product', 'user'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => EmiApplicationListResource::collection($paginator->items()),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $record = EmiRequestModel::query()->findOrFail($id);

        return response()->json(new EmiApplicationResource($record));
    }

    public function store(StoreEmiApplicationRequest $request): JsonResponse
    {
        // TODO: Create EMI application.
        return response()->json([], 201);
    }

    public function update(string $id, UpdateEmiApplicationRequest $request): JsonResponse
    {
        // TODO: Update EMI application.
        return response()->json(['id' => $id]);
    }

    public function approve(string $id, ApproveEmiApplicationRequest $request): JsonResponse
    {
        // TODO: Approve EMI application.
        return response()->json(['id' => $id]);
    }
}
