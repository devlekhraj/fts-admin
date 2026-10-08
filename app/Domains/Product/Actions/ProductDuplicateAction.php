<?php

declare(strict_types=1);

namespace App\Domains\Product\Actions;

use App\Domains\Faq\Models\Faq;
use App\Domains\Product\Models\Product;
use App\Domains\Product\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Creates an editable copy of a product: its fields, categories, variants,
 * images, FAQs and free-gift links.
 *
 * The copy is created hidden (status off) so a half-edited duplicate never
 * shows on the website. Images are shared with the original through new
 * file_usages rows (removing an image from one product only removes that link),
 * and sales data (campaign discounts, reviews, carts, orders) is not copied.
 */
final class ProductDuplicateAction
{
    private const NAME_MAX = 500;

    private const SKU_MAX = 30;

    public function execute(Product $source): Product
    {
        return DB::transaction(function () use ($source): Product {
            $source->loadMissing(['categories', 'variants', 'giftItems']);

            $copy = $source->replicate();
            $copy->name = $this->copyName((string) $source->name);
            $copy->slug = $this->uniqueSlug((string) $source->slug);
            $copy->sku = $this->uniqueSku((string) $source->sku);
            $copy->status = false;
            $copy->is_featured = false;
            $copy->save();

            $categoryIds = $source->categories->pluck('id')->all();
            if ($categoryIds !== []) {
                $copy->categories()->sync($categoryIds);
            }

            foreach ($source->giftItems as $gift) {
                $copy->giftItems()->attach($gift->id, ['is_active' => $gift->pivot->is_active]);
            }

            $this->copyFileUsages('products', (int) $source->id, (int) $copy->id);

            foreach ($source->variants as $variant) {
                /** @var ProductVariant $newVariant */
                $newVariant = $variant->replicate();
                $newVariant->product_id = $copy->id;
                $newVariant->save();

                $this->copyFileUsages('product_variants', (int) $variant->id, (int) $newVariant->id);
            }

            $faqs = Faq::query()
                ->where('type_id', $source->id)
                ->whereIn('type', ['product', 'products', Product::class])
                ->get();
            foreach ($faqs as $faq) {
                Faq::query()->create([
                    'type' => $faq->type,
                    'type_id' => $copy->id,
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                ]);
            }

            return $copy;
        });
    }

    private function copyFileUsages(string $usageType, int $fromId, int $toId): void
    {
        $now = now();

        $rows = DB::table('file_usages')
            ->where('usage_type', $usageType)
            ->where('usage_id', $fromId)
            ->orderBy('id')
            ->get()
            ->map(fn ($usage) => [
                'file_id' => $usage->file_id,
                'usage_type' => $usageType,
                'usage_id' => $toId,
                'title' => $usage->title,
                'alt_text' => $usage->alt_text,
                'meta' => $usage->meta,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        if ($rows !== []) {
            DB::table('file_usages')->insert($rows);
        }
    }

    private function copyName(string $name): string
    {
        return mb_substr($name, 0, self::NAME_MAX - 7) . ' (Copy)';
    }

    private function uniqueSlug(string $slug): string
    {
        $base = mb_substr($slug, 0, self::NAME_MAX - 12) . '-copy';
        $candidate = $base;
        $suffix = 2;

        // Slugs are only unique by convention (no DB index), and soft-deleted rows still count.
        while (Product::withTrashed()->where('slug', $candidate)->exists()) {
            $candidate = $base . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function uniqueSku(string $sku): string
    {
        $prefix = mb_substr((string) preg_replace('/[^A-Za-z0-9-]/', '', $sku), 0, self::SKU_MAX - 9);

        do {
            $candidate = $prefix . '-C' . strtoupper(Str::random(6));
        } while (Product::withTrashed()->where('sku', $candidate)->exists());

        return $candidate;
    }
}
