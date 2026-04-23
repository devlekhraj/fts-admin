<?php

declare(strict_types=1);

namespace App\Domains\PaymentMethod\Controllers;

use App\Domains\PaymentMethod\DTOs\PaymentMethodListData;
use App\Domains\PaymentMethod\DTOs\PaymentMethodUpdateData;
use App\Domains\PaymentMethod\Requests\UpdatePaymentMethodRequest;
use App\Domains\PaymentMethod\Resources\PaymentMethodResource;
use App\Domains\PaymentMethod\Services\PaymentMethodService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PaymentMethodController extends Controller
{
    public function __construct(private readonly PaymentMethodService $paymentMethodService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $result = $this->paymentMethodService->list(PaymentMethodListData::fromArray($request->query()));

        return response()->json([
            'data' => PaymentMethodResource::collection($result['items']),
            'meta' => $result['meta'],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $paymentMethod = $this->paymentMethodService->detail($id);

        return response()->json([
            'data' => (new PaymentMethodResource($paymentMethod)),
            'success' => true,
        ], 200);
    }

    public function store(): JsonResponse
    {
        return response()->json([], 201);
    }

    public function update(UpdatePaymentMethodRequest $request, string $id): JsonResponse
    {
        $paymentMethod = $this->paymentMethodService->update($id, PaymentMethodUpdateData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Payment method updated successfully.',
            'data' => (new PaymentMethodResource($paymentMethod)),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        return response()->json(null, 204);
    }
}

