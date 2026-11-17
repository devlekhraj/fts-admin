<?php

declare(strict_types=1);

namespace App\Domains\Exchange\Controllers;

use App\Domains\Exchange\Models\ExchangeDamageRule;
use App\Domains\Exchange\Models\ExchangeValuation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Prices and condition deductions used by the customer's trade-in estimate. */
final class ExchangePricingController extends Controller
{
    public function valuations(): JsonResponse
    {
        $rows = ExchangeValuation::query()->orderBy('sort_order')->orderBy('id')->get();

        return response()->json([
            'data' => $rows->map(fn (ExchangeValuation $v) => $this->valuation($v))->all(),
        ]);
    }

    public function storeValuation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'brand' => ['nullable', 'string', 'max:50'],
            'model' => ['required', 'string', 'max:100'],
            'storage_gb' => ['required', 'integer', 'min:1', 'max:8192'],
            'base_value' => ['required', 'integer', 'min:0', 'max:10000000'],
            'confidence' => ['nullable', Rule::in(ExchangeValuation::CONFIDENCE)],
            'evidence_note' => ['nullable', 'string', 'max:255'],
        ]);

        $brand = $data['brand'] ?? 'Apple';
        if (ExchangeValuation::query()->where(['brand' => $brand, 'model' => $data['model'], 'storage_gb' => $data['storage_gb']])->exists()) {
            return response()->json(['success' => false, 'message' => 'This model and storage already has a price. Edit it instead.'], 422);
        }

        $row = ExchangeValuation::create([
            'brand' => $brand,
            'model' => $data['model'],
            'storage_gb' => $data['storage_gb'],
            'base_value' => $data['base_value'],
            'confidence' => $data['confidence'] ?? 'verified',
            'evidence_note' => $data['evidence_note'] ?? null,
            'effective_date' => now()->toDateString(),
            'is_active' => true,
            'sort_order' => (int) ExchangeValuation::query()->max('sort_order') + 1,
        ]);

        $this->recordAudit('valuation', $row->id, "Added {$row->model} {$row->storage_gb} GB", [
            'base_value' => ['from' => null, 'to' => $row->base_value],
            'confidence' => ['from' => null, 'to' => $row->confidence],
        ]);

        return response()->json(['success' => true, 'message' => 'Price added.', 'data' => $this->valuation($row)]);
    }

    public function updateValuation(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'base_value' => ['required', 'integer', 'min:0', 'max:10000000'],
            'admin_override' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'confidence' => ['required', Rule::in(ExchangeValuation::CONFIDENCE)],
            'evidence_note' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        $row = ExchangeValuation::query()->findOrFail($id);
        $row->fill($data);
        $changes = $this->diff($row, ['base_value', 'admin_override', 'confidence', 'evidence_note', 'is_active']);
        $row->effective_date = now()->toDateString();
        $row->save();

        if ($changes !== []) {
            $this->recordAudit('valuation', $row->id, "Changed {$row->model} {$row->storage_gb} GB", $changes);
        }

        return response()->json(['success' => true, 'message' => 'Price saved.', 'data' => $this->valuation($row)]);
    }

    public function rules(): JsonResponse
    {
        $rows = ExchangeDamageRule::query()->orderBy('sort_order')->orderBy('id')->get();

        return response()->json(['data' => $rows->map(fn (ExchangeDamageRule $r) => $this->rule($r))->all()]);
    }

    public function updateRule(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'deduction_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'guidance' => ['nullable', 'string', 'max:255'],
            'requires_manual_review' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);

        $row = ExchangeDamageRule::query()->findOrFail($id);
        $row->fill($data);
        $changes = $this->diff($row, ['deduction_percent', 'guidance', 'requires_manual_review', 'is_active']);
        $row->save();

        if ($changes !== []) {
            $this->recordAudit('rule', $row->id, 'Changed ' . $row->label, $changes);
        }

        return response()->json(['success' => true, 'message' => 'Condition saved.', 'data' => $this->rule($row)]);
    }

    /** Who changed which price or deduction, newest first. */
    public function auditLog(Request $request): JsonResponse
    {
        $rows = DB::table('exchange_pricing_audit')->orderByDesc('id')->limit(200)->get();

        return response()->json(['data' => $rows->map(fn ($r) => [
            'id' => $r->id,
            'entity' => $r->entity,
            'entity_id' => $r->entity_id,
            'summary' => $r->summary,
            'changes' => json_decode((string) $r->changes, true) ?: [],
            'admin_name' => $r->admin_name,
            'created_at' => $r->created_at,
        ])->all()]);
    }

    /** @param array<int, string> $fields */
    private function diff($model, array $fields): array
    {
        $changes = [];
        foreach ($fields as $field) {
            if ($model->isDirty($field)) {
                $changes[$field] = ['from' => $model->getOriginal($field), 'to' => $model->getAttribute($field)];
            }
        }

        return $changes;
    }

    private function recordAudit(string $entity, int $id, string $summary, array $changes): void
    {
        $actor = auth('admin_api')->user();

        DB::table('exchange_pricing_audit')->insert([
            'entity' => $entity,
            'entity_id' => $id,
            'summary' => mb_substr($summary, 0, 255),
            'changes' => json_encode($changes, JSON_UNESCAPED_UNICODE),
            'admin_id' => $actor?->getAuthIdentifier(),
            'admin_name' => is_object($actor) && isset($actor->name) ? mb_substr((string) $actor->name, 0, 120) : null,
            'created_at' => now(),
        ]);
    }

    private function valuation(ExchangeValuation $v): array
    {
        return [
            'id' => $v->id,
            'brand' => $v->brand,
            'model' => $v->model,
            'storage_gb' => $v->storage_gb,
            'base_value' => $v->base_value,
            'admin_override' => $v->admin_override,
            'final_value' => $v->final_value,
            'confidence' => $v->confidence,
            'evidence_note' => $v->evidence_note,
            'effective_date' => $v->effective_date?->format('Y-m-d'),
            'is_active' => $v->is_active,
        ];
    }

    private function rule(ExchangeDamageRule $r): array
    {
        return [
            'id' => $r->id,
            'category' => $r->category,
            'label' => $r->label,
            'deduction_percent' => $r->deduction_percent,
            'guidance' => $r->guidance,
            'requires_manual_review' => $r->requires_manual_review,
            'is_active' => $r->is_active,
        ];
    }
}
