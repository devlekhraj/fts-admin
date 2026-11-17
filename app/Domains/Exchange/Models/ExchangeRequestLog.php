<?php

declare(strict_types=1);

namespace App\Domains\Exchange\Models;

use Illuminate\Database\Eloquent\Model;

final class ExchangeRequestLog extends Model
{
    public $timestamps = false;

    protected $table = 'exchange_request_logs';

    protected $fillable = ['exchange_request_id', 'from_status', 'to_status', 'changed_by', 'changed_by_type', 'note', 'meta', 'created_at'];

    protected $casts = ['meta' => 'array'];
}
