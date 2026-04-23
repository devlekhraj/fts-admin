<?php

declare(strict_types=1);

namespace App\Domains\Customer\Controllers;

use App\Domains\Customer\DTOs\CustomerListData;
use App\Domains\Customer\Resources\CustomerResource;
use App\Domains\Customer\Services\CustomerService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customerService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $result = $this->customerService->list(CustomerListData::fromArray($request->query()));

        return response()->json([
            'data' => CustomerResource::collection($result['items']),
            'meta' => $result['meta'],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $customer = $this->customerService->detail($id);

        return response()->json([
            'data' => (new CustomerResource($customer)),
            'success' => true,
        ], 200);
    }
}

