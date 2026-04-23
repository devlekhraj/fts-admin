<?php

declare(strict_types=1);

namespace App\Domains\PaymentMethod\Controllers;

use App\Domains\PaymentMethod\DTOs\PaymentMethodImageUpdateData;
use App\Domains\PaymentMethod\Requests\UpdatePaymentMethodImageRequest;
use App\Domains\PaymentMethod\Services\PaymentMethodImageService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class PaymentMethodImageController extends Controller
{
    public function __construct(private readonly PaymentMethodImageService $paymentMethodImageService)
    {
    }

    public function update(UpdatePaymentMethodImageRequest $request, string $id, string $fileUsageId): JsonResponse
    {
        $fileUsage = $this->paymentMethodImageService->update(
            paymentMethodId: $id,
            fileUsageId: $fileUsageId,
            data: PaymentMethodImageUpdateData::fromArray($request->validated()),
        );

        if (! $fileUsage) {
            return response()->json([
                'message' => 'Payment method image not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Payment method image updated successfully.',
            'data' => [
                'id' => $fileUsage->id,
            ],
        ]);
    }

    public function destroy(string $id, string $fileUsageId): JsonResponse
    {
        $deleted = $this->paymentMethodImageService->delete($id, $fileUsageId);

        if (! $deleted) {
            return response()->json([
                'message' => 'Payment method image not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Payment method image deleted successfully.',
        ]);
    }
}

