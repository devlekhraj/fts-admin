<?php

declare(strict_types=1);

namespace App\Domains\Faq\Controllers;

use App\Domains\Faq\DTOs\FaqListData;
use App\Domains\Faq\Resources\FaqResource;
use App\Domains\Faq\Services\FaqService;
use Illuminate\Routing\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class FaqController extends Controller
{
    public function __construct(private readonly FaqService $faqService) {}

    public function index(Request $request): JsonResponse
    {
        $result = $this->faqService->list(FaqListData::fromArray($request->query()));

        return response()->json([
            'data' => FaqResource::collection($result['items']),
            'meta' => $result['meta'],
        ]);
    }
}
