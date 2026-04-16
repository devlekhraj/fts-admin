<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use App\Foundation\Interfaces\Http\Resources\FaqResource;
use App\Http\Controllers\Controller;
use App\Models\FaqModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        $query = FaqModel::query()
            ->orderByDesc('updated_at');

        $type = trim((string) $request->query('type', ''));
        if ($type === 'brand') {
            $query->with('brand');
        } elseif ($type === 'category') {
            $query->with('category');
        } else {
            $query->with(['brand', 'category']);
        }

        if ($type !== '') {
            if ($type === 'general') {
                $query->where(function ($builder) {
                    $builder->whereNull('type')
                        ->orWhere('type', '');
                });
            } else {
                $query->where('type', $type);
            }
        }

        $typeId = $request->query('type_id');
        if ($typeId !== null && trim((string) $typeId) !== '') {
            $query->where('type_id', $typeId);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        $perPageParam = (int) $request->query('per_page', 15);
        if ($perPageParam === -1) {
            $items = $query->get();

            return response()->json([
                'data' => FaqResource::collection($items),
                'meta' => [
                    'current_page' => 1,
                    'per_page' => $items->count(),
                    'total' => $items->count(),
                    'last_page' => 1,
                    'from' => $items->count() > 0 ? 1 : null,
                    'to' => $items->count() > 0 ? $items->count() : null,
                ],
            ]);
        }

        $perPage = max(1, min($perPageParam, 100));
        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => FaqResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }
}
