<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Services;

use App\Domains\EmiRequest\Actions\EmiApplicationApproveAction;
use App\Domains\EmiRequest\Actions\EmiApplicationGeneratePdfAction;
use App\Domains\EmiRequest\Actions\EmiApplicationListAction;
use App\Domains\EmiRequest\Requests\GenerateEmiApplicationPdfRequest;
use Illuminate\Database\Eloquent\Collection;

final class EmiApplicationService
{
    public function __construct(
        private readonly EmiApplicationListAction $emiApplicationListAction,
        private readonly EmiApplicationGeneratePdfAction $emiApplicationGeneratePdfAction,
        private readonly EmiApplicationApproveAction $emiApplicationApproveAction,
    ) {}

    public function list(string $emiRequestId): Collection
    {
        return $this->emiApplicationListAction->execute($emiRequestId);
    }

    /**
     * @return array{status:int,payload:array}
     */
    public function generatePdf(GenerateEmiApplicationPdfRequest $request, string $emiRequestId): array
    {
        return $this->emiApplicationGeneratePdfAction->execute($request, $emiRequestId);
    }

    /**
     * @return array{status:int,payload:array}
     */
    public function approve(array $validated, string $applicationId): array
    {
        return $this->emiApplicationApproveAction->execute($validated, $applicationId);
    }
}

