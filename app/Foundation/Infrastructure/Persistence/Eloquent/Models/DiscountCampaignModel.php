<?php

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;



use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountCampaignModel extends Model
{
    use SoftDeletes;
    protected $table = 'discount_campaigns';
    protected $fillable = [
      "title", "slug", "start_date", "end_date",'is_published'
    ];
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_published' => 'boolean',
    ];

    protected $appends = ['is_active'];
    public function getIsActiveAttribute()
    {
        $current_time = Carbon::now();
        if ($this->start_date < $current_time && $this->end_date > $current_time) {
            return true;
        }
        return false;
    }

    public function products(){
        return $this->hasMany(DiscountCampaignProductModel::class, 'campaign_id', 'id');
    }
}
