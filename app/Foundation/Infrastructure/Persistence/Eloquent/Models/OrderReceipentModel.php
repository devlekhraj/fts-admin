<?php

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;



use App\Foundation\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use Illuminate\Database\Eloquent\Model;

class OrderReceipentModel extends Model
{
    protected $table = 'order_receipents';

    public function order(){
        return $this->belongsTo(OrderModel::class,'order_id');
    }
}
