<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Controllers;

use App\Domains\EmiRequest\Models\EmiRequest;
use Illuminate\Routing\Controller;

use App\Support\Formatters\NumberToWords;
use App\Support\Formatters\PriceFormatter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

final class EmiRequestApplicationController extends Controller
{
    private string $disk;

    public function __construct()
    {
        $this->disk = (string) config('filesystems.default');
    }

    public function generateApplication(string $id): Response|JsonResponse
    {
        $application = EmiRequest::query()
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
            ->setPaper('a4', 'portrait');

        $fileName = 'emi-application-' . $application->id . '.pdf';
        $relativePath = 'applications/' . $fileName;

        Storage::disk($this->disk)->put($relativePath, $pdf->output());

        return response()->json([
            'path' => Storage::disk($this->disk)->path($relativePath),
        ]);
    }
}
