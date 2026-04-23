<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Controllers;

use App\Domains\EmiRequest\Requests\GenerateEmiApplicationPdfRequest;
use App\Domains\EmiRequest\Resources\EmiApplicationResource;
use App\Domains\EmiRequest\Services\EmiApplicationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmiApplicationController extends Controller
{
    public function __construct(
        private readonly EmiApplicationService $emiApplicationService,
    ) {}

    public function generatePdf(GenerateEmiApplicationPdfRequest $request, string $id): JsonResponse
    {
        $result = $this->emiApplicationService->generatePdf($request, $id);

        return response()->json($result['payload'], $result['status']);
    }

    public function index(Request $request, string $id): JsonResponse
    {
        $items = $this->emiApplicationService->list($id);

        return response()->json([
            'data' => EmiApplicationResource::collection($items),
        ]);
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'application_id' => ['nullable', 'integer'],
            'request_id' => ['nullable', 'integer'],
            'subject' => ['required', 'string', 'max:255'],
            'to' => ['required', 'string'],
            'cc' => ['nullable', 'string'],
            'bcc' => ['nullable', 'string'],
            'file_name' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $this->emiApplicationService->approve($validated, $id);

        return response()->json($result['payload'], $result['status']);
    }
}

