<?php

declare(strict_types=1);

namespace App\Domains\Order\Controllers;

use App\Domains\Order\DTOs\OrderListData;
use App\Domains\Order\DTOs\OrderStatusUpdateData;
use App\Domains\Order\Resources\OrderResource;
use App\Domains\Order\Services\OrderService;
use Illuminate\Routing\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = OrderListData::fromArray($request->query());
        $result = $this->orderService->list($data);

        return response()->json([
            'data' => OrderResource::collection($result['items']),
            'meta' => $result['meta'],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $order = $this->orderService->show($id);

        return response()->json([
            'data' => new OrderResource($order),
            'success' => true,
        ], 200);
    }

    public function warranty(string $id): JsonResponse
    {
        return response()->json($this->orderService->warranty($id), 200);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'integer', 'in:0,1,2,3,4,5,6'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data = OrderStatusUpdateData::fromArray($validated);
        $order = $this->orderService->updateStatus($id, $data);

        return response()->json([
            'success' => true,
            'status' => $order->order_status,
            'status_code' => $order->status,
        ], 200);
    }

    public function comment(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:220'],
        ]);

        $order = $this->orderService->show($id);
        $actor = auth('admin_api')->user();

        $activity = $order->logActivity(
            action: 'order_comment',
            label: 'Comment added',
            description: $validated['comment'],
            actor: $actor,
            meta: array_filter([
                'type' => 'comment',
            ], fn($v) => $v !== null && $v !== ''),
        );

        return response()->json([
            'success' => true,
            'activity_id' => $activity->getKey(),
        ], 200);
    }
}
