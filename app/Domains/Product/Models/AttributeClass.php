<?php

declare(strict_types=1);

namespace App\Domains\Product\Models;

use App\Support\Eloquent\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AttributeClass extends BaseModel
{
    protected $table = 'attribute_classes';

    protected $fillable = [
        'name',
    ];

    public function attributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class, 'class_id');
    }
}
