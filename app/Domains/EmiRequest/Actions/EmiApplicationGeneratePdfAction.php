<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Actions;

use App\Domains\EmiBank\Models\EmiBank;
use App\Domains\EmiRequest\Models\EmiApplication;
use App\Domains\EmiRequest\Requests\GenerateEmiApplicationPdfRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class EmiApplicationGeneratePdfAction
{
    private string $disk;

    public function __construct(
        private readonly GenerateEmiApplicationPdfAction $generateEmiApplicationPdfAction,
    ) {
        $this->disk = (string) config('filesystems.default');
    }

    /**
     * @return array{status:int,payload:array}
     */
    public function execute(GenerateEmiApplicationPdfRequest $request, string $id): array
    {
        $pdf = $this->generateEmiApplicationPdfAction->execute($id);
        $emiRequestId = (int) ($pdf->emiRequestId ?? $id);

        $uniqueSuffix = (string) random_int(100000, 999999);
        $filename = $pdf->filename;
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $nameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);
        $uniqueFilename = $extension !== ''
            ? $nameWithoutExtension . '-' . $uniqueSuffix . '.' . $extension
            : $filename . '-' . $uniqueSuffix;
        $relativePath = "emi-requests/{$emiRequestId}/applications/{$uniqueFilename}";
        Storage::disk($this->disk)->put($relativePath, $pdf->bytes);

        if ($pdf->emiBankId === null) {
            return [
                'status' => 422,
                'payload' => [
                    'message' => 'EMI bank not found for this request.',
                ],
            ];
        }

        $applicationData = $pdf->applicationData;
        if (is_object($applicationData)) {
            $applicationData = json_decode(json_encode($applicationData), true);
        }

        if (! is_array($applicationData)) {
            $applicationData = [];
        }

        $requestData = $request->except(['signature_file']);
        if (! empty($requestData)) {
            $applicationData['form'] = $requestData;
        }

        $signaturePath = null;
        if ($request->hasFile('signature_file')) {
            $signature = $request->file('signature_file');
            $signatureName = 'signature-' . (string) Str::uuid() . '.' . $signature->getClientOriginalExtension();
            $signaturePath = "emi/applications/{$emiRequestId}/signatures/{$signatureName}";
            Storage::disk($this->disk)->put($signaturePath, $signature->get());
            $applicationData['signature_file_path'] = $signaturePath;
        }

        $requestBankCode = (string) ($request->input('bank_code') ?? $request->input('bank') ?? '');
        $bankId = $pdf->emiBankId;
        if ($requestBankCode !== '') {
            $normalized = trim($requestBankCode);
            $bankRecord = EmiBank::query()
                ->where('bank_code', strtoupper($normalized))
                ->orWhere('name', $normalized)
                ->first();
            if ($bankRecord?->id) {
                $bankId = (int) $bankRecord->id;
            }
        }

        $application = new EmiApplication();
        $application->emi_request_id = $emiRequestId;
        $application->emi_bank_id = $bankId;

        $application->application_data = $applicationData;
        $application->file_path = $relativePath;
        $application->status = $application->status ?? 'generated';
        $application->save();

        return [
            'status' => 200,
            'payload' => [
                'message' => 'EMI application PDF generated successfully.',
                'path' => $relativePath,
                'file_url' => $this->buildDiskUrl($relativePath),
                'signature_path' => $signaturePath,
                'signature_url' => $signaturePath ? $this->buildDiskUrl($signaturePath) : null,
            ],
        ];
    }

    private function buildDiskUrl(string $relativePath): string
    {
        $baseUrl = trim((string) config('filesystems.disks.' . $this->disk . '.url', ''), '/');

        return $baseUrl !== '' ? $baseUrl . '/' . ltrim($relativePath, '/') : '/' . ltrim($relativePath, '/');
    }
}
