<?php

declare(strict_types=1);

namespace App\Domains\Faq\Controllers;

use App\Domains\Faq\DTOs\FaqListData;
use App\Domains\Faq\Models\Faq;
use App\Domains\Faq\Requests\SaveFaqRequest;
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

    public function save(SaveFaqRequest $request): JsonResponse
    {
        $data = $request->validated();

        $faq = isset($data['id'])
            ? Faq::query()->findOrFail((int) $data['id'])
            : new Faq();

        $type = (string) $data['type'];
        $typeId = $data['type_id'] ?? null;

        $faq->type = $type === 'general' ? null : $type;
        $faq->type_id = $type === 'general' ? null : $typeId;
        $faq->question = (string) $data['question'];
        $faq->answer = isset($data['answer']) ? (string) $data['answer'] : '';
        $faq->save();

        $faq->load(['brand', 'category', 'product']);

        return response()->json([
            'message' => 'FAQ saved successfully.',
            'data' => new FaqResource($faq),
            'success' => true,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $faq = Faq::query()->findOrFail((int) $id);
        $faq->delete();

        return response()->json([
            'message' => 'FAQ deleted successfully.',
            'success' => true,
        ]);
    }
}
