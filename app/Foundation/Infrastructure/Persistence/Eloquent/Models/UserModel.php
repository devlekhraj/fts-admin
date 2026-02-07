<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Models;

use App\Foundation\Shared\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class UserModel extends BaseModel
{
    protected $table = 'users';

    // TODO: Define fillable, casts, relations.
}
