<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Actions;

use App\Domains\EmiRequest\Models\EmiApplication;
use App\Domains\EmiRequest\Models\EmiRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class EmiApplicationApproveAction
{
    private string $disk;

    public function __construct()
    {
        $this->disk = (string) config('filesystems.default');
    }

    /**
     * @param array{
     *   application_id?: int|null,
     *   request_id?: int|null,
     *   subject: string,
     *   to: string,
     *   cc?: string|null,
     *   bcc?: string|null,
     *   file_name?: string|null
     * } $validated
     * @return array{status:int,payload:array}
     */
    public function execute(array $validated, string $id): array
    {
        $applicationId = (int) ($validated['application_id'] ?? $id);
        $application = EmiApplication::query()->findOrFail($applicationId);

        $to = $this->parseEmailList((string) $validated['to']);
        if (empty($to)) {
            return [
                'status' => 422,
                'payload' => [
                    'message' => 'A valid "to" email address is required.',
                ],
            ];
        }
        $cc = $this->parseEmailList((string) ($validated['cc'] ?? ''));
        $bcc = $this->parseEmailList((string) ($validated['bcc'] ?? ''));

        $filePath = (string) ($application->file_path ?? '');
        $fileName = (string) ($validated['file_name'] ?? '');
        if ($fileName === '') {
            $fileName = basename($filePath);
        }

        try {
            $disk = $this->disk;

            Mail::send([], [], function ($message) use ($validated, $to, $cc, $bcc, $filePath, $fileName, $disk): void {
                $message
                    ->to($to)
                    ->subject((string) $validated['subject'])
                    ->html('<p>Your EMI application has been approved. Please find the attached file.</p>');

                if (! empty($cc)) {
                    $message->cc($cc);
                }

                if (! empty($bcc)) {
                    $message->bcc($bcc);
                }

                if ($filePath !== '' && Storage::disk($disk)->exists($filePath)) {
                    $content = Storage::disk($disk)->get($filePath);

                    $message->attachData($content, $fileName !== '' ? $fileName : 'emi-application.pdf', [
                        'mime' => 'application/pdf',
                    ]);
                }
            });
        } catch (Throwable $exception) {
            return [
                'status' => 500,
                'payload' => [
                    'message' => 'Failed to send approval email.',
                    'error' => $exception->getMessage(),
                ],
            ];
        }

        $application->status = 'approved';
        $application->save();

        $requestId = (int) ($validated['request_id'] ?? $application->emi_request_id ?? 0);
        if ($requestId > 0) {
            EmiRequest::query()
                ->where('id', $requestId)
                ->update(['status' => EmiRequest::STATUS_APPROVED]);
        }

        return [
            'status' => 200,
            'payload' => [
                'message' => 'Approval email sent and application approved successfully.',
                'application_id' => $application->id,
                'request_id' => $requestId > 0 ? $requestId : null,
                'status' => $application->status,
            ],
        ];
    }

    private function parseEmailList(string $value): array
    {
        $emails = array_filter(array_map('trim', explode(',', $value)));

        return array_values(array_filter($emails, static function (string $email): bool {
            return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
        }));
    }
}
