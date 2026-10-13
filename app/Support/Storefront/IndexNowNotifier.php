<?php

declare(strict_types=1);

namespace App\Support\Storefront;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Tells IndexNow search engines (Bing, Yandex, Naver, Seznam; shared between them) which storefront
 * pages changed, so new or updated products, categories, brands and articles are recrawled within minutes.
 *
 * Only pages that are public, or were public and have just been hidden, deleted or renamed, are sent:
 * a hidden draft (e.g. a duplicated product) is never announced. URLs are collected during the request
 * and sent once after the response, so admin saves never slow down and a failure never breaks a save.
 *
 * @see https://www.indexnow.org/documentation
 */
final class IndexNowNotifier
{
    private const ENDPOINT = 'https://api.indexnow.org/indexnow';

    // Above this many URLs in one admin request (bulk import) we skip: the sitemap covers bulk changes.
    private const MAX_URLS = 500;

    private const PATHS = [
        'product' => '/product-detail/',
        'category' => '/category/',
        'brand' => '/brand/',
        'blog' => '/blog/',
    ];

    /** @var array<string, true> */
    private static array $urls = [];

    private static bool $scheduled = false;

    public static function enabled(): bool
    {
        $enabled = config('services.indexnow.enabled');

        // Default: on wherever this admin is wired to the live storefront (the same switch as cache sync).
        if ($enabled === null) {
            $enabled = (bool) config('services.storefront.invalidate_url');
        }

        return (bool) $enabled && config('services.indexnow.key') && config('services.indexnow.host');
    }

    /** Queue the page(s) affected by a model event ('saved', 'deleted' or 'restored'). */
    public static function queueModel(string $type, Model $model, string $event): void
    {
        if (! isset(self::PATHS[$type]) || ! self::enabled()) {
            return;
        }

        $slug = self::slug($model->getAttribute('slug'));
        $isPublic = (bool) $model->getAttribute('status');

        if ($event === 'deleted' || $event === 'restored') {
            // A soft-delete keeps status as it was: announce the removal (or return) of a public page.
            if ($isPublic && $slug) {
                self::queue($type, $slug);
            }

            return;
        }

        // 'saved' fires before the model syncs its original values, so getOriginal() is the previous state.
        $wasPublic = $model->wasRecentlyCreated ? false : (bool) $model->getOriginal('status');
        $oldSlug = $model->wasRecentlyCreated ? null : self::slug($model->getOriginal('slug'));

        if ($isPublic && $slug) {
            self::queue($type, $slug);
        }

        // The old address stops working when a public page is hidden or renamed.
        if ($wasPublic && $oldSlug && (! $isPublic || $oldSlug !== $slug)) {
            self::queue($type, $oldSlug);
        }
    }

    /** Queue a page whose content changed through a related record (variant, gift, image). */
    public static function queuePage(string $type, ?string $slug, bool $isPublic): void
    {
        $slug = self::slug($slug);
        if ($isPublic && $slug && isset(self::PATHS[$type]) && self::enabled()) {
            self::queue($type, $slug);
        }
    }

    public static function flush(): void
    {
        $urls = array_keys(self::$urls);
        self::$urls = [];
        self::$scheduled = false;

        if ($urls === [] || count($urls) > self::MAX_URLS || ! self::enabled()) {
            return;
        }

        $host = (string) config('services.indexnow.host');
        $key = (string) config('services.indexnow.key');

        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->asJson()
                ->post(self::ENDPOINT, [
                    'host' => $host,
                    'key' => $key,
                    'keyLocation' => "https://{$host}/{$key}.txt",
                    'urlList' => $urls,
                ]);

            // 200 = accepted, 202 = accepted while the key is being verified.
            if (! in_array($response->status(), [200, 202], true)) {
                Log::warning('IndexNow submission rejected', ['status' => $response->status(), 'urls' => count($urls)]);
            }
        } catch (\Throwable $e) {
            Log::warning('IndexNow submission failed', ['error' => $e->getMessage()]);
        }
    }

    private static function queue(string $type, string $slug): void
    {
        $host = (string) config('services.indexnow.host');
        self::$urls['https://' . $host . self::PATHS[$type] . rawurlencode($slug)] = true;

        if (! self::$scheduled) {
            self::$scheduled = true;
            app()->terminating(static fn () => self::flush());
        }
    }

    private static function slug(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }
}
