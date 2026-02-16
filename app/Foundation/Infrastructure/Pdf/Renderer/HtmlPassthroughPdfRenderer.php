<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Pdf\Renderer;

final class HtmlPassthroughPdfRenderer implements PdfRenderer
{
    public function renderHtml(string $html): string
    {
        // Placeholder renderer: returns HTML bytes until a real PDF engine is wired.
        return $html;
    }
}

