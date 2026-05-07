<?php

declare(strict_types=1);

namespace App\Domains\Admin\Controllers;

use App\Domains\Order\Models\Order;
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
}

