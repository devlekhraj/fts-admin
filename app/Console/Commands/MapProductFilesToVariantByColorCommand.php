<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MapProductFilesToVariantByColorCommand extends Command
{
    protected $signature = 'media:map-product-files-to-variants
        {--chunk=500 : Chunk size for processing}
        {--dry-run : Show what would be updated without writing changes}';

    protected $description = 'Map product-level file usages to product variants by normalized color match.';

    public function handle(): int
    {
        if (! DB::getSchemaBuilder()->hasTable('file_usages')) {
            $this->error('Table "file_usages" does not exist.');
            return self::FAILURE;
        }

        if (! DB::getSchemaBuilder()->hasTable('product_variants')) {
            $this->error('Table "product_variants" does not exist.');
            return self::FAILURE;
        }

        $chunkSize = max(1, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');

        $baseQuery = DB::table('file_usages')
            ->select(['id', 'usage_id', 'usage_type', 'meta'])
            ->where('usage_type', 'products')
            ->orderBy('id');

        $total = (clone $baseQuery)->count();
        if ($total === 0) {
            $this->info('No product-level file_usages rows found.');
            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Processing %d product-level file usage rows (chunk: %d)%s',
            $total,
            $chunkSize,
            $dryRun ? ' [dry-run]' : ''
        ));

        $processed = 0;
        $updated = 0;
        $skippedNoColor = 0;
        $skippedNoVariant = 0;
        $ambiguousColors = 0;

        $baseQuery->chunkById($chunkSize, function ($rows) use (
            $dryRun,
            &$processed,
            &$updated,
            &$skippedNoColor,
            &$skippedNoVariant,
            &$ambiguousColors
        ): void {
            $productIds = collect($rows)
                ->pluck('usage_id')
                ->filter(static fn ($id) => is_numeric($id) && (int) $id > 0)
                ->map(static fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            if (empty($productIds)) {
                $processed += count($rows);
                return;
            }

            $variants = DB::table('product_variants')
                ->select(['id', 'product_id', 'attributes'])
                ->whereIn('product_id', $productIds)
                ->orderBy('id')
                ->get();

            $variantMap = [];
            foreach ($variants as $variant) {
                $productId = (int) ($variant->product_id ?? 0);
                if ($productId <= 0) {
                    continue;
                }

                $attributes = $this->decodeJson($variant->attributes);
                $color = $this->extractColorFromAttributes($attributes);
                $normalizedColor = $this->normalizeColor($color);
                if ($normalizedColor === null) {
                    continue;
                }

                if (! isset($variantMap[$productId])) {
                    $variantMap[$productId] = [];
                }

                // Keep the first (lowest id) match deterministic, track ambiguity.
                if (isset($variantMap[$productId][$normalizedColor]) && $variantMap[$productId][$normalizedColor] !== (int) $variant->id) {
                    $ambiguousColors++;
                    continue;
                }

                $variantMap[$productId][$normalizedColor] = (int) $variant->id;
            }

            $upserts = [];
            $timestamp = now();
            foreach ($rows as $usage) {
                $usageId = (int) ($usage->usage_id ?? 0);
                if ($usageId <= 0) {
                    $skippedNoVariant++;
                    continue;
                }

                $meta = $this->decodeJson($usage->meta);
                $color = $this->extractColorFromMeta($meta);
                $normalizedColor = $this->normalizeColor($color);
                if ($normalizedColor === null) {
                    $skippedNoColor++;
                    continue;
                }

                $matchedVariantId = $variantMap[$usageId][$normalizedColor] ?? null;
                if (! is_int($matchedVariantId) || $matchedVariantId <= 0) {
                    $skippedNoVariant++;
                    continue;
                }

                $upserts[] = [
                    'id' => (int) $usage->id,
                    'usage_type' => 'product_variants',
                    'usage_id' => $matchedVariantId,
                    'updated_at' => $timestamp,
                ];
            }

            if (! $dryRun && ! empty($upserts)) {
                foreach ($upserts as $payload) {
                    DB::table('file_usages')
                        ->where('id', (int) $payload['id'])
                        ->update([
                            'usage_type' => $payload['usage_type'],
                            'usage_id' => $payload['usage_id'],
                            'updated_at' => $payload['updated_at'],
                        ]);
                }
            }

            $updated += count($upserts);
            $processed += count($rows);
            $this->line(sprintf('Processed %d rows... (matched updates: %d)', $processed, count($upserts)));
        }, 'id');

        $this->newLine();
        $this->info(sprintf(
            '%s complete. Processed: %d, Updated: %d, Skipped(no color): %d, Skipped(no variant match): %d, Ambiguous color collisions: %d',
            $dryRun ? 'Dry run' : 'Mapping',
            $processed,
            $updated,
            $skippedNoColor,
            $skippedNoVariant,
            $ambiguousColors
        ));

        return self::SUCCESS;
    }

    private function decodeJson(mixed $value): ?array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function extractColorFromAttributes(?array $attributes): ?string
    {
        if (! is_array($attributes)) {
            return null;
        }

        $keys = ['Color', 'color', 'Colour', 'colour'];
        foreach ($keys as $key) {
            if (array_key_exists($key, $attributes) && is_string($attributes[$key])) {
                return $attributes[$key];
            }
        }

        return null;
    }

    private function extractColorFromMeta(?array $meta): ?string
    {
        if (! is_array($meta)) {
            return null;
        }

        $keys = ['color', 'Color', 'colour', 'Colour'];
        foreach ($keys as $key) {
            if (array_key_exists($key, $meta) && is_string($meta[$key])) {
                return $meta[$key];
            }
        }

        return null;
    }

    private function normalizeColor(?string $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = mb_strtolower(trim($value));
        if ($normalized === '') {
            return null;
        }

        $normalized = str_replace(['_', '-'], ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
        $normalized = trim($normalized);

        return $normalized !== '' ? $normalized : null;
    }
}
