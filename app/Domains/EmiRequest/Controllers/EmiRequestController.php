<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Controllers;

use App\Domains\EmiRequest\DTOs\EmiRequestListData;
use App\Domains\EmiRequest\Mail\EmiApprovedMail;
use App\Domains\EmiRequest\Mail\EmiCancelledMail;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $emiRequest = EmiRequest::find($id);
        if (! $emiRequest) {
            return response()->json(['success' => false, 'message' => 'EMI request not found.'], 404);
        }

        $validated = $request->validate([
            'status' => ['required', 'integer', 'in:0,1,2,3,4,5,6,7,8'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $emiRequest->update([
            'status' => (int) $validated['status'],
        ]);

        $actor = auth('admin_api')->user();
        $actorName = is_object($actor) && isset($actor->name) ? (string) $actor->name : 'admin';

        $statusCode = (int) $validated['status'];
        $statusLabel = EmiRequest::getStatusLabels()[$statusCode] ?? 'Updated';

        $label = $statusLabel;
        $description = "EMI request status updated to {$statusLabel} by {$actorName}.";

        if ($statusCode === EmiRequest::STATUS_PENDING) {
            $label = 'Pending';
            $description = 'EMI request is pending.';
        }
        if ($statusCode === EmiRequest::STATUS_PROCESSING) {
            $label = 'Processing';
            $description = 'EMI request is being verified.';
        }
        if ($statusCode === EmiRequest::STATUS_BANK_APPROVAL) {
            $label = 'Bank Approval';
            $description = 'EMI request sent for bank approval.';
        }
        if ($statusCode === EmiRequest::STATUS_APPROVED) {
            $label = 'Approved';
            $description = 'EMI request has been approved.';
        }
        if ($statusCode === EmiRequest::STATUS_DISPATCHED) {
            $label = 'Dispatched';
            $description = 'Product has been dispatched.';
        }
        if ($statusCode === EmiRequest::STATUS_DELIVERED) {
            $label = 'Delivered';
            $description = 'Product has been delivered.';
        }
        if ($statusCode === EmiRequest::STATUS_COMPLETED) {
            $label = 'Completed';
            $description = 'EMI request has been completed.';
        }
        if ($statusCode === EmiRequest::STATUS_FINISHED) {
            $label = 'Finished';
            $description = 'EMI request has been finished.';
        }
        if ($statusCode === EmiRequest::STATUS_CANCELLED) {
            $label = 'Cancelled';
            $description = 'EMI request has been cancelled.';
        }

        $emiRequest->logActivity(
            action: 'emi_processing',
            label: $label,
            description: $description,
            actor: $actor,
            meta: array_filter([
                'notes' => isset($validated['notes']) ? (string) $validated['notes'] : null,
            ], fn($v) => $v !== null && $v !== ''),
        );

        if ($statusCode === EmiRequest::STATUS_CANCELLED) {
            try {
                $toEmail = config('app.env') === 'production' ? $emiRequest->email : 'devlekhraj88@gmail.com';
                if (! empty($toEmail)) {
                    $timestamp = now()->timezone(config('app.timezone'))->format('Y-m-d H:i:s');
                    Mail::to($toEmail)->send(new EmiCancelledMail(
                        emiRequest: $emiRequest,
                        reason: $validated['notes'] ?? null,
                        cancelledAt: $timestamp,
                    ));
                }
            } catch (\Throwable $mailException) {
                Log::error('Failed to send EMI cancellation email.', [
                    'emi_request_id' => $emiRequest->id,
                    'email' => $emiRequest->email ?? null,
                    'error' => $mailException->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'status' => (int) $emiRequest->status,
            'status_label' => EmiRequest::getStatusLabels()[(int) $emiRequest->status] ?? 'Unknown',
        ]);
    }

    public function comment(Request $request, string $id): JsonResponse
    {
        $emiRequest = EmiRequest::find($id);
        if (! $emiRequest) {
            return response()->json(['success' => false, 'message' => 'EMI request not found.'], 404);
        }

        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:220'],
        ]);

        $actor = auth('admin_api')->user();

        $activity = $emiRequest->logActivity(
            action: 'emi_request_comment',
            label: 'Comment added',
            description: $validated['comment'],
            actor: $actor,
            meta: array_filter([
                'type' => 'comment',
            ], fn($v) => $v !== null && $v !== ''),
        );

        return response()->json([
            'success' => true,
            'activity_id' => $activity?->id,
        ]);
    }

    public function delete(string $id): JsonResponse
    {
        $emiRequest = EmiRequest::find($id);
        if (! $emiRequest) {
            return response()->json(['success' => false, 'message' => 'EMI request not found.'], 404);
        }

        $allowedStatuses = [EmiRequest::STATUS_PENDING, EmiRequest::STATUS_CANCELLED];
        if (! in_array((int) $emiRequest->status, $allowedStatuses, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending or cancelled EMI requests can be deleted.',
            ], 422);
        }

        $actor = auth('admin_api')->user();
        $actorName = is_object($actor) && isset($actor->name) ? (string) $actor->name : 'admin';
        $actorId = is_object($actor) && isset($actor->id) ? (int) $actor->id : null;


        try {
            DB::transaction(function () use ($emiRequest, $actorId, $actorName, $actor) {
                // emi_applications FK does not cascade, so clean up first.
                // DB::table('emi_applications')->where('emi_request_id', $emiRequest->id)->delete();

                // // file_usages has no FK to usage_id; remove the pivot rows.
                // DB::table('file_usages')
                //     ->where('usage_type', 'emi_requests')
                //     ->where('usage_id', $emiRequest->id)
                //     ->delete();

                $emiRequest->update([
                    'is_deleted' => true,
                    'deleted_at' => now(),
                    'deleted_by' => $actorId,
                ]);

                $emiRequest->logActivity(
                    action: 'emi_request_deleted',
                    label: 'EMI request deleted',
                    description: "EMI request was deleted by {$actorName}.",
                    actor: $actor
                );
            });
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'EMI request deleted successfully.',
        ]);
    }
}
