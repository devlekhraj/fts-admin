<?php

declare(strict_types=1);

namespace App\Support\Storefront;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Tells the customer-facing API (and through it the Next.js storefront) that
 * catalogue data changed, so the change shows up without waiting for caches to expire.
 *
 * Calls are collected during the request and sent once, after the response has
 * been returned, so admin saves never get slower and a failure never breaks a save.
 */
final class StorefrontCacheInvalidator
{
    private const MAX_ITEMS = 200;

    /** @var array<string, array{type: string, slug: ?string}> */
    private static array $queue = [];

    private static bool $scheduled = false;

    public static function queue(string $type, ?string $slug = null): void
    {
        $key = $type . '|' . ($slug ?? '');
        if (isset(self::$queue[$key])) {
            return;
        }

        self::$queue[$key] = ['type' => $type, 'slug' => $slug];

        if (! self::$scheduled) {
            self::$scheduled = true;
            app()->terminating(static fn () => self::flush());
        }
    }

    public static function flush(): void
    {
        $items = array_values(self::$queue);
        self::$queue = [];
        self::$scheduled = false;

        $url = config('services.storefront.invalidate_url');
        $secret = config('services.storefront.invalidate_secret');
        if ($items === [] || ! $url || ! $secret) {
            return;
        }

        if (count($items) > self::MAX_ITEMS) {
            $items = [['type' => 'all', 'slug' => null]];
        }

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->withHeaders(['X-Storefront-Secret' => $secret])
                ->post($url, ['items' => $items]);

            if ($response->failed()) {
                Log::warning('Storefront invalidation rejected', ['status' => $response->status()]);
            }
        } catch (\Throwable $e) {
            Log::warning('Storefront invalidation failed', ['error' => $e->getMessage()]);
        }
    }
}
