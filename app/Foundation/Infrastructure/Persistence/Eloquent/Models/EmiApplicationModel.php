<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class EmiApplicationModel extends BaseModel
{
    protected $table = 'emi_applications';

    protected $casts = [
        'application_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function emiRequest()
    {
        return $this->belongsTo(EmiRequestModel::class, 'emi_request_id');
    }

    public function emiBank()
    {
        return $this->belongsTo(EmiBankModel::class, 'emi_bank_id');
    }
}
