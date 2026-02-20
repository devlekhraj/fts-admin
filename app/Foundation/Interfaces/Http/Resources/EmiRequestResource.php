<?php

declare(strict_types=1);

namespace App\Foundation\Interfaces\Http\Resources;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\EmiRequestModel;
use Illuminate\Http\Resources\Json\JsonResource;

class EmiRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'contact_number' => $this->contact_number,
            'address' => $this->address,
            'dob_ad' => $this->dob_ad,
            'dob_bs' => $this->dob_bs,
            'gender' => $this->gender,
            'credit_card' => $this->credit_card,
            'bank' => $this->bank,
            'length_of_employment' => $this->length_of_employment,
            'monthly_income' => $this->monthly_income,
            'no_of_dependents' => $this->no_of_dependents,
            'occupation' => $this->occupation,
            'residental_status' => $this->residental_status,
            'vehicle' => $this->vehicle,
            'emi_mode' => $this->emi_mode,
            'down_payment' => $this->down_payment,
            'finance_amount' => $this->finance_amount,
            'emi_per_month' => $this->emi_per_month,
            'product_attributes' => $this->product_attributes,
            'salary_certificate' => $this->salary_certificate,
            'citizenship' => $this->citizenship,
            'photo' => $this->photo,
            'product' => $this->product ? [
                'id' => $this->product->id,
                'name' => $this->product->name ?? null,
                'price' => $this->product->price ?? null,
            ] : null,
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name ?? null,
                'email' => $this->user->email ?? null,
                'mobile' => $this->contact_number ?? null,
                'avatar' => $this->user->avatar ?? null,
            ] : null,
            'product_price' => $this->product_price,
            'status' => EmiRequestModel::getStatusLabels()[$this->status] ?? 'Unknown',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'bank_statement' => $this->bank_statement,
            'card_holder_name' => $this->card_holder_name,
            'card_number' => $this->card_number,
            'card_expiry_date' => $this->card_expiry_date,
        ];
    }
}
