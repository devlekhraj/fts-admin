<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Controllers;

use App\Domains\EmiRequest\DTOs\EmiRequestListData;
use App\Domains\EmiRequest\Mail\EmiApprovedMail;
use App\Domains\EmiRequest\Mail\EmiRejectedMail;
use App\Domains\EmiRequest\Models\EmiRequest;
use App\Domains\EmiRequest\Requests\ApproveEmiRequestRequest;
use App\Domains\EmiRequest\Requests\EmiRequestRejectRequest;
use App\Domains\EmiRequest\Requests\StoreEmiRequestRequest;
use App\Domains\EmiRequest\Requests\UpdateEmiRequestRequest;
use App\Domains\EmiRequest\Resources\EmiRequestListResource;
use App\Domains\EmiRequest\Services\EmiRequestService;
use App\Domains\Shared\Helpers\EmiHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class EmiRequestController extends Controller
{
    public function __construct(
        private readonly EmiRequestService $emiRequestService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = EmiRequestListData::fromArray($request->query());
        $paginator = $this->emiRequestService->list($data);

        return response()->json([
            'data' => EmiRequestListResource::collection($paginator->items()),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $record = $this->emiRequestService->show($id);

        return response()->json(new EmiRequestListResource($record));
    }

    public function store(StoreEmiRequestRequest $request): JsonResponse
    {
        // TODO: Create EMI request.
        return response()->json([], 201);
    }

    public function update(string $id, UpdateEmiRequestRequest $request): JsonResponse
    {
        // TODO: Update EMI request.
        return response()->json(['id' => $id]);
    }

    public function approve(string $id, ApproveEmiRequestRequest $request): JsonResponse
    {
        $emiRequest = EmiRequest::find($id);
        if (! $emiRequest) {
            return response()->json(['success' => false, 'message' => 'EMI request not found.'], 404);
        }


        $emiHelper = new EmiHelper();
        try {
            $pdfResult = $emiHelper->generateEmiPdf($emiRequest);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        $emiRequest->update([
            'status' => EmiRequest::STATUS_APPROVED,
        ]);

        $actor = auth('admin_api')->user();
        $actorName = is_object($actor) && isset($actor->name) ? (string) $actor->name : 'admin';

        $emiRequest->logActivity(
            action: 'emi_request_approved',
            label: 'EMI request approved',
            description: "EMI request was approved by {$actorName}.",
            actor: $actor
        );

        $toEmail = config('app.env') === 'production' ? $emiRequest->email : "devlekhraj88@gmail.com";

        dd([
            'default_mailer' => config('mail.default'),
            'from' => config('mail.from'),
            'mailer_config' => config('mail.mailers.' . config('mail.default')),
            'env' => [
                'MAIL_MAILER' => env('MAIL_MAILER'),
                'MAIL_HOST' => env('MAIL_HOST'),
                'MAIL_PORT' => env('MAIL_PORT'),
                'MAIL_USERNAME' => env('MAIL_USERNAME'),
                'MAIL_PASSWORD' => env('MAIL_PASSWORD'),
            ],
        ]);

        try {
            Mail::to($toEmail)->send(new EmiApprovedMail($emiRequest, $pdfResult));
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Emi requests successfully approved.',
            'quotation_pdf' => $pdfResult,
        ]);
    }
    public function reject(string $id, EmiRequestRejectRequest $request): JsonResponse
    {
        $emiRequest = EmiRequest::find($id);
        if (! $emiRequest) {
            return response()->json(['success' => false, 'message' => 'EMI request not found.'], 404);
        }

        $reason = (string) $request->validated('reason');

        $emiRequest->update([
            'status' => EmiRequest::STATUS_CANCELLED,
        ]);

        $actor = auth('admin_api')->user();
        $actorName = is_object($actor) && isset($actor->name) ? (string) $actor->name : 'admin';

        $emiRequest->logActivity(
            action: 'emi_request_rejected',
            label: 'EMI request rejected',
            description: "EMI request was rejected by {$actorName}.",
            actor: $actor,
            meta: ['reason' => $reason]
        );

        $toEmail = config('app.env') === 'production' ? $emiRequest->email : "devlekhraj88@gmail.com";

        try {
            Mail::to($toEmail)->send(new EmiRejectedMail($emiRequest, $reason));
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Emi request successfully rejected.',
        ]);
    }
}
