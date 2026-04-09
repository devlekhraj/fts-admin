<?php

namespace App\Models;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\EmiBankModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmiRequestCreditCard extends Model
{
    
    use SoftDeletes;
    protected $fillable = [
        'card_number',
        'card_holder',
        'card_provider',
        'expiry_date',
        'credit_limit',
    ];

    public function cardProvider()
    {
        return $this->belongsTo(EmiBankModel::class, 'card_provider');
    }
}
