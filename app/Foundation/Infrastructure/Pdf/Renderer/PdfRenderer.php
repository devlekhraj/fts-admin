<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Pdf\Renderer;

interface PdfRenderer
{
    public function renderHtml(string $html): string;
}

