<?php

declare(strict_types=1);

namespace App\Domains\Faq\Services;

use App\Domains\Faq\Actions\FaqListAction;
use App\Domains\Faq\DTOs\FaqListData;

final class FaqService
{
    public function __construct(private readonly FaqListAction $listAction)
    {
    }

    public function list(FaqListData $data): array
    {
        return $this->listAction->execute($data);
    }
}

