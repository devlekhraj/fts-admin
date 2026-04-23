<?php

declare(strict_types=1);

namespace App\Domains\Campaign\Models;

use App\Domains\Product\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class CampaignProduct extends Model
{
    protected $table = 'discount_campaign_products';
    protected $fillable = [
      'product_id', 'discount_type', 'discount_value', 'campaign_id'
    ];

    protected $appends = ['discount_type_label'];

    public function getDiscountTypeLabelAttribute(): string
    {
        return $this->discount_type == 1 ? 'fixed' : 'percentage';
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }
}
