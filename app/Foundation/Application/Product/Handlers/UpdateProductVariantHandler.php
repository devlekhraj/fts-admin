<?php

declare(strict_types=1);

namespace App\Foundation\Application\Product\Handlers;

use App\Foundation\Application\Product\Commands\UpdateProductVariantCommand;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\ProductVariantModel;
use App\Foundation\Shared\Domain\Exceptions\FieldValidationException;

final class UpdateProductVariantHandler
{
    /**
     * @return array{message: string, data: array<string, mixed>, success: bool}
     */
    public function handle(UpdateProductVariantCommand $cmd): array
    {
        $normalizedAttributes = $this->normalizeAttributes($cmd->attributes);

        if ($this->hasDuplicateAttributes($cmd->productId, $cmd->variantId, $normalizedAttributes)) {
            throw new FieldValidationException('attributes', 'Variant with same attributes already exists.');
        }

        $variant = ProductVariantModel::query()
            ->where('id', $cmd->variantId)
            ->where('product_id', $cmd->productId)
            ->firstOrFail();

        $variant->update([
            'price' => $cmd->price,
            'quantity' => $cmd->quantity,
            'attributes' => $normalizedAttributes,
        ]);

        $variant->refresh();

        return [
            'message' => 'Variant updated successfully.',
            'data' => [
                'id' => $variant->id,
                'product_id' => $variant->product_id,
                'price' => is_numeric($variant->price) ? (float) $variant->price : null,
                'quantity' => is_numeric($variant->quantity) ? (int) $variant->quantity : null,
                'attributes' => is_array($variant->attributes) ? $variant->attributes : [],
                'images' => [],
                'created_at' => $variant->created_at,
                'updated_at' => $variant->updated_at,
            ],
            'success' => true,
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    private function normalizeAttributes(array $attributes): array
    {
        $normalized = [];
        foreach ($attributes as $key => $value) {
            $normalizedKey = trim((string) $key);
            if ($normalizedKey === '') {
                continue;
            }
            $normalized[$normalizedKey] = $this->normalizeValue($value);
        }
        ksort($normalized);
        return $normalized;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if (is_string($value)) {
            return trim($value);
        }
        if (is_array($value)) {
            $isAssoc = array_keys($value) !== range(0, count($value) - 1);
            if ($isAssoc) {
                $next = [];
                foreach ($value as $k => $v) {
                    $next[trim((string) $k)] = $this->normalizeValue($v);
                }
                ksort($next);
                return $next;
            }
            return array_map(fn ($item) => $this->normalizeValue($item), $value);
        }
        return $value;
    }

    /**
     * @param array<string, mixed> $normalizedAttributes
     */
    private function hasDuplicateAttributes(int $productId, int $variantId, array $normalizedAttributes): bool
    {
        $target = json_encode($normalizedAttributes);
        if ($target === false) {
            return false;
        }

        $variants = ProductVariantModel::query()
            ->where('product_id', $productId)
            ->where('id', '!=', $variantId)
            ->get(['attributes']);

        foreach ($variants as $variant) {
            $existing = is_array($variant->attributes) ? $variant->attributes : [];
            $existingEncoded = json_encode($this->normalizeAttributes($existing));
            if ($existingEncoded !== false && $existingEncoded === $target) {
                return true;
            }
        }

        return false;
    }
}
