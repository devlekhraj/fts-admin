<?php

declare(strict_types=1);

namespace App\Domains\Setting\Services;

use App\Domains\Setting\Actions\LogoImagesListAction;

final class LogoService
{
    public function __construct(private readonly LogoImagesListAction $listAction)
    {
    }

    public function list(): array
    {
        return $this->listAction->execute();
    }
}

