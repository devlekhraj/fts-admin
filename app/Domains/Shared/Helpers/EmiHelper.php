<?php

declare(strict_types=1);

namespace App\Domains\Shared\Helpers;

use App\Domains\EmiRequest\Models\EmiRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class EmiHelper
{
    private string $disk;

    public function __construct()
    {
        $this->disk = (string) config('filesystems.default');
    }

    public function generateEmiPdf(EmiRequest $emiRequest): array
    {
        // dd($emiRequest->bank);
        try {
            $dir = "emi-requests/{$emiRequest->id}/emi-quotations";
            $relativePath = $dir . '/emi-user-quotation.pdf';

            $pdf = Pdf::loadView('pdf.emi.emi-quotation', ['emiRequest' => $emiRequest]);
            $bytes = $pdf->output();

            if (! is_string($bytes) || $bytes === '') {
                throw new RuntimeException('Failed to generate EMI quotation PDF bytes.');
            }

            $disk = Storage::disk($this->disk);

            try {
                $disk->makeDirectory($dir);
            } catch (Throwable $e) {
                Log::warning('Unable to create EMI quotation directory.', [
                    'disk' => $this->disk,
                    'dir' => $dir,
                    'error' => $e->getMessage(),
                ]);
            }

            if ($disk->exists($relativePath)) {
                $disk->delete($relativePath);
            }

            if (! $disk->put($relativePath, $bytes)) {
                throw new RuntimeException('Failed to store EMI quotation PDF.');
            }

            return [
                'disk' => $this->disk,
                'path' => $relativePath,
                'url' => $disk->url($relativePath),
            ];
        } catch (Throwable $e) {
            dd($e->getMessage());
            throw new RuntimeException('Unable to generate/store EMI quotation PDF.', 0, $e);
        }
    }
}
