<?php

declare(strict_types=1);

namespace App\Domains\ProductBrand\Models;
use Illuminate\Database\Eloquent\Relations\Pivot;

final class BrandCategory extends Pivot
{
    protected $table = 'brand_category';
}
