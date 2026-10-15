<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Builds public links for stored files.
 *
 * Catalogue images (products, blogs, banners...) get a plain permanent link.
 * Customer documents under `emi-requests/` (citizenship, signatures, guarantor papers, quotation PDFs)
 * get a time-limited, signed link: the image server (nginx secure_link) refuses them without a valid
 * signature and answers 410 once the link has expired, so a copied or leaked link stops working.
 *
 * The signature is base64url(md5("<expires><path> <secret>")), the exact format nginx expects:
 *   secure_link_md5 "$secure_link_expires$uri <secret>";
 */
final class MediaUrl
{
    /** Folders whose files are private and need a signed link. */
    private const PROTECTED_PREFIXES = ['emi-requests/'];

    public static function for(string $relativePath, ?string $baseUrl = null): string
    {
        $path = ltrim($relativePath, '/');
        $base = rtrim($baseUrl ?? (string) config('filesystems.disks.' . config('filesystems.default') . '.url', ''), '/');

        $encoded = implode('/', array_map('rawurlencode', explode('/', $path)));
        $url = ($base !== '' ? $base : '') . '/' . $encoded;

        if (! self::isProtected($path)) {
            return $url;
        }

        $secret = (string) config('services.media.emi_url_secret', '');
        if ($secret === '') {
            // Without a secret no valid signature exists, so the image server refuses this link.
            // That is safe (private files stay private) but visible, rather than silently public.
            return $url;
        }

        $expires = time() + max(60, (int) config('services.media.emi_url_ttl', 14400));

        return $url . '?md5=' . self::signature($expires, $path, $secret) . '&expires=' . $expires;
    }

    public static function isProtected(string $relativePath): bool
    {
        $path = ltrim($relativePath, '/');
        foreach (self::PROTECTED_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /** Signature for a path (without the leading slash) that expires at the given unix time. */
    public static function signature(int $expires, string $path, string $secret): string
    {
        $raw = md5($expires . '/' . ltrim($path, '/') . ' ' . $secret, true);

        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }
}
