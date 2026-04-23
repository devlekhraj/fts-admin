<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Pdf\Renderer;

interface PdfRenderer
{
    public function renderHtml(string $html): string;
}

