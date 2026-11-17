<?php

declare(strict_types=1);

namespace App\Domains\Exchange\Controllers;

use App\Domains\Exchange\Models\ExchangeRequest;
use App\Domains\Exchange\Models\ExchangeRequestLog;
use App\Domains\Exchange\Services\ExchangeNotifier;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class ExchangeRequestController extends Controller
{
    public function __construct(private readonly ExchangeNotifier $notifier) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'status' => ['nullable', Rule::in(ExchangeRequest::STATUSES)],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $query = ExchangeRequest::query()->select(ExchangeRequest::READABLE)->latest('id');

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (! empty($validated['search'])) {
            $term = '%' . addcslashes(trim($validated['search']), '%_\\') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('reference', 'like', $term)
                    ->orWhere('customer_name', 'like', $term)
                    ->orWhere('customer_phone', 'like', $term)
                    ->orWhere('device_name', 'like', $term)
                    ->orWhere('serial_number', 'like', $term);
            });
        }

        $page = $query->paginate((int) ($validated['per_page'] ?? 15));

        return response()->json([
            'data' => collect($page->items())->map(fn (ExchangeRequest $r) => $this->summary($r))->all(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
            'counts' => ExchangeRequest::query()->select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c', 'status'),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $row = ExchangeRequest::query()->select(ExchangeRequest::READABLE)->with('logs')->findOrFail($id);

        return response()->json([
            'data' => $this->summary($row) + [
                'serial_number' => $row->serial_number,
                'photo_url' => $row->device_photo_path ? MediaUrl::for((string) $row->device_photo_path) : null,
                'condition_answers' => $row->condition_answers,
                'estimate_breakdown' => $row->estimate_breakdown,
                'target_name' => $row->target_name,
                'target_price' => $row->target_price,
                'pickup_info' => $row->pickup_info,
                'is_satisfied' => $row->is_satisfied,
                'expected_amount' => $row->expected_amount,
                'satisfaction_reason' => $row->satisfaction_reason,
                'admin_note' => $row->admin_note,
                'customer_note' => $row->customer_note,
                'allowed_statuses' => ExchangeRequest::TRANSITIONS[$row->status] ?? [],
                'logs' => $row->logs->map(fn (ExchangeRequestLog $l) => [
                    'id' => $l->id,
                    'from_status' => $l->from_status,
                    'to_status' => $l->to_status,
                    'changed_by_type' => $l->changed_by_type,
                    'note' => $l->note,
                    'created_at' => $l->created_at,
                ])->all(),
            ],
        ]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(ExchangeRequest::STATUSES)],
            'admin_offer' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $to = $validated['status'];

        if ($to === ExchangeRequest::STATUS_APPROVED && ! isset($validated['admin_offer'])) {
            return $this->fail('Enter the offer amount before approving.', ['admin_offer' => ['The offer amount is required.']]);
        }
        if ($to === ExchangeRequest::STATUS_REJECTED && trim((string) ($validated['customer_note'] ?? '')) === '') {
            return $this->fail('Write the reason the customer will see.', ['customer_note' => ['The reason is required.']]);
        }

        $actor = auth('admin_api')->user();

        $response = DB::transaction(function () use ($id, $to, $validated, $actor) {
            $row = ExchangeRequest::query()->select(ExchangeRequest::READABLE)->lockForUpdate()->findOrFail($id);

            if (! in_array($to, ExchangeRequest::TRANSITIONS[$row->status] ?? [], true)) {
                return $this->fail("A {$row->status} request cannot be moved to {$to}.");
            }

            $from = $row->status;
            $row->status = $to;
            if (array_key_exists('admin_offer', $validated) && $validated['admin_offer'] !== null) {
                $row->admin_offer = $validated['admin_offer'];
            }
            if (isset($validated['admin_note'])) {
                $row->admin_note = $validated['admin_note'];
            }
            if (isset($validated['customer_note'])) {
                $row->customer_note = $validated['customer_note'];
            }
            $row->save();

            ExchangeRequestLog::create([
                'exchange_request_id' => $row->id,
                'from_status' => $from,
                'to_status' => $to,
                'changed_by' => $actor?->getAuthIdentifier(),
                'changed_by_type' => 'admin',
                'note' => $validated['admin_note'] ?? ($validated['customer_note'] ?? null),
                'created_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Request updated.', 'data' => ['id' => $row->id, 'status' => $row->status]]);
        });

        // Tell the customer by email once the change is saved. A failed email never undoes the decision.
        if ($response->getStatusCode() === 200) {
            $this->notifier->notifyCustomerOfDecision($id, $to);
        }

        return $response;
    }

    private function summary(ExchangeRequest $r): array
    {
        return [
            'id' => $r->id,
            'reference' => $r->reference,
            'status' => $r->status,
            'customer_name' => $r->customer_name,
            'customer_phone' => $r->customer_phone,
            'device_name' => $r->device_name,
            'customer_estimate' => $r->customer_estimate,
            'admin_offer' => $r->admin_offer,
            'needs_manual_review' => (bool) data_get($r->estimate_breakdown, 'needs_manual_review', true),
            'created_at' => $r->created_at,
        ];
    }

    private function fail(string $message, array $errors = []): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, 'errors' => $errors], 422);
    }
}
