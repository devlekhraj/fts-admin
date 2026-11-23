<?php

declare(strict_types=1);

namespace App\Domains\Exchange\Controllers;

use App\Domains\Exchange\Models\ExchangeRequest;
use App\Domains\Exchange\Models\ExchangeRequestLog;
use App\Domains\Exchange\Services\ExchangeNotifier;
use App\Domains\Exchange\Support\ImeiValidator;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class ExchangeRequestController extends Controller
{
    /** Same cap the customer estimate uses. */
    private const MAX_DEDUCTION_PERCENT = 70;

    public function __construct(private readonly ExchangeNotifier $notifier) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            // One status, or several separated by commas (the "Pending" tab covers pending and under review).
            'status' => ['nullable', 'string', 'max:80'],
            'device_type' => ['nullable', Rule::in(['phone', 'laptop'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $query = ExchangeRequest::query()->select(ExchangeRequest::READABLE)->latest('id');

        if (! empty($validated['status'])) {
            $statuses = array_values(array_filter(array_map('trim', explode(',', $validated['status']))));
            if ($statuses === [] || array_diff($statuses, ExchangeRequest::STATUSES) !== []) {
                return $this->fail('Unknown status.', ['status' => ['Choose a valid status.']]);
            }
            $query->whereIn('status', $statuses);
        }

        // A phone has a 15-digit IMEI; anything else saved in that field is a laptop's serial number.
        if (($validated['device_type'] ?? null) === 'phone') {
            $query->whereRaw("serial_number REGEXP '^[0-9]{15}$'");
        } elseif (($validated['device_type'] ?? null) === 'laptop') {
            $query->whereRaw("serial_number NOT REGEXP '^[0-9]{15}$'");
        }

        if (! empty($validated['date_from'])) {
            $query->where('created_at', '>=', $validated['date_from'] . ' 00:00:00');
        }
        if (! empty($validated['date_to'])) {
            $query->where('created_at', '<=', $validated['date_to'] . ' 23:59:59');
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
                'rule_version' => data_get($row->estimate_breakdown, 'rule_version'),
                'flags' => (array) data_get($row->estimate_breakdown, 'flags', []),
                'approval_requires' => $this->approvalRequirements($row),
                'logs' => $row->logs->map(fn (ExchangeRequestLog $l) => [
                    'id' => $l->id,
                    'from_status' => $l->from_status,
                    'to_status' => $l->to_status,
                    'changed_by_type' => $l->changed_by_type,
                    'note' => $l->note,
                    'meta' => $l->meta,
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
            'inspection_note' => ['nullable', 'string', 'max:2000'],
            'override_reason' => ['nullable', 'string', 'max:1000'],
            'cap_override_reason' => ['nullable', 'string', 'max:1000'],
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

            // Approving a phone that needs a manual check, is on hold, or is offered below the 70% cap needs the
            // admin's written record (this is what the audit log keeps).
            if ($to === ExchangeRequest::STATUS_APPROVED) {
                $problem = $this->approvalProblem($row, $validated);
                if ($problem !== null) {
                    return $this->fail($problem[0], $problem[1]);
                }
            }

            $this->recordTransition($row, $to, $validated, $actor);

            return response()->json(['success' => true, 'message' => 'Request updated.', 'data' => ['id' => $row->id, 'status' => $row->status]]);
        });

        // Tell the customer by email once the change is saved. A failed email never undoes the decision.
        if ($response->getStatusCode() === 200) {
            $this->notifier->notifyCustomerOfDecision($id, $to);
        }

        return $response;
    }

    /** Saves one status change and writes its audit log entry. The caller has already checked the transition is allowed. */
    private function recordTransition(ExchangeRequest $row, string $to, array $input, mixed $actor): void
    {
        $from = $row->status;
        $row->status = $to;
        if (array_key_exists('admin_offer', $input) && $input['admin_offer'] !== null) {
            $row->admin_offer = $input['admin_offer'];
        }
        if (isset($input['admin_note'])) {
            $row->admin_note = $input['admin_note'];
        }
        if (isset($input['customer_note'])) {
            $row->customer_note = $input['customer_note'];
        }
        $row->save();

        ExchangeRequestLog::create([
            'exchange_request_id' => $row->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => $actor?->getAuthIdentifier(),
            'changed_by_type' => 'admin',
            'note' => $input['admin_note'] ?? ($input['customer_note'] ?? null),
            'meta' => $this->auditSnapshot($row, $to, $input, $actor),
            'created_at' => now(),
        ]);
    }

    /**
     * Staff enter a request for a customer who is at the counter or on the phone. It starts as pending and is always
     * marked for an in-person check, because nothing here was calculated from the price list.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s\-]{5,28}$/'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'device_type' => ['required', Rule::in(['phone', 'laptop'])],
            'device_name' => ['required', 'string', 'max:255'],
            'serial_number' => ['required', 'string', 'max:60'],
            'estimated_value' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'target_name' => ['nullable', 'string', 'max:255'],
            'target_price' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ], [
            'customer_phone.regex' => 'Enter a valid phone number.',
            'serial_number.required' => 'Enter the IMEI or serial number of the device.',
        ]);

        $serial = ImeiValidator::normalize($validated['serial_number']);
        if ($error = ImeiValidator::checkFor($serial, $validated['device_type'] === 'laptop')) {
            return $this->fail($error, ['serial_number' => [$error]]);
        }

        $open = ExchangeRequest::query()->where('serial_number', $serial)
            ->whereIn('status', [ExchangeRequest::STATUS_PENDING, ExchangeRequest::STATUS_UNDER_REVIEW, ExchangeRequest::STATUS_APPROVED])
            ->exists();
        if ($open) {
            $message = 'An exchange request for this device is already open.';

            return $this->fail($message, ['serial_number' => [$message]]);
        }

        $actor = auth('admin_api')->user();
        $adminName = is_object($actor) && isset($actor->name) ? (string) $actor->name : 'staff';

        $row = DB::transaction(function () use ($validated, $serial, $actor, $adminName) {
            do {
                $reference = 'EX' . now()->format('ymd') . strtoupper(Str::random(4));
            } while (ExchangeRequest::query()->where('reference', $reference)->exists());

            $row = new ExchangeRequest();
            $row->forceFill([
                'reference' => $reference,
                'user_id' => $validated['user_id'] ?? null,
                'status' => ExchangeRequest::STATUS_PENDING,
                'customer_name' => trim($validated['customer_name']),
                'customer_phone' => trim($validated['customer_phone']),
                'device_name' => trim($validated['device_name']),
                'serial_number' => $serial,
                'customer_estimate' => (float) $validated['estimated_value'],
                'estimate_breakdown' => [
                    'needs_manual_review' => true,
                    'hold' => false,
                    'flags' => ['Entered by staff (' . $adminName . ') in the admin: no automatic valuation was done. Check the device in person.'],
                    'created_by' => 'admin',
                ],
                'target_name' => isset($validated['target_name']) ? trim($validated['target_name']) : null,
                'target_price' => $validated['target_price'] ?? null,
                'admin_note' => isset($validated['admin_note']) ? trim($validated['admin_note']) : null,
            ])->save();

            ExchangeRequestLog::create([
                'exchange_request_id' => $row->id,
                'from_status' => null,
                'to_status' => ExchangeRequest::STATUS_PENDING,
                'changed_by' => $actor?->getAuthIdentifier(),
                'changed_by_type' => 'admin',
                'note' => $row->admin_note ?? 'Request entered by staff',
                'meta' => $this->auditSnapshot($row, ExchangeRequest::STATUS_PENDING, [], $actor),
                'created_at' => now(),
            ]);

            return $row;
        });

        return response()->json([
            'success' => true,
            'message' => 'Exchange request ' . $row->reference . ' created.',
            'data' => ['id' => $row->id, 'reference' => $row->reference],
        ], 201);
    }

    /**
     * The same change for several requests at once. Only moves that need no per-request decision are allowed:
     * approving is left out on purpose (it needs the offer and the inspection note of each request).
     * A request that cannot make the move is skipped and reported, the others still go through.
     */
    public function bulkStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:50'],
            'ids.*' => ['integer', 'distinct'],
            'status' => ['required', Rule::in([ExchangeRequest::STATUS_UNDER_REVIEW, ExchangeRequest::STATUS_REJECTED, ExchangeRequest::STATUS_COMPLETED])],
            'customer_note' => ['nullable', 'string', 'max:1000'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $to = $validated['status'];
        if ($to === ExchangeRequest::STATUS_REJECTED && trim((string) ($validated['customer_note'] ?? '')) === '') {
            return $this->fail('Write the reason the customer will see.', ['customer_note' => ['The reason is required.']]);
        }

        $actor = auth('admin_api')->user();
        $updated = [];
        $skipped = [];

        foreach ($validated['ids'] as $id) {
            $result = DB::transaction(function () use ($id, $to, $validated, $actor) {
                $row = ExchangeRequest::query()->select(ExchangeRequest::READABLE)->lockForUpdate()->find($id);
                if (! $row) {
                    return ['id' => $id, 'reference' => null, 'reason' => 'Request not found.'];
                }
                if (! in_array($to, ExchangeRequest::TRANSITIONS[$row->status] ?? [], true)) {
                    return ['id' => $id, 'reference' => $row->reference, 'reason' => "A {$row->status} request cannot be moved to {$to}."];
                }

                $this->recordTransition($row, $to, $validated, $actor);

                return null;
            });

            if ($result === null) {
                $updated[] = $id;
                $this->notifier->notifyCustomerOfDecision($id, $to);
            } else {
                $skipped[] = $result;
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($updated) . ' updated, ' . count($skipped) . ' skipped.',
            'data' => ['updated' => $updated, 'skipped' => $skipped],
        ]);
    }

    /** What an admin must write down before this request can be approved. */
    private function approvalRequirements(ExchangeRequest $r): array
    {
        $breakdown = $r->estimate_breakdown;
        $base = data_get($breakdown, 'base_value');

        return [
            'inspection_note' => (bool) data_get($breakdown, 'needs_manual_review', true),
            'override_reason' => (bool) data_get($breakdown, 'hold', false),
            // An offer below this amount takes off more than the 70% cap allows.
            'cap_floor' => is_numeric($base) && $base > 0 ? (int) round($base * (1 - self::MAX_DEDUCTION_PERCENT / 100)) : null,
        ];
    }

    /** @return array{0: string, 1: array<string, array<int, string>>}|null */
    private function approvalProblem(ExchangeRequest $r, array $input): ?array
    {
        $need = $this->approvalRequirements($r);
        $text = fn (string $key) => trim((string) ($input[$key] ?? ''));

        if ($need['inspection_note'] && mb_strlen($text('inspection_note')) < 5) {
            return ['Write what you found when you checked the phone before approving.', ['inspection_note' => ['The inspection note is required.']]];
        }
        if ($need['override_reason'] && mb_strlen($text('override_reason')) < 10) {
            return ['This request is on hold. Record who authorised approving it and why.', ['override_reason' => ['The hold override reason is required.']]];
        }
        $offer = (float) ($input['admin_offer'] ?? 0);
        if ($need['cap_floor'] !== null && $offer < $need['cap_floor'] && mb_strlen($text('cap_override_reason')) < 10) {
            return [
                'This offer takes off more than 70% of the phone value. Record the authorised reason for going past the cap.',
                ['cap_override_reason' => ['The cap override reason is required.']],
            ];
        }

        return null;
    }

    /** The audit record of one decision: values as they were, who decided, and under which price and rule version. */
    private function auditSnapshot(ExchangeRequest $r, string $to, array $input, mixed $actor): array
    {
        $b = $r->estimate_breakdown;

        return [
            'status' => $to,
            'admin_id' => $actor?->getAuthIdentifier(),
            'admin_name' => is_object($actor) && isset($actor->name) ? (string) $actor->name : null,
            'rule_version' => data_get($b, 'rule_version'),
            'base_value' => data_get($b, 'base_value'),
            'deductions' => data_get($b, 'deductions'),
            'calculated_value' => (float) $r->customer_estimate,
            'final_offer' => $r->admin_offer !== null ? (float) $r->admin_offer : null,
            'needs_manual_review' => (bool) data_get($b, 'needs_manual_review', true),
            'hold' => (bool) data_get($b, 'hold', false),
            'flags' => (array) data_get($b, 'flags', []),
            'inspection_note' => trim((string) ($input['inspection_note'] ?? '')) ?: null,
            'override_reason' => trim((string) ($input['override_reason'] ?? '')) ?: null,
            'cap_override_reason' => trim((string) ($input['cap_override_reason'] ?? '')) ?: null,
        ];
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
            'serial_number' => $r->serial_number,
            'device_type' => preg_match('/^[0-9]{15}$/', (string) $r->serial_number) ? 'phone' : 'laptop',
            'target_name' => $r->target_name,
            'customer_estimate' => $r->customer_estimate,
            'admin_offer' => $r->admin_offer,
            'needs_manual_review' => (bool) data_get($r->estimate_breakdown, 'needs_manual_review', true),
            'hold' => (bool) data_get($r->estimate_breakdown, 'hold', false),
            'created_at' => $r->created_at,
        ];
    }

    private function fail(string $message, array $errors = []): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, 'errors' => $errors], 422);
    }
}
