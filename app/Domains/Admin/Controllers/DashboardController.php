<?php

declare(strict_types=1);

namespace App\Domains\Admin\Controllers;

use App\Domains\EmiRequest\Models\EmiRequest;
use App\Domains\Order\Models\Order;
use App\Domains\Shared\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class DashboardController
{
    public function metrics(): JsonResponse
    {
        $totalOrders = Order::query()->count();
        $totalEmiRequests = Schema::hasTable('emi_requests') ? (int) DB::table('emi_requests')->count() : 0;
        $totalProducts = Schema::hasTable('products') ? (int) DB::table('products')->count() : 0;
        $totalCustomers = Schema::hasTable('users') ? (int) DB::table('users')->count() : 0;

        return response()->json([
            'data' => [
                'totalOrders' => $totalOrders,
                'totalEmiRequests' => $totalEmiRequests,
                'totalProducts' => $totalProducts,
                'totalCustomers' => $totalCustomers,
            ],
        ]);
    }

    public function latest(): JsonResponse
    {
        $orders = Order::query()
            ->with(['user:id,name,avatar'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(static function (Order $order): array {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_no,
                    'status' => $order->order_status,
                    'total' => $order->total,
                    'customer' => [
                        'id' => $order->user?->id,
                        'name' => $order->user?->name,
                        'avatar' => $order->user?->avatar,
                    ],
                    'created_at' => $order->created_at,
                ];
            })
            ->values();

        $emiRequests = [];
        if (Schema::hasTable('emi_requests')) {
            $emiRequests = EmiRequest::query()
                ->with(['user:id,name,avatar', 'product:id,name'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
                ->map(static function (EmiRequest $request): array {
                    $labels = EmiRequest::getStatusLabels();

                    return [
                        'id' => $request->id,
                        'product_name' => $request->product?->name,
                        'customer_name' => $request->user?->name ?? $request->name ?? $request->email,
                        'customer_avatar' => $request->user?->avatar,
                        'status' => $labels[$request->status] ?? 'Unknown',
                        'amount' => $request->emi_per_month,
                        'created_at' => $request->created_at,
                    ];
                })
                ->values();
        }

        $activityLogs = [];
        if (Schema::hasTable('activity_logs')) {
            $activityLogs = ActivityLog::query()
                ->with(['actor', 'entity'])
                ->orderByDesc('created_at')
                ->limit(30)
                ->get()
                // Only include logs whose related entity still exists (and is not soft-deleted).
                ->filter(static fn (ActivityLog $log): bool => $log->entity !== null)
                ->take(10)
                ->map(static function (ActivityLog $log): array {
                    $actorName = null;
                    if ($log->actor) {
                        $actorName = $log->actor->name
                            ?? $log->actor->email
                            ?? null;
                    }

                    return [
                        'id' => $log->id,
                        'action' => $log->action,
                        'label' => $log->description ?? $log->label,
                        'description' => $log->meta['notes'] ?? null,
                        'entity_type' => $log->entity_type,
                        'entity_id' => $log->entity_id,
                        'actor_name' => $actorName,
                        'created_at' => $log->created_at,
                    ];
                })
                ->values();
        }

        return response()->json([
            'data' => [
                'orders' => $orders,
                'emi_requests' => $emiRequests,
                'activity_logs' => $activityLogs,
            ],
        ]);
    }
}
