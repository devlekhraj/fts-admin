<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use App\Foundation\Application\Emi\Commands\GenerateEmiApplicationPdfCommand;
use App\Foundation\Application\Emi\Handlers\GenerateEmiApplicationPdfHandler;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\EmiApplicationModel;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\EmiBankModel;
use App\Foundation\Interfaces\Http\Requests\Admin\GenerateEmiApplicationPdfRequest;
use App\Foundation\Interfaces\Http\Resources\EmiApplicationResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class EmiApplicationsController extends Controller
{
    public function __construct(
        private readonly GenerateEmiApplicationPdfHandler $handler,
    ) {}

    public function generatePdf(GenerateEmiApplicationPdfRequest $request, string $id): JsonResponse
    {
        $pdf = $this->handler->handle(new GenerateEmiApplicationPdfCommand($id));

        $uniqueSuffix = (string) random_int(100000, 999999);
        $filename = $pdf->filename;
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $nameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);
        $uniqueFilename = $extension !== ''
            ? $nameWithoutExtension . '-' . $uniqueSuffix . '.' . $extension
            : $filename . '-' . $uniqueSuffix;
        $relativePath = 'applications/' . $uniqueFilename;
        Storage::disk('cdn')->put($relativePath, $pdf->bytes);

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
            $signaturePath = 'applications/signatures/' . $signatureName;
            Storage::disk('cdn')->put($signaturePath, $signature->get());
            $applicationData['signature_file_path'] = $signaturePath;
        }

        $emiRequestId = (int) ($pdf->emiRequestId ?? $id);
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
        $application->application_id = (string) Str::uuid();
        $application->emi_request_id = $emiRequestId;
        $application->emi_bank_id = $bankId;

        $application->application_data = $applicationData;
        $application->file_path = $relativePath;
        $application->status = $application->status ?? 'pending';
        $application->save();

        return response()->json([
            'message' => 'EMI application PDF generated successfully.',
            'path' => Storage::disk('cdn')->path($relativePath),
            'signature_path' => $signaturePath,
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
}
