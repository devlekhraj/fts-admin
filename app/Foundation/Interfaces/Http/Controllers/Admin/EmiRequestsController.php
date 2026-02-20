<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\EmiRequestModel;
use App\Foundation\Interfaces\Http\Requests\Admin\ApproveEmiRequestRequest;
use App\Foundation\Interfaces\Http\Requests\Admin\StoreEmiRequestRequest;
use App\Foundation\Interfaces\Http\Requests\Admin\UpdateEmiRequestRequest;
use App\Foundation\Interfaces\Http\Resources\EmiRequestListResource;
use App\Foundation\Interfaces\Http\Resources\EmiRequestResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmiRequestsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $emiType = $request->query('emi_type');
        $status = $request->query('status');

        $query = EmiRequestModel::query()
            ->with(['product', 'user']);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('application_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%");
                    });

                if (is_numeric($search)) {
                    $builder->orWhere('id', (int) $search);
                }
            });
        }

        if ($emiType !== null && $emiType !== '') {
            $query->where('emi_type', $emiType);
        }

        if ($status !== null && $status !== '') {
            $query->where('status', (int) $status);
        }

        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data' => EmiRequestListResource::collection($paginator->items()),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $record = EmiRequestModel::query()->findOrFail($id);

        return response()->json(new EmiRequestResource($record));
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
