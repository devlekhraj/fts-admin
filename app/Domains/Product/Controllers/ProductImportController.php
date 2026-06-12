<?php

declare(strict_types=1);

namespace App\Domains\Product\Controllers;

use App\Domains\File\Actions\FileUploadAction;
use App\Domains\File\Models\File;
use App\Domains\File\Models\FileUsage;
use App\Domains\Product\Models\Product;
use App\Domains\Product\Requests\ImportProductsRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Throwable;

final class ProductImportController extends Controller
{
    public function __construct(
        private readonly FileUploadAction $fileUploadAction,
    ) {}

    public function preview(ImportProductsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $rows = is_array($validated['rows'] ?? null) ? $validated['rows'] : [];
        $normalizedRows = array_map(
            fn ($row) => $this->normalizeRow(is_array($row) ? $row : []),
            $rows,
        );
        $lookupMaps = $this->buildLookupMaps($normalizedRows);

        $summary = [
            'processed' => 0,
            'existing' => 0,
            'new' => 0,
            'unchanged' => 0,
            'invalid' => 0,
        ];
        $rowsPreview = [];

        foreach ($normalizedRows as $index => $normalizedRow) {
            $rowNumber = $this->resolveRowNumber($normalizedRow, (int) $index + 2);
            $previewRow = $this->previewRow($normalizedRow, $lookupMaps['by_id'], $lookupMaps['by_sku'], $rowNumber);

            $summary['processed']++;
            $summary[$previewRow['mode']]++;
            $rowsPreview[] = $previewRow;
        }

        return response()->json([
            'success' => true,
            'message' => 'Product import preview generated successfully.',
            'data' => [
                'summary' => $summary,
                'rows' => $rowsPreview,
            ],
        ]);
    }

    public function store(ImportProductsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $rows = is_array($validated['rows'] ?? null) ? $validated['rows'] : [];
        $normalizedRows = array_map(
            fn ($row) => $this->normalizeRow(is_array($row) ? $row : []),
            $rows,
        );
        $lookupMaps = $this->buildLookupMaps($normalizedRows);
        $summary = [
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];
        $results = [];
        $validationErrors = [];
        $rowWarnings = [];

        try {
            DB::transaction(function () use ($normalizedRows, $lookupMaps, &$summary, &$results, &$validationErrors, &$rowWarnings): void {
                foreach (array_chunk($normalizedRows, 5, true) as $chunk) {
                    foreach ($chunk as $index => $normalizedRow) {
                        $rowNumber = $this->resolveRowNumber($normalizedRow, (int) $index + 2);

                        try {
                            $summary['processed']++;
                            $resolvedProduct = $this->resolveProduct($normalizedRow, $lookupMaps['by_id'], $lookupMaps['by_sku']);
                            $payload = $this->extractProductPayload($normalizedRow);
                            $categoryIds = $this->extractCategoryIds($normalizedRow);

                            if ($resolvedProduct) {
                                if (empty($payload) && empty($categoryIds)) {
                                    $summary['skipped']++;
                                    $results[] = [
                                        'row' => $rowNumber,
                                        'status' => 'skipped',
                                        'reason' => 'No updatable values were provided.',
                                        'product_id' => $resolvedProduct->id,
                                        'product_name' => $resolvedProduct->name,
                                        'price' => $resolvedProduct->price,
                                        'warnings' => $rowWarnings[$rowNumber] ?? [],
                                    ];

                                    continue;
                                }

                                $changes = $this->buildUpdateChanges($resolvedProduct, $payload, $categoryIds);
                                if ($changes === []) {
                                    $summary['skipped']++;
                                    $results[] = [
                                        'row' => $rowNumber,
                                        'status' => 'skipped',
                                        'reason' => 'No changes detected.',
                                        'product_id' => $resolvedProduct->id,
                                        'product_name' => $resolvedProduct->name,
                                        'price' => $resolvedProduct->price,
                                        'match_field' => $this->matchedBy($normalizedRow, $resolvedProduct),
                                        'warnings' => $rowWarnings[$rowNumber] ?? [],
                                    ];

                                    continue;
                                }

                                $resolvedProduct->fill($payload);
                                $resolvedProduct->save();

                                if ($categoryIds !== null) {
                                    $resolvedProduct->categories()->sync($categoryIds);
                                }

                                $summary['updated']++;
                                $results[] = [
                                    'row' => $rowNumber,
                                    'status' => 'updated',
                                    'product_id' => $resolvedProduct->id,
                                    'product_name' => $resolvedProduct->name,
                                    'price' => $resolvedProduct->price,
                                    'match_field' => $this->matchedBy($normalizedRow, $resolvedProduct),
                                    'changes' => $changes,
                                    'warnings' => $rowWarnings[$rowNumber] ?? [],
                                ];

                                continue;
                            }

                            $createPayload = $this->buildCreatePayload($normalizedRow, $validationErrors, $rowNumber);
                            if ($createPayload === null) {
                                continue;
                            }

                            $product = Product::query()->create($createPayload);
                            $imageData = $this->extractImageData($normalizedRow);
                            $hasImages = $imageData['default'] !== null || $imageData['gallery'] !== [];
                            if ($categoryIds !== null) {
                                $product->categories()->sync($categoryIds);
                            }

                            if ($hasImages) {
                                $this->syncProductImages($product, $imageData, $validationErrors, $rowNumber, $rowWarnings);
                            }

                            $summary['created']++;
                            $results[] = [
                                'row' => $rowNumber,
                                'status' => 'created',
                                'product_id' => $product->id,
                                'product_name' => $product->name,
                                'price' => $product->price,
                                'match_field' => null,
                                'warnings' => $rowWarnings[$rowNumber] ?? [],
                            ];
                        } catch (Throwable $throwable) {
                            $summary['failed']++;
                            $validationErrors["rows.{$rowNumber}"] = [$throwable->getMessage()];
                            $results[] = [
                                'row' => $rowNumber,
                                'status' => 'failed',
                                'error' => $throwable->getMessage(),
                                'warnings' => $rowWarnings[$rowNumber] ?? [],
                            ];
                        }
                    }
                }

                if ($validationErrors !== []) {
                    throw ValidationException::withMessages($validationErrors);
                }
            });
        } catch (Throwable $throwable) {
            throw $throwable;
        }

        return response()->json([
            'success' => true,
            'message' => 'Product import completed successfully.',
            'data' => [
                'summary' => $summary,
                'results' => $results,
            ],
        ]);
    }

    private function buildLookupMaps(array $rows): array
    {
        $ids = [];
        $skus = [];

        foreach ($rows as $row) {
            $id = $this->toInteger($row['id'] ?? null);
            if ($id !== null) {
                $ids[] = $id;
            }

            $sku = $this->toString($row['sku'] ?? null);
            if ($sku !== null) {
                $skus[] = $sku;
            }
        }

        $byId = [];
        $bySku = [];

        if ($ids === [] && $skus === []) {
            return [
                'by_id' => $byId,
                'by_sku' => $bySku,
            ];
        }

        $products = Product::query()
            ->where(function ($query) use ($ids, $skus): void {
                $uniqueIds = array_values(array_unique($ids));
                $uniqueSkus = array_values(array_unique($skus));

                if ($uniqueIds !== []) {
                    $query->whereIn('id', $uniqueIds);
                }

                if ($uniqueSkus !== []) {
                    if ($uniqueIds !== []) {
                        $query->orWhereIn('sku', $uniqueSkus);
                    } else {
                        $query->whereIn('sku', $uniqueSkus);
                    }
                }
            })
            ->get();

        foreach ($products as $product) {
            $byId[(string) $product->id] = $product;
            if ($product->sku !== null && $product->sku !== '') {
                $bySku[(string) $product->sku] = $product;
            }
        }

        return [
            'by_id' => $byId,
            'by_sku' => $bySku,
        ];
    }

    private function resolveProduct(array $row, array $byId, array $bySku): ?Product
    {
        $id = $this->toInteger($row['id'] ?? null);
        if ($id !== null) {
            $product = $byId[(string) $id] ?? null;
            if ($product instanceof Product) {
                return $product;
            }
        }

        $sku = $this->toString($row['sku'] ?? null);
        if ($sku !== null) {
            $product = $bySku[$sku] ?? null;
            if ($product instanceof Product) {
                return $product;
            }
        }

        return null;
    }

    private function matchedBy(array $row, Product $product): ?string
    {
        $id = $this->toInteger($row['id'] ?? null);
        if ($id !== null && (int) $product->id === $id) {
            return 'id';
        }

        $sku = $this->toString($row['sku'] ?? null);
        if ($sku !== null && $product->sku === $sku) {
            return 'sku';
        }

        return null;
    }

    private function previewRow(array $normalizedRow, array $byId, array $bySku, int $rowNumber): array
    {
        $resolvedProduct = $this->resolveProduct($normalizedRow, $byId, $bySku);
        $payload = $this->extractProductPayload($normalizedRow);
        $categoryIds = $this->extractCategoryIds($normalizedRow);

        if ($resolvedProduct instanceof Product) {
            if (empty($payload) && empty($categoryIds)) {
                return [
                    'row' => $rowNumber,
                    'mode' => 'unchanged',
                    'product_id' => $resolvedProduct->id,
                    'product_name' => $resolvedProduct->name,
                    'price' => $resolvedProduct->price,
                    'match_field' => $this->matchedBy($normalizedRow, $resolvedProduct),
                    'reason' => 'No updatable values were provided.',
                ];
            }

            $changes = $this->buildUpdateChanges($resolvedProduct, $payload, $categoryIds);
            if ($changes === []) {
                return [
                    'row' => $rowNumber,
                    'mode' => 'unchanged',
                    'product_id' => $resolvedProduct->id,
                    'product_name' => $resolvedProduct->name,
                    'price' => $resolvedProduct->price,
                    'match_field' => $this->matchedBy($normalizedRow, $resolvedProduct),
                    'reason' => 'No changes detected.',
                ];
            }

            return [
                'row' => $rowNumber,
                'mode' => 'existing',
                'product_id' => $resolvedProduct->id,
                'product_name' => $resolvedProduct->name,
                'price' => $resolvedProduct->price,
                'match_field' => $this->matchedBy($normalizedRow, $resolvedProduct),
                'changes' => $changes,
            ];
        }

        $previewErrors = [];
        $createPayload = $this->buildCreatePayload($normalizedRow, $previewErrors, $rowNumber);
        if ($createPayload === null) {
            return [
                'row' => $rowNumber,
                'mode' => 'invalid',
                'reason' => $this->firstValidationMessage($previewErrors) ?? 'Unable to preview this row.',
            ];
        }

        return [
            'row' => $rowNumber,
            'mode' => 'new',
            'product_id' => null,
            'product_name' => $createPayload['name'] ?? null,
            'price' => $createPayload['price'] ?? null,
            'match_field' => null,
        ];
    }

    private function firstValidationMessage(array $validationErrors): ?string
    {
        foreach ($validationErrors as $messages) {
            if (is_array($messages) && $messages !== []) {
                $first = $messages[0] ?? null;
                if (is_string($first) && trim($first) !== '') {
                    return $first;
                }
            }
        }

        return null;
    }

    private function buildUpdateChanges(Product $product, array $payload, ?array $categoryIds): array
    {
        $changes = [];
        $original = $product->getOriginal();

        foreach ($payload as $field => $currentValue) {
            $previousValue = $original[$field] ?? null;
            if ($this->valuesAreEqual($previousValue, $currentValue)) {
                continue;
            }

            $changes[] = [
                'field' => (string) $field,
                'previous' => $this->normalizeChangeValue($previousValue),
                'current' => $this->normalizeChangeValue($currentValue),
            ];
        }

        if ($categoryIds !== null) {
            $previousCategoryIds = $product->categories()->pluck('id')->map(fn ($value) => (int) $value)->values()->all();
            $normalizedPrevious = array_values(array_map('intval', $previousCategoryIds));
            $normalizedCurrent = array_values(array_map('intval', $categoryIds));
            sort($normalizedPrevious);
            sort($normalizedCurrent);

            if ($normalizedPrevious !== $normalizedCurrent) {
                $changes[] = [
                    'field' => 'category_ids',
                    'previous' => $normalizedPrevious,
                    'current' => $normalizedCurrent,
                ];
            }
        }

        return $changes;
    }

    private function buildCreatePayload(array $row, array &$validationErrors, int $rowNumber): ?array
    {
        $payload = $this->extractProductPayload($row);
        $name = $this->toString($row['name'] ?? $row['title'] ?? $row['product_name'] ?? null);
        if ($name === null) {
            $validationErrors["rows.{$rowNumber}.name"] = ['Name is required when creating a new product.'];

            return null;
        }

        $slug = $this->toString($row['slug'] ?? null);
        if ($slug === null) {
            $slug = Str::slug($name);
        }
        if ($slug === '') {
            $validationErrors["rows.{$rowNumber}.slug"] = ['Slug is required when creating a new product.'];

            return null;
        }

        $payload['name'] = $name;
        $payload['slug'] = $slug;
        $payload['sku'] = $this->toString($row['sku'] ?? null) ?? $slug;

        return $payload;
    }

    private function extractProductPayload(array $row): array
    {
        $payload = [];
        $fields = [
            'name',
            'slug',
            'short_description',
            'description',
            'sku',
            'price',
            'original_price',
            'brand_id',
            'vendor_id',
            'quantity',
            'pre_order',
            'pre_order_price',
            'unit',
            'highlights',
            'product_video_url',
            'weight',
            'length',
            'width',
            'height',
            'status',
            'is_featured',
            'emi_enabled',
            'attributes',
            'attribute_class_id',
            'variant_attributes',
            'meta_title',
            'meta_keywords',
            'meta_description',
            'custom_code',
            'warranty_description',
        ];

        foreach ($fields as $field) {
            if (! array_key_exists($field, $row)) {
                continue;
            }

            $value = $this->castFieldValue($field, $row[$field]);
            if ($this->isEmptyValue($value)) {
                continue;
            }

            $payload[$field] = $value;
        }

        return $payload;
    }

    private function extractCategoryIds(array $row): ?array
    {
        if (! array_key_exists('category_ids', $row)) {
            return null;
        }

        $value = $row['category_ids'];
        if ($this->isEmptyValue($value)) {
            return [];
        }

        if (is_array($value)) {
            $ids = [];
            foreach ($value as $item) {
                $id = $this->toInteger($item);
                if ($id !== null) {
                    $ids[] = $id;
                }
            }

            return $ids;
        }

        $decoded = null;
        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return [];
            }

            if (Str::startsWith($trimmed, '[')) {
                $decoded = json_decode($trimmed, true);
            }
        }

        if (is_array($decoded)) {
            $ids = [];
            foreach ($decoded as $item) {
                $id = $this->toInteger($item);
                if ($id !== null) {
                    $ids[] = $id;
                }
            }

            return $ids;
        }

        if (! is_string($value)) {
            return [];
        }

        $ids = [];
        foreach (preg_split('/[,|;]/', $value) ?: [] as $part) {
            $id = $this->toInteger(trim($part));
            if ($id !== null) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    private function normalizeRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalizedKey = $this->normalizeKey((string) $key);
            if ($normalizedKey === '') {
                continue;
            }

            $normalized[$normalizedKey] = $this->normalizeCellValue($value);
        }

        return $normalized;
    }

    private function normalizeCellValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed === '' ? null : $trimmed;
        }

        if (is_array($value)) {
            return array_map(fn ($item) => $this->normalizeCellValue($item), $value);
        }

        return $value;
    }

    private function normalizeKey(string $key): string
    {
        $key = Str::of($key)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();

        return $this->mapAliasToCanonical($key);
    }

    private function mapAliasToCanonical(string $key): string
    {
        $aliases = [
            'id' => ['id', 'product_id'],
            'name' => ['name', 'product_name', 'title'],
            'slug' => ['slug', 'product_slug'],
            'sku' => ['sku', 'product_sku'],
            'short_description' => ['short_description', 'short_desc'],
            'description' => ['description', 'product_description'],
            'price' => ['price', 'current_price'],
            'original_price' => ['original_price', 'compare_price'],
            'brand_id' => ['brand_id', 'brand'],
            'vendor_id' => ['vendor_id', 'vendor'],
            'quantity' => ['quantity', 'stock', 'stock_quantity'],
            'pre_order' => ['pre_order', 'preorder'],
            'pre_order_price' => ['pre_order_price', 'preorder_price'],
            'product_video_url' => ['product_video_url', 'video_url'],
            'is_featured' => ['is_featured', 'featured'],
            'emi_enabled' => ['emi_enabled', 'emi'],
            'attribute_class_id' => ['attribute_class_id', 'attribute_class'],
            'meta_title' => ['meta_title', 'seo_title'],
            'meta_keywords' => ['meta_keywords', 'seo_keywords'],
            'meta_description' => ['meta_description', 'seo_description'],
            'custom_code' => ['custom_code', 'schema_jsonld'],
            'warranty_description' => ['warranty_description', 'warranty'],
            'category_ids' => ['category_ids', 'categories', 'category'],
            'image_url' => ['image_url', 'image', 'default_image', 'main_image', 'thumbnail_url', 'thumb_url', 'photo_url'],
            'gallery_images' => ['gallery_images', 'images', 'image_urls', 'gallery'],
        ];

        foreach ($aliases as $canonical => $variants) {
            if (in_array($key, $variants, true)) {
                return $canonical;
            }
        }

        return $key;
    }

    private function extractImageData(array $row): array
    {
        $default = null;
        foreach (['image_url', 'default_image', 'main_image', 'thumbnail_url', 'thumb_url', 'photo_url'] as $key) {
            $value = $this->toString($row[$key] ?? null);
            if ($value !== null) {
                $default = $value;
                break;
            }
        }

        $gallery = $this->normalizeImageUrls($row['gallery_images'] ?? null);

        $singleImage = $this->toString($row['image_url'] ?? null);
        if ($singleImage !== null) {
            $default = $singleImage;
        }

        $gallery = array_values(array_filter($gallery, static fn (string $url): bool => $url !== ''));

        if ($default !== null) {
            $gallery = array_values(array_filter($gallery, static fn (string $url) => $url !== $default));
        }

        if ($default === null && $gallery !== []) {
            $default = array_shift($gallery);
        }

        return [
            'default' => $default,
            'gallery' => $gallery,
        ];
    }

    private function normalizeImageUrls(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            $urls = [];
            foreach ($value as $item) {
                $url = $this->toString($item);
                if ($url !== null) {
                    $urls[] = $url;
                }
            }

            return array_values(array_unique($urls));
        }

        if (! is_string($value)) {
            return [];
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return [];
        }

        $decoded = json_decode($trimmed, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $urls = [];
            foreach ($decoded as $item) {
                $url = $this->toString($item);
                if ($url !== null) {
                    $urls[] = $url;
                }
            }

            return array_values(array_unique($urls));
        }

        $parts = preg_split('/[\r\n,;|]+/', $trimmed) ?: [];
        $urls = [];
        foreach ($parts as $part) {
            $url = $this->toString($part);
            if ($url !== null) {
                $urls[] = $url;
            }
        }

        return array_values(array_unique($urls));
    }

    private function syncProductImages(Product $product, array $imageData, array &$validationErrors, int $rowNumber, array &$rowWarnings): void
    {
        $defaultUrl = $imageData['default'] ?? null;
        $galleryUrls = is_array($imageData['gallery'] ?? null) ? $imageData['gallery'] : [];
        $urls = [];

        if (is_string($defaultUrl) && trim($defaultUrl) !== '') {
            $urls[] = ['url' => trim($defaultUrl), 'is_default' => true];
        }

        foreach ($galleryUrls as $galleryUrl) {
            if (! is_string($galleryUrl) || trim($galleryUrl) === '') {
                continue;
            }

            $normalizedGalleryUrl = trim($galleryUrl);
            if ($defaultUrl !== null && $normalizedGalleryUrl === trim($defaultUrl)) {
                continue;
            }

            $urls[] = [
                'url' => $normalizedGalleryUrl,
                'is_default' => false,
            ];
        }

        foreach ($urls as $imageIndex => $item) {
            $this->attachProductImage(
                $product,
                (string) $item['url'],
                (bool) $item['is_default'],
                $validationErrors,
                $rowNumber,
                $rowWarnings,
                $imageIndex === 0
            );
        }
    }

    private function attachProductImage(
        Product $product,
        string $imageUrl,
        bool $isDefault,
        array &$validationErrors,
        int $rowNumber,
        array &$rowWarnings,
        bool $useProductNameAsAlt = false,
    ): void {
        $imageUrl = trim($imageUrl);
        if ($imageUrl === '') {
            return;
        }

        if (! filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            $validationErrors["rows.{$rowNumber}.image_url"] = ["Invalid image URL: {$imageUrl}"];

            return;
        }

        $imageCheck = $this->checkRemoteImage($imageUrl);
        if (! ($imageCheck['exists'] ?? false)) {
            $rowWarnings[$rowNumber][] = (string) ($imageCheck['reason'] ?? "Image skipped: {$imageUrl} does not appear to be an image.");

            return;
        }

        try {
            $downloadedFile = $this->downloadRemoteImage($imageUrl, $imageCheck['content_type'] ?? null);
            if ($downloadedFile === null) {
                $rowWarnings[$rowNumber][] = "Image skipped: unable to download {$imageUrl}.";

                return;
            }

            $fileName = $downloadedFile->getClientOriginalName();
            $uploadResult = $this->fileUploadAction->execute($downloadedFile, 'products', $fileName);
            $file = $uploadResult['fileModel'] ?? null;

            if (! $file instanceof File) {
                $rowWarnings[$rowNumber][] = "Image skipped: unable to persist {$imageUrl}.";

                return;
            }

            $file->update([
                'is_public' => true,
            ]);

            if ($isDefault) {
                FileUsage::query()
                    ->where('usage_type', 'products')
                    ->where('usage_id', $product->id)
                    ->update(['meta->is_default' => false]);
            }

            FileUsage::query()->create([
                'file_id' => (int) $file->id,
                'usage_type' => 'products',
                'usage_id' => $product->id,
                'title' => $useProductNameAsAlt ? (string) $product->name : pathinfo($fileName, PATHINFO_FILENAME),
                'alt_text' => $useProductNameAsAlt ? (string) $product->name : pathinfo($fileName, PATHINFO_FILENAME),
                'meta' => [
                    'is_default' => $isDefault,
                ],
            ]);
        } catch (Throwable $throwable) {
            $rowWarnings[$rowNumber][] = 'Image skipped: ' . $throwable->getMessage();
        } finally {
            if (isset($downloadedFile)) {
                $tempPath = $downloadedFile->getPathname();
                if (is_string($tempPath) && $tempPath !== '' && is_file($tempPath)) {
                    @unlink($tempPath);
                }
            }
        }
    }

    private function checkRemoteImage(string $imageUrl): array
    {
        try {
            $response = Http::connectTimeout(3)->timeout(5)->retry(0, 0)->head($imageUrl);
        } catch (Throwable $throwable) {
            return [
                'exists' => false,
                'reason' => 'Image skipped: unable to verify URL ' . $imageUrl . ' (' . $throwable->getMessage() . ')',
            ];
        }

        if (! $response->successful()) {
            return [
                'exists' => false,
                'reason' => "Image skipped: {$imageUrl} returned HTTP {$response->status()}",
            ];
        }

        $contentType = strtolower(trim((string) $response->header('Content-Type', '')));
        if ($contentType !== '' && str_starts_with($contentType, 'image/')) {
            return [
                'exists' => true,
                'content_type' => $contentType,
            ];
        }

        $extension = strtolower((string) pathinfo((string) parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'avif', 'svg'], true)) {
            return [
                'exists' => true,
                'content_type' => $contentType !== '' ? $contentType : null,
            ];
        }

        return [
            'exists' => false,
            'reason' => $contentType !== ''
                ? "Image skipped: {$imageUrl} is not an image (content-type: {$contentType})"
                : "Image skipped: {$imageUrl} is not an image",
        ];
    }

    private function downloadRemoteImage(string $imageUrl, mixed $contentType = null): ?UploadedFile
    {
        try {
            $response = Http::connectTimeout(5)->timeout(15)->retry(0, 0)->get($imageUrl);
        } catch (Throwable $throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $bytes = $response->body();
        if (! is_string($bytes) || $bytes === '') {
            return null;
        }

        $fileName = $this->guessImageName($imageUrl, $contentType);
        $tempPath = tempnam(sys_get_temp_dir(), 'product-import-');
        if (! is_string($tempPath) || $tempPath === '') {
            return null;
        }

        file_put_contents($tempPath, $bytes);

        return new UploadedFile(
            $tempPath,
            $fileName,
            is_string($contentType) ? $contentType : null,
            null,
            true
        );
    }

    private function guessImageName(string $imageUrl, mixed $contentType = null): string
    {
        $path = (string) parse_url($imageUrl, PHP_URL_PATH);
        $name = trim((string) pathinfo($path, PATHINFO_BASENAME));
        if ($name === '') {
            $name = 'product-image';
        }

        $extension = strtolower((string) pathinfo($name, PATHINFO_EXTENSION));
        if ($extension === '') {
            $extension = $this->extensionFromMimeType(is_string($contentType) ? $contentType : null) ?? 'jpg';
            $name .= '.' . $extension;
        }

        return $name;
    }

    private function resolveRowNumber(array $row, int $fallback): int
    {
        $rowNumber = $this->toInteger($row['row_number'] ?? null);
        if ($rowNumber !== null && $rowNumber > 0) {
            return $rowNumber;
        }

        return $fallback;
    }

    private function extensionFromMimeType(?string $mimeType): ?string
    {
        $mimeType = is_string($mimeType) ? strtolower(trim($mimeType)) : '';
        if ($mimeType === '') {
            return null;
        }

        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/bmp' => 'bmp',
            'image/avif' => 'avif',
            'image/svg+xml' => 'svg',
            default => null,
        };
    }

    private function mimeTypeFromExtension(string $extension): ?string
    {
        $extension = strtolower(trim($extension));
        if ($extension === '') {
            return null;
        }

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            default => null,
        };
    }

    private function castFieldValue(string $field, mixed $value): mixed
    {
        return match ($field) {
            'id',
            'brand_id',
            'vendor_id',
            'quantity',
            'attribute_class_id' => $this->toInteger($value),
            'price',
            'original_price',
            'pre_order_price',
            'weight',
            'length',
            'width',
            'height' => $this->toFloat($value),
            'pre_order',
            'status',
            'is_featured',
            'emi_enabled' => $this->toBoolean($value),
            'attributes', 'variant_attributes' => $this->toArrayValue($value),
            default => $this->toString($value),
        };
    }

    private function toString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed === '' ? null : $trimmed;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return null;
    }

    private function toInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_float($value) && (int) $value === $value) {
            return (int) $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private function toFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    private function toBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) ((int) $value);
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if (in_array($normalized, ['1', 'true', 'yes', 'y', 'on'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'no', 'n', 'off'], true)) {
                return false;
            }
        }

        return null;
    }

    private function toArrayValue(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        $decoded = json_decode($trimmed, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return preg_split('/[,|;]/', $trimmed) ?: [$trimmed];
    }

    private function isEmptyValue(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_array($value)) {
            return $value === [];
        }

        return false;
    }

    private function valuesAreEqual(mixed $previous, mixed $current): bool
    {
        if (is_array($previous) || is_array($current)) {
            return $this->normalizeComparable($previous) === $this->normalizeComparable($current);
        }

        return $previous === $current || (string) $previous === (string) $current;
    }

    private function normalizeComparable(mixed $value): mixed
    {
        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $item) {
                $normalized[] = $this->normalizeComparable($item);
            }

            return $normalized;
        }

        if (is_bool($value) || is_int($value) || is_float($value) || is_string($value) || $value === null) {
            return $value;
        }

        return (string) $value;
    }

    private function normalizeChangeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($item) => $this->normalizeChangeValue($item), $value);
        }

        if (is_object($value)) {
            return method_exists($value, '__toString') ? (string) $value : get_class($value);
        }

        return $value;
    }
}
