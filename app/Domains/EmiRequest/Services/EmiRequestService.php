<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Services;

use App\Domains\EmiRequest\Actions\EmiRequestListAction;
use App\Domains\EmiRequest\Actions\EmiRequestShowAction;
use App\Domains\EmiRequest\DTOs\EmiRequestListData;
use App\Domains\EmiRequest\Models\EmiRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EmiRequestService
{
    public function __construct(
        private readonly EmiRequestListAction $emiRequestListAction,
        private readonly EmiRequestShowAction $emiRequestShowAction,
    ) {}

    public function list(EmiRequestListData $data): LengthAwarePaginator
    {
        return $this->emiRequestListAction->execute($data);
    }

    public function show(string $id): EmiRequest
    {
        return $this->emiRequestShowAction->execute($id);
    }
}

