<?php

declare(strict_types=1);

namespace App\Domains\EmiBank\Actions;

use App\Domains\EmiBank\DTOs\EmiBankListData;
use App\Domains\EmiBank\Models\EmiBank;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EmiBankListAction
{
    public function execute(EmiBankListData $data): LengthAwarePaginator
    {
        return EmiBank::query()
            ->orderByDesc('created_at')
            ->paginate($data->perPage);
    }
}

