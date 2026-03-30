<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class OrderItemModel extends BaseModel
{
    protected $table = 'order_items';

    protected $casts = [
        'product_attributes' => 'array'
    ];
    public function order(){
        return $this->belongsTo(OrderModel::class,'order_id','id');
    }
    
    public function product(){
        return $this->belongsTo(ProductModel::class,'product_id','id');
    }
}
