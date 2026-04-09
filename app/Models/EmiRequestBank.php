<?php

namespace App\Models;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\EmiBankModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmiRequestBank extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'bank_id',
        'account_number',
        'branch',
    ];

    public function bank()
    {
        return $this->belongsTo(EmiBankModel::class,'bank_id');
    }
}
