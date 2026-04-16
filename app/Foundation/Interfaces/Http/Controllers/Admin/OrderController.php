<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Foundation\Interfaces\Http\Resources\OrderResource;
use App\Http\Controllers\Controller;
use App\Mail\OrderStatusUpdatedMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        $query = OrderModel::query()
            ->with(['user', 'paymentMethod'])
            ->orderByDesc('created_at');

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('order_number', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $perPageParam = (int) $request->query('per_page', 15);
        if ($perPageParam === -1) {
            $items = $query->get();

            return response()->json([
                'data' => OrderResource::collection($items),
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
            'data' => OrderResource::collection($paginator->items()),
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

    public function details(string $id): JsonResponse
    {
        $order = OrderModel::query()
            ->with(['user', 'paymentMethod', 'receipent', 'shippingAddress'])
            ->findOrFail($id);

        return response()->json(
            [
                'data' => new OrderResource($order),
                'success' => true,
            ],
            200,
        );
    }

    // public function generateWarranty(string $id): JsonResponse
    // {
    //     $order = OrderModel::query()->with('user')->findOrFail($id);

    //     // dd($order);
    //     if ($order->warranty_token) {
    //         return response()->json(
    //             [
    //                 'warranty_token' => $order->warranty_token,
    //                 'success' => true,
    //                 'message' => 'Warranty token already exists.',
    //             ],
    //             200,
    //         );
    //     }

    //     $order->warranty_token = 'WS-'.Str::upper(Str::random(8));
    //     $order->save();

    //     $order->logActivity(action: 'Warrenty Generated', label: 'Warrenty Generated', description: 'Warrenty generated for'.$order->order_no.' successfully', actor: auth()->user());

    //     return response()->json(
    //         [
    //             'warranty_token' => $order->warranty_token,
    //             'success' => true,
    //         ],
    //         200,
    //     );
    // }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'integer', 'in:0,1,2,3,4,5'],
        ]);

        $order = OrderModel::query()->findOrFail($id);
        $oldLabel = $order->order_status;
        $order->status = (int) $validated['status'];
        $order->save();

        $newLabel = $order->order_status;
        $order->logActivity(

            action: 'order_status_updated',
            label: "Order moved to {$newLabel}",
            description: "Order has been {$newLabel}",
            oldStatus: (string) $oldLabel,
            newStatus: (string) $oldLabel,
            meta: [
                'old_status_label' => $oldLabel,
                'new_status_label' => $newLabel,
                'order_no' => $order->order_no,
            ],
            actor: auth()->user()
        );

        try {
            $customer = $order->user;
            if ($customer && $customer->email) {
                $orderNumber = $order->order_number ?? ($order->order_no ?? $order->id);
                $statusLabel = $order->order_status;
                $timestamp = now()->timezone(config('app.timezone'))->format('Y-m-d H:i:s');
                Mail::to($customer->email)->send(new OrderStatusUpdatedMail(orderNumber: (string) $orderNumber, status: $statusLabel, customerName: $customer->name ?? null, updatedAt: $timestamp));
            }
        } catch (\Throwable $mailException) {
            Log::error('Failed to send order status update email.', [
                'order_id' => $order->id,
                'email' => $order->user->email ?? null,
                'error' => $mailException->getMessage(),
            ]);
        }

        return response()->json(
            [
                'success' => true,
                'status' => $order->order_status,
                'status_code' => $order->status,
            ],
            200,
        );
    }
}
