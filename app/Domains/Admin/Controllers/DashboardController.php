<?php

declare(strict_types=1);

namespace App\Domains\Admin\Controllers;

use App\Domains\Admin\Models\Admin;
use App\Domains\EmiRequest\Models\EmiRequest;
use App\Domains\Order\Models\Order;
use App\Domains\Shared\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class DashboardController
{
    /** Which part of the dashboard needs which permission. A number or list the admin may not see is left out (null). */
    private const NEEDS = [
        'orders' => ['access-orders'],
        'emi' => ['access-emi'],
        'products' => ['view-products'],
        'customers' => ['users-view'],
    ];

    /** Activity entries are shown only when the admin may see the thing they are about; anything else is for Administrators. */
    private const ACTIVITY_NEEDS = [
        'order' => ['access-orders'],
        'emi' => ['access-emi'],
        'exchange' => ['access-exchange'],
        'brand' => ['view-brands'],
        'categor' => ['view-product-categories'],   // before 'product', so a product category is judged as a category
        'product' => ['view-products'],
        'blog' => ['view-blogs'],
        'banner' => ['view-banners'],
    ];

    public function metrics(): JsonResponse
    {
        $admin = $this->admin();
        $may = fn (string $part): bool => $admin->hasAnyPermission(self::NEEDS[$part]);

        return response()->json([
            'data' => [
                'totalOrders' => $may('orders') ? Order::query()->count() : null,
                'totalEmiRequests' => $may('emi') ? (Schema::hasTable('emi_requests') ? (int) DB::table('emi_requests')->count() : 0) : null,
                'totalProducts' => $may('products') ? (Schema::hasTable('products') ? (int) DB::table('products')->count() : 0) : null,
                'totalCustomers' => $may('customers') ? (Schema::hasTable('users') ? (int) DB::table('users')->count() : 0) : null,
            ],
        ]);
    }

    private function admin(): Admin
    {
        $admin = auth('admin_api')->user();
        abort_unless($admin instanceof Admin, 401, 'Unauthenticated');
        $admin->loadMissing('role');

        return $admin;
    }

    private function maySeeActivity(Admin $admin, ?string $entityType): bool
    {
        if ($admin->isSuper()) {
            return true;
        }

        $type = strtolower((string) $entityType);
        foreach (self::ACTIVITY_NEEDS as $needle => $permissions) {
            if (str_contains($type, $needle)) {
                return $admin->hasAnyPermission($permissions);
            }
        }

        return false;
    }

    public function latest(): JsonResponse
    {
        $admin = $this->admin();
        $orders = ! $admin->hasAnyPermission(self::NEEDS['orders']) ? collect() : Order::query()
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
        if ($admin->hasAnyPermission(self::NEEDS['emi']) && Schema::hasTable('emi_requests')) {
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
                ->filter(fn (ActivityLog $log): bool => $log->entity !== null && $this->maySeeActivity($admin, $log->entity_type))
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
