<?php

declare(strict_types=1);

namespace App\Foundation\Shared\Application\Contracts;

interface ImageConverter
{
    /**
     * Convert an image file to webp.
     *
     * @param string $disk       Storage disk name (local/cdn/s3)
     * @param string $sourcePath Path on disk (e.g. products/x/original.jpg)
     * @param string $targetPath Path on disk (e.g. products/x/original.webp)
     * @param int    $quality    0-100
     */
    public function toWebp(string $disk, string $sourcePath, string $targetPath, int $quality = 80): void;
}