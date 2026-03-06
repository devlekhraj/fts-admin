<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Files\Images;

use App\Foundation\Shared\Application\Contracts\ImageConverter;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class WebpImageConverter implements ImageConverter
{
    /**
     * Example usage:
     * public function __construct(private readonly ImageConverter $converter) {}
     * $this->converter->toWebp('cdn', 'products/key/iphone.jpg', 'products/key/iphone.webp', 82);
     */
    public function toWebp(string $disk, string $sourcePath, string $targetPath, int $quality = 80): void
    {
        $storage = Storage::disk($disk);

        if (! $storage->exists($sourcePath)) {
            throw new RuntimeException(sprintf(
                'Source image not found on disk "%s": %s',
                $disk,
                $sourcePath
            ));
        }

        $bytes = $storage->get($sourcePath);
        if (! is_string($bytes) || $bytes === '') {
            throw new RuntimeException(sprintf(
                'Unable to read source image bytes from disk "%s": %s',
                $disk,
                $sourcePath
            ));
        }

        $this->assertEnoughMemoryForConversion($bytes, $sourcePath);

        $image = @imagecreatefromstring($bytes);
        if ($image === false) {
            throw new RuntimeException(sprintf(
                'Unsupported image or corrupted source: %s',
                $sourcePath
            ));
        }
        unset($bytes);

        $quality = max(0, min(100, $quality));

        if (function_exists('imagepalettetotruecolor')) {
            @imagepalettetotruecolor($image);
        }
        @imagealphablending($image, true);
        @imagesavealpha($image, true);

        $tmpHandle = tmpfile();
        if ($tmpHandle === false) {
            @imagedestroy($image);
            throw new RuntimeException('Failed to allocate temporary file for WebP conversion.');
        }

        $meta = stream_get_meta_data($tmpHandle);
        $tmpPath = is_array($meta) ? ($meta['uri'] ?? null) : null;
        if (! is_string($tmpPath) || $tmpPath === '') {
            fclose($tmpHandle);
            @imagedestroy($image);
            throw new RuntimeException('Failed to resolve temporary path for WebP conversion.');
        }

        $written = @imagewebp($image, $tmpPath, $quality);
        @imagedestroy($image);

        if ($written !== true) {
            fclose($tmpHandle);
            throw new RuntimeException(sprintf(
                'Failed to encode image to WebP: %s',
                $sourcePath
            ));
        }

        rewind($tmpHandle);
        $webpBytes = stream_get_contents($tmpHandle);
        fclose($tmpHandle);

        if (! is_string($webpBytes) || $webpBytes === '') {
            throw new RuntimeException(sprintf(
                'Failed to read converted WebP bytes for: %s',
                $sourcePath
            ));
        }

        if (! $storage->put($targetPath, $webpBytes)) {
            throw new RuntimeException(sprintf(
                'Failed to write converted WebP to disk "%s": %s',
                $disk,
                $targetPath
            ));
        }
    }

    private function assertEnoughMemoryForConversion(string $bytes, string $sourcePath): void
    {
        $limitBytes = $this->memoryLimitBytes();
        if ($limitBytes <= 0) {
            return;
        }

        $imageInfo = @getimagesizefromstring($bytes);
        if (! is_array($imageInfo) || ! isset($imageInfo[0], $imageInfo[1])) {
            return;
        }

        $width = (int) $imageInfo[0];
        $height = (int) $imageInfo[1];
        if ($width <= 0 || $height <= 0) {
            return;
        }

        $currentUsage = memory_get_usage(true);
        $rgbaBytes = (float) $width * (float) $height * 4.0;
        $estimatedExtraBytes = (int) ceil(($rgbaBytes * 2.2) + 8_388_608);
        $estimatedRequired = $currentUsage + $estimatedExtraBytes;

        if ($estimatedRequired <= $limitBytes) {
            return;
        }

        throw new RuntimeException(sprintf(
            'Insufficient memory for WebP conversion of "%s" (%dx%d). Estimated needed: %s, available under memory_limit: %s. Increase PHP memory_limit or fallback to original copy.',
            $sourcePath,
            $width,
            $height,
            $this->formatBytes($estimatedRequired),
            $this->formatBytes($limitBytes)
        ));
    }

    private function memoryLimitBytes(): int
    {
        $rawLimit = ini_get('memory_limit');
        if (! is_string($rawLimit)) {
            return 0;
        }

        $value = strtolower(trim($rawLimit));
        if ($value === '' || $value === '-1') {
            return 0;
        }

        if (! preg_match('/^(\d+(?:\.\d+)?)([gmk])?$/', $value, $matches)) {
            return 0;
        }

        $number = (float) $matches[1];
        $unit = $matches[2] ?? '';

        return match ($unit) {
            'g' => (int) round($number * 1024 * 1024 * 1024),
            'm' => (int) round($number * 1024 * 1024),
            'k' => (int) round($number * 1024),
            default => (int) round($number),
        };
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1_048_576) {
            return number_format($bytes / 1024, 1).' KB';
        }

        if ($bytes < 1_073_741_824) {
            return number_format($bytes / 1_048_576, 1).' MB';
        }

        return number_format($bytes / 1_073_741_824, 2).' GB';
    }
}
