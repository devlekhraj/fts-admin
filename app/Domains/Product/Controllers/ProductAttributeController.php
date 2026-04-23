<?php

declare(strict_types=1);

namespace App\Domains\Product\Controllers;

use App\Domains\Product\Models\AttributeClass;
use App\Domains\Product\Models\ProductAttribute;
use App\Domains\Product\Requests\UpdateProductAttributeItemRequest;
use App\Domains\Product\Requests\UpdateProductAttributeValuesRequest;
use App\Domains\Product\Resources\AttributeResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductAttributeController extends Controller
{
    public function attributeList(Request $request): JsonResponse
    {
        $attributes = AttributeClass::select('id', 'name', 'created_at')
            ->withCount('attributes')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => AttributeResource::collection($attributes)->resolve(),
            'success' => true,
        ], 200);
    }

    public function attributeDetail(Request $request, string $id): JsonResponse
    {
        $attributeClass = AttributeClass::query()
            ->withCount('attributes')
            ->with(['attributes'])
            ->findOrFail($id);

        return response()->json((new AttributeResource($attributeClass))->resolve(), 200);
    }

    public function updateAttributeValues(UpdateProductAttributeValuesRequest $request, string $id, string $attributeId): JsonResponse
    {
        $attributeClass = AttributeClass::query()->findOrFail($id);

        $attribute = ProductAttribute::query()
            ->where('class_id', $attributeClass->id)
            ->findOrFail($attributeId);

        $values = collect((array) $request->validated('values', []))
            ->map(static fn ($value): string => trim((string) $value))
            ->filter(static fn (string $value): bool => $value !== '')
            ->values();

        if (strtolower((string) $attribute->type) === 'text') {
            $values = $values->take(1)->values();
        }

        $attribute->values = $values->all();
        $attribute->save();

        return response()->json([
            'message' => 'Attribute values updated successfully.',
            'data' => [
                'id' => $attribute->id,
                'values' => is_array($attribute->values) ? $attribute->values : [],
            ],
        ], 200);
    }

    public function updateAttributeItem(UpdateProductAttributeItemRequest $request, string $id, string $attributeId): JsonResponse
    {
        $attributeClass = AttributeClass::query()->findOrFail($id);

        $attribute = ProductAttribute::query()
            ->where('class_id', $attributeClass->id)
            ->findOrFail($attributeId);

        $validated = $request->validated();
        $type = strtolower(trim((string) ($validated['type'] ?? '')));

        $attribute->name = trim((string) $validated['name']);
        $attribute->type = $type;
        $attribute->use_for_variant = (bool) ($validated['use_for_variant'] ?? false);
        $attribute->use_in_filter = (bool) ($validated['use_in_filter'] ?? false);
        $attribute->save();

        return response()->json([
            'message' => 'Attribute item updated successfully.',
            'data' => [
                'id' => $attribute->id,
                'name' => $attribute->name,
                'type' => $attribute->type,
                'use_for_variant' => (bool) $attribute->use_for_variant,
                'use_in_filter' => (bool) $attribute->use_in_filter,
                'values' => is_array($attribute->values) ? $attribute->values : [],
            ],
        ], 200);
    }
}
