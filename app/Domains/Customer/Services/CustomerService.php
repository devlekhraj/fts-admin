<?php

declare(strict_types=1);

namespace App\Domains\Customer\Services;

use App\Domains\Customer\Actions\CustomerDetailAction;
use App\Domains\Customer\Actions\CustomerListAction;
use App\Domains\Customer\DTOs\CustomerListData;
use App\Domains\User\Models\User;

final class CustomerService
{
    public function __construct(
        private readonly CustomerListAction $listAction,
        private readonly CustomerDetailAction $detailAction,
    ) {
    }

    public function list(CustomerListData $data): array
    {
        return $this->listAction->execute($data);
    }

    public function detail(string $id): User
    {
        return $this->detailAction->execute($id);
    }
}
