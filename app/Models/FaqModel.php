<?php

namespace App\Models;

use App\Foundation\Infrastructure\Persistence\Eloquent\Models\ProductBrandModel;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\ProductCategoryModel;
use App\Foundation\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use Illuminate\Database\Eloquent\Model;

class FaqModel extends Model
{
    protected $table = 'faqs';

    protected $fillable = [
        'type',
        'type_id',
        'question',
        'answer',
    ];

    public function faqable()
    {
        return $this->morphTo(__FUNCTION__, 'type', 'type_id');
    }

    public function brand()
    {
        return $this->belongsTo(ProductBrandModel::class, 'type_id');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategoryModel::class, 'type_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'type_id');
    }
}
