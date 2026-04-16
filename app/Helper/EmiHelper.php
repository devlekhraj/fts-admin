<?php

namespace App\Helper;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\EmiRequestModel;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Storage;

class EmiHelper
{
    public function generateEmiPdf(EmiRequestModel $emiRequest)
    {
        try {
            $pdf_path = "public/uploads/emi-requests/{$emiRequest->id}/emi-user-quotation.pdf";
            $check_existance = Storage::disk('local')->exists($pdf_path);
            if ($check_existance) {
                Storage::disk('local')->delete($pdf_path);
            }

            $folder_exists = Storage::disk('local')->exists("public/uploads/emi-requests/{$emiRequest->id}");
            if (! $folder_exists) {
                Storage::disk('local')->makeDirectory("public/uploads/emi-requests/{$emiRequest->id}");
            }
            $real_path = Storage::disk('local')->path($pdf_path);
            $pdf = PDF::loadView(resolveView('ecommerce::pdfs.emi-quotation'), ['emiRequest' => $emiRequest])->save($real_path);
            // return PDF::loadView(resolveView('ecommerce::pdfs.emi-quotation'), ['emiRequest' => $emiRequest])->stream($real_path);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
