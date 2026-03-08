<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use App\Foundation\Application\Emi\Commands\GenerateEmiApplicationPdfCommand;
use App\Foundation\Application\Emi\Handlers\GenerateEmiApplicationPdfHandler;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\EmiApplicationModel;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\EmiBankModel;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\EmiRequestModel;
use App\Foundation\Interfaces\Http\Requests\Admin\GenerateEmiApplicationPdfRequest;
use App\Foundation\Interfaces\Http\Resources\EmiApplicationResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

final class EmiApplicationsController extends Controller
{
    private const STORAGE_DISK = 'fatafat_cdn';

    public function __construct(
        private readonly GenerateEmiApplicationPdfHandler $handler,
    ) {}

    public function generatePdf(GenerateEmiApplicationPdfRequest $request, string $id): JsonResponse
    {
        $pdf = $this->handler->handle(new GenerateEmiApplicationPdfCommand($id));
        $emiRequestId = (int) ($pdf->emiRequestId ?? $id);

        $uniqueSuffix = (string) random_int(100000, 999999);
        $filename = $pdf->filename;
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $nameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);
        $uniqueFilename = $extension !== ''
            ? $nameWithoutExtension . '-' . $uniqueSuffix . '.' . $extension
            : $filename . '-' . $uniqueSuffix;
        $relativePath = "emi-requests/{$emiRequestId}/applications/{$uniqueFilename}";
        Storage::disk(self::STORAGE_DISK)->put($relativePath, $pdf->bytes);

        if ($pdf->emiBankId === null) {
            return response()->json([
                'message' => 'EMI bank not found for this request.',
            ], 422);
        }

        $applicationData = $pdf->applicationData;
        if (is_object($applicationData)) {
            $applicationData = json_decode(json_encode($applicationData), true);
        }

        if (!is_array($applicationData)) {
            $applicationData = [];
        }

        $requestData = $request->except(['signature_file']);
        if (!empty($requestData)) {
            $applicationData['form'] = $requestData;
        }

        $signaturePath = null;
        if ($request->hasFile('signature_file')) {
            $signature = $request->file('signature_file');
            $signatureName = 'signature-' . (string) Str::uuid() . '.' . $signature->getClientOriginalExtension();
            $signaturePath = "emi/applications/{$emiRequestId}/signatures/{$signatureName}";
            Storage::disk(self::STORAGE_DISK)->put($signaturePath, $signature->get());
            $applicationData['signature_file_path'] = $signaturePath;
        }

        $requestBankCode = (string) ($request->input('bank_code') ?? $request->input('bank') ?? '');
        $bankId = $pdf->emiBankId;
        if ($requestBankCode !== '') {
            $normalized = trim($requestBankCode);
            $bankRecord = EmiBankModel::query()
                ->where('bank_code', strtoupper($normalized))
                // ->orWhere('slug', strtolower($normalized))
                ->orWhere('name', $normalized)
                ->first();
            if ($bankRecord?->id) {
                $bankId = (int) $bankRecord->id;
            }
        }

        $application = new EmiApplicationModel();
        $application->emi_request_id = $emiRequestId;
        $application->emi_bank_id = $bankId;

        $application->application_data = $applicationData;
        $application->file_path = $relativePath;
        $application->status = $application->status ?? 'generated';
        $application->save();

        return response()->json([
            'message' => 'EMI application PDF generated successfully.',
            'path' => $relativePath,
            'file_url' => Storage::disk(self::STORAGE_DISK)->url($relativePath),
            'signature_path' => $signaturePath,
            'signature_url' => $signaturePath ? Storage::disk(self::STORAGE_DISK)->url($signaturePath) : null,
        ]);
    }

    public function applicationList(Request $request, string $id): JsonResponse
    {
        $items = EmiApplicationModel::query()
            ->where('emi_request_id', (int) $id)
            ->with('emiBank')
            ->latest('id')
            ->get();

        return response()->json([
            'data' => EmiApplicationResource::collection($items),
        ]);
    }

    public function approveApplication(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'application_id' => ['nullable', 'integer'],
            'request_id' => ['nullable', 'integer'],
            'subject' => ['required', 'string', 'max:255'],
            'to' => ['required', 'string'],
            'cc' => ['nullable', 'string'],
            'bcc' => ['nullable', 'string'],
            'file_name' => ['nullable', 'string', 'max:255'],
        ]);

        
        $applicationId = (int) ($validated['application_id'] ?? $id);
        $application = EmiApplicationModel::query()->findOrFail($applicationId);

        // dd($application);
        $to = $this->parseEmailList((string) $validated['to']);
        if (empty($to)) {
            return response()->json([
                'message' => 'A valid "to" email address is required.',
            ], 422);
        }
        $cc = $this->parseEmailList((string) ($validated['cc'] ?? ''));
        $bcc = $this->parseEmailList((string) ($validated['bcc'] ?? ''));
        
        $filePath = (string) ($application->file_path ?? '');
        $fileName = (string) ($validated['file_name'] ?? '');
        if ($fileName === '') {
            $fileName = basename($filePath);
        }
        // dd($filePath);

        try {
            Mail::send([], [], function ($message) use ($validated, $to, $cc, $bcc, $filePath, $fileName): void {
                $message
                    ->to($to)
                    ->subject((string) $validated['subject'])
                    ->html('<p>Your EMI application has been approved. Please find the attached file.</p>');

                if (!empty($cc)) {
                    $message->cc($cc);
                }

                if (!empty($bcc)) {
                    $message->bcc($bcc);
                }

                if ($filePath !== '' && Storage::disk(self::STORAGE_DISK)->exists($filePath)) {
                    $content = Storage::disk(self::STORAGE_DISK)->get($filePath);

                    $message->attachData($content, $fileName !== '' ? $fileName : 'emi-application.pdf', [
                        'mime' => 'application/pdf',
                    ]);
                }
            });
        } catch (Throwable $exception) {
            return response()->json([
                'message' => 'Failed to send approval email.',
                'error' => $exception->getMessage(),
            ], 500);
        }

        $application->status = 'approved';
        $application->save();

        $requestId = (int) ($validated['request_id'] ?? $application->emi_request_id ?? 0);
        if ($requestId > 0) {
            EmiRequestModel::query()
                ->where('id', $requestId)
                ->update(['status' => EmiRequestModel::STATUS_APPROVED]);
        }

        return response()->json([
            'message' => 'Approval email sent and application approved successfully.',
            'application_id' => $application->id,
            'request_id' => $requestId > 0 ? $requestId : null,
            'status' => $application->status,
        ]);
    }

    private function parseEmailList(string $value): array
    {
        $emails = array_filter(array_map('trim', explode(',', $value)));

        return array_values(array_filter($emails, static function (string $email): bool {
            return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
        }));
    }
}
