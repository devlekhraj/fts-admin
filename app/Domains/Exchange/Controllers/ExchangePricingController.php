<?php

declare(strict_types=1);

namespace App\Domains\Exchange\Controllers;

use App\Domains\Exchange\Models\ExchangeDamageRule;
use App\Domains\Exchange\Models\ExchangeValuation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
        $row->effective_date = now()->toDateString();
        $row->save();

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
        $row->fill($data)->save();

        return response()->json(['success' => true, 'message' => 'Condition saved.', 'data' => $this->rule($row)]);
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
