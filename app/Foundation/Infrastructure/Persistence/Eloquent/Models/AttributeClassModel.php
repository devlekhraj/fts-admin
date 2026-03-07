<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttributeClassModel extends BaseModel
{
    protected $table = 'attribute_classes';

    protected $fillable = [
        'name',
    ];

    public function attributes() : HasMany{
        return $this->hasMany(ProductAttributeModel::class,'class_id');
    }
}
