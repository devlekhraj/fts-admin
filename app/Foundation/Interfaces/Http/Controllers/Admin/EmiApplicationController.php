<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Foundation\Interfaces\Http\Resources\EmiRequestResource;
use App\Foundation\Interfaces\Http\Requests\Admin\StoreEmiRequestRequest;
use App\Foundation\Interfaces\Http\Requests\Admin\UpdateEmiRequestRequest;
use App\Foundation\Interfaces\Http\Requests\Admin\ApproveEmiRequestRequest;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\EmiRequestModel;
use App\Foundation\Shared\Support\Formatters\NumberToWords;
use App\Foundation\Shared\Support\Formatters\PriceFormatter;

class EmiApplicationController extends Controller
{
    public function generateApplication(string $id): Response|JsonResponse
    {
        $application = EmiRequestModel::query()
            ->with(['product.brand', 'bank'])
            ->findOrFail($id);

        $product = $application->product;
        $brandName = $product?->brand?->name ?? 'N/A';
        $cardNumber = preg_replace('/[^0-9]/', '', (string) $application->card_number);
        $expiryDate = str_replace('/', '', (string) $application->card_expiry_date);
        $tenures = $application->bank?->tenures ?? [];
        $defaultImage = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';
        $requestedBank = strtolower(trim((string) request()->input('bank', '')));
        $applicationBank = strtolower(trim((string) ($application->bank?->slug ?? $application->bank?->name ?? '')));
        $bank = $requestedBank !== '' ? $requestedBank : $applicationBank;

        $view = 'emi.emi-sbl';
        $logo = asset('logo/sbl-logo.png');

        switch ($bank) {
            case 'nabil':
            case 'nabil bank':
                $view = 'emi.emi-nabil';
                $logo = asset('logo/nabil.png');
                break;

            case 'sbl':
            case 'siddhartha':
            case 'siddhartha bank':
            default:
                $view = 'emi.emi-sbl';
                $logo = asset('logo/sbl-logo.png');
                break;
        }

        $data = [

            'title' => 'PDF Title',
            'content' => 'This is the content of the PDF.',
            'logo' => $logo,
            'application' => $application,

            'product_name' => $product?->name ?? 'N/A',
            'manufacturer' => $brandName,
            'model_no' => 'n/a',
            'serial_no' => 'n/a',
            'down_payment' => PriceFormatter::format((float) ($application->down_payment ?? 0)),
            'down_payment_words' => NumberToWords::npr((float) ($application->down_payment ?? 0)),

            'name_address_merchant' => 'Fatafat Sewa Pvt. Ltd, Sitapaila, Kathmandu',
            'merchant_name' => 'Jiban Kumar Shrestha',
            'merchant_phone' => '9813001000 / 9828757575',
            'merchant_signature' => $defaultImage,

            'name' => $application->name ?? 'N/A',
            'card_holder_name' => $application->card_holder_name ?? 'N/A',
            'card_number' => $cardNumber,
            'expiry_date' => $expiryDate,
            'telephone_no' => 'n/a',
            'mobile_no' => $application->contact_number ?? 'N/A',
            'email' => $application->email ?? 'N/A',
            'selected_tenure' => $application->emi_mode ?? 'N/A',

            'emi_tenures' => $tenures,

            'photo' => $defaultImage,
            'signature' => $defaultImage,
            'date' => optional($application->created_at)->format('F j, Y') ?? '',
        ];

        $pdf = Pdf::loadView($view, $data)
            ->setPaper('a4', 'portrait'); // 👈 A4 size, Portrait orientation

        $fileName = 'emi-application-' . $application->id . '.pdf';
        $relativePath = 'applications/' . $fileName;

        Storage::disk('cdn')->put($relativePath, $pdf->output());

        return response()->json([
            'path' => Storage::disk('cdn')->path($relativePath),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $record = EmiRequestModel::query()->findOrFail($id);

        return response()->json(new EmiRequestResource($record));
    }

    public function store(StoreEmiRequestRequest $request): JsonResponse
    {
        // TODO: Create EMI request.
        return response()->json([], 201);
    }

    public function update(string $id, UpdateEmiRequestRequest $request): JsonResponse
    {
        // TODO: Update EMI request.
        return response()->json(['id' => $id]);
    }

    public function approve(string $id, ApproveEmiRequestRequest $request): JsonResponse
    {
        // TODO: Approve EMI request.
        return response()->json(['id' => $id]);
    }

    private function buildPdfHtml(EmiRequestModel $record): string
    {
        $userName = e($record->user?->name ?? 'N/A');
        $userEmail = e($record->user?->email ?? 'N/A');
        $userPhone = e($record->user?->phone ?? 'N/A');
        $productName = e($record->product?->name ?? 'N/A');
        $productSku = e($record->product?->sku ?? 'N/A');
        $applicationCode = e($record->application_code ?? $record->id);
        $emiType = e($record->emi_type ?? 'N/A');
        $emiMode = e($record->emi_mode ?? 'N/A');
        $emiPerMonth = e((string) ($record->emi_per_month ?? 'N/A'));
        $downPayment = e((string) ($record->down_payment ?? 'N/A'));
        $status = e($record->status_label ?? $record->status ?? 'N/A');
        $createdAt = e(optional($record->created_at)->format('Y-m-d H:i'));

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EMI Request {$applicationCode}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 12px; }
        h2 { font-size: 14px; margin: 16px 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        .muted { color: #666; }
    </style>
</head>
<body>
    <h1>EMI Request</h1>
    <div class="muted">Request #: {$applicationCode}</div>
    <div class="muted">Created: {$createdAt}</div>

    <h2>User</h2>
    <table>
        <tr><th>Name</th><td>{$userName}</td></tr>
        <tr><th>Email</th><td>{$userEmail}</td></tr>
        <tr><th>Phone</th><td>{$userPhone}</td></tr>
    </table>

    <h2>Product</h2>
    <table>
        <tr><th>Name</th><td>{$productName}</td></tr>
        <tr><th>SKU</th><td>{$productSku}</td></tr>
    </table>

    <h2>EMI Details</h2>
    <table>
        <tr><th>Type</th><td>{$emiType}</td></tr>
        <tr><th>Mode</th><td>{$emiMode}</td></tr>
        <tr><th>Per Month</th><td>{$emiPerMonth}</td></tr>
        <tr><th>Down Payment</th><td>{$downPayment}</td></tr>
        <tr><th>Status</th><td>{$status}</td></tr>
    </table>
</body>
</html>
HTML;
    }
}
