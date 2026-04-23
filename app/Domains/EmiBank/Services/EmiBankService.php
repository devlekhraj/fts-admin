<?php

declare(strict_types=1);

namespace App\Domains\EmiBank\Services;

use App\Domains\EmiBank\Actions\EmiBankListAction;
use App\Domains\EmiBank\Actions\EmiBankShowAction;
use App\Domains\EmiBank\Actions\EmiBankTenuresAction;
use App\Domains\EmiBank\DTOs\EmiBankListData;
use App\Domains\EmiBank\Models\EmiBank;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EmiBankService
{
    public function __construct(
        private readonly EmiBankListAction $emiBankListAction,
        private readonly EmiBankShowAction $emiBankShowAction,
        private readonly EmiBankTenuresAction $emiBankTenuresAction,
    ) {}

    public function list(EmiBankListData $data): LengthAwarePaginator
    {
        return $this->emiBankListAction->execute($data);
    }

    public function show(string $id): EmiBank
    {
        return $this->emiBankShowAction->execute($id);
    }

    public function tenures(string $id): array
    {
        return $this->emiBankTenuresAction->execute($id);
    }
}

