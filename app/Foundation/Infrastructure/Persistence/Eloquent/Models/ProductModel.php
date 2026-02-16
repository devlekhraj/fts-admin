<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class ProductModel extends BaseModel
{
    protected $table = "products";
    // TODO: Define table, fillable, casts, relations.

    public function brand(){
        return $this->belongsTo(BrandModel::class,'brand_id');
    }
}
