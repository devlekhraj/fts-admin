<?php

declare(strict_types=1);

namespace App\Support\Storefront;

use App\Domains\Banner\Models\Banner;
use App\Domains\Blog\Models\Blog;
use App\Domains\Campaign\Models\Campaign;
use App\Domains\Campaign\Models\CampaignProduct;
use App\Domains\Faq\Models\Faq;
use App\Domains\File\Models\FileUsage;
use App\Domains\Page\Models\Page;
use App\Domains\Product\Models\Product;
use App\Domains\Product\Models\ProductGift;
use App\Domains\Product\Models\ProductVariant;
use App\Domains\ProductBrand\Models\ProductBrand;
use App\Domains\ProductCategory\Models\ProductCategory;
use App\Domains\Setting\Models\Setting;
use Illuminate\Database\Eloquent\Model;

/**
 * Hooks catalogue models so any admin change (form save, import, delete, restore)
 * asks the storefront to refresh. Everything is best-effort and never throws.
 */
final class StorefrontCacheObservers
{
    private const FILE_USAGE_TARGETS = [
        Product::class => 'product',
        ProductCategory::class => 'category',
        ProductBrand::class => 'brand',
        Blog::class => 'blog',
        ProductVariant::class => 'variant',
    ];

    public static function register(): void
    {
        $direct = [
            Product::class => 'product',
            ProductCategory::class => 'category',
            ProductBrand::class => 'brand',
            Blog::class => 'blog',
            Banner::class => 'all',
            Setting::class => 'all',
            Campaign::class => 'all',
            CampaignProduct::class => 'all',
            Page::class => 'all',
            Faq::class => 'all',
        ];

        foreach ($direct as $class => $type) {
            foreach (['saved', 'deleted', 'restored'] as $event) {
                if ($event === 'restored' && ! method_exists($class, 'restored')) {
                    continue;
                }

                $class::{$event}(static function (Model $model) use ($type): void {
                    self::safely(static function () use ($model, $type): void {
                        if ($type === 'all') {
                            StorefrontCacheInvalidator::queue('all');
                            return;
                        }

                        StorefrontCacheInvalidator::queue($type, self::slugOf($model));
                    });
                });
            }
        }

        foreach ([ProductVariant::class, ProductGift::class] as $class) {
            foreach (['saved', 'deleted'] as $event) {
                $class::{$event}(static function (Model $model): void {
                    self::safely(static function () use ($model): void {
                        self::queueProductById($model->getAttribute('product_id'));
                    });
                });
            }
        }

        foreach (['saved', 'deleted'] as $event) {
            FileUsage::{$event}(static function (FileUsage $usage): void {
                self::safely(static fn () => self::queueForFileUsage($usage));
            });
        }
    }

    private static function queueForFileUsage(FileUsage $usage): void
    {
        $usageType = (string) $usage->usage_type;
        $class = null;

        foreach (self::FILE_USAGE_TARGETS as $candidate => $type) {
            if ($usageType === $candidate || $usageType === (new $candidate())->getTable()) {
                $class = $candidate;
                break;
            }
        }

        if ($class === null) {
            return;
        }

        $type = self::FILE_USAGE_TARGETS[$class];

        if ($type === 'variant') {
            self::queueProductById(ProductVariant::query()->whereKey($usage->usage_id)->value('product_id'));
            return;
        }

        $slug = $class::query()->whereKey($usage->usage_id)->value('slug');
        StorefrontCacheInvalidator::queue($type, $slug ?: null);
    }

    private static function queueProductById(mixed $productId): void
    {
        $slug = $productId ? Product::withTrashed()->whereKey($productId)->value('slug') : null;
        StorefrontCacheInvalidator::queue('product', $slug ?: null);
    }

    private static function slugOf(Model $model): ?string
    {
        $slug = $model->getAttribute('slug');

        return is_string($slug) && $slug !== '' ? $slug : null;
    }

    private static function safely(callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
