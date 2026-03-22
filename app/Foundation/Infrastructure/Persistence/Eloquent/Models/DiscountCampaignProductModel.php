<?php

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;



use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountCampaignProductModel extends Model
{
    protected $table = 'discount_campaign_products';
    protected $fillable = [
      'product_id', 'discount_type', 'discount_value', 'campaign_id'
    ];

    public function product()
    {
        return $this->hasOne(ProductModel::class, 'id', 'product_id');
    }

    public function campaign()
    {
        return $this->belongsTo(DiscountCampaignModel::class, 'campaign_id');
    }
}
