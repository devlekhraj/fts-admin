<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Mail;

use App\Domains\EmiRequest\Models\EmiRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class EmiApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array{disk?:string,path?:string,url?:string}|null $quotationPdf
     */
    public function __construct(
        public readonly EmiRequest $emiRequest,
        public readonly ?array $quotationPdf = null,
    ) {}

    public function build(): self
    {
        $mail = $this
            ->subject('Your EMI request has been approved')
            ->view('emails.emi_approved')
            ->with([
                'emiRequest' => $this->emiRequest,
                'quotationPdf' => $this->quotationPdf,
            ]);

        $diskName = is_array($this->quotationPdf) ? (string) ($this->quotationPdf['disk'] ?? '') : '';
        $path = is_array($this->quotationPdf) ? (string) ($this->quotationPdf['path'] ?? '') : '';

        if ($diskName !== '' && $path !== '') {
            try {
                $disk = Storage::disk($diskName);

                if ($disk->exists($path)) {
                    $bytes = $disk->get($path);
                    if (is_string($bytes) && $bytes !== '') {
                        $mail->attachData($bytes, 'emi-user-quotation.pdf', [
                            'mime' => 'application/pdf',
                        ]);
                    }
                }
            } catch (Throwable $e) {
                Log::warning('Unable to attach EMI quotation PDF to email.', [
                    'disk' => $diskName,
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $mail;
    }
}
