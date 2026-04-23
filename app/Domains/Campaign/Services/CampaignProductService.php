<?php

declare(strict_types=1);

namespace App\Domains\Campaign\Services;

use App\Domains\Campaign\Actions\CampaignAssignProductsAction;
use App\Domains\Campaign\Actions\CampaignListProductsAction;
use App\Domains\Campaign\Actions\CampaignRemoveCampaignProductAction;
use App\Domains\Campaign\Actions\CampaignUpdateCampaignProductAction;
use App\Domains\Campaign\Actions\CampaignUpdateProductsDiscountAction;
use App\Domains\Campaign\DTOs\CampaignAssignProductsData;
use App\Domains\Campaign\DTOs\CampaignDiscountUpdateData;
use App\Domains\Campaign\DTOs\CampaignProductUpdateData;
use App\Domains\Campaign\Models\Campaign;
use App\Domains\Campaign\Models\CampaignProduct;
use Illuminate\Database\Eloquent\Collection;

final class CampaignProductService
{
    public function __construct(
        private readonly CampaignAssignProductsAction $assignProductsAction,
        private readonly CampaignListProductsAction $listProductsAction,
        private readonly CampaignUpdateProductsDiscountAction $updateDiscountAction,
        private readonly CampaignUpdateCampaignProductAction $updateCampaignProductAction,
        private readonly CampaignRemoveCampaignProductAction $removeCampaignProductAction,
    ) {
    }

    public function assign(string $campaignId, CampaignAssignProductsData $data): Collection
    {
        $campaign = Campaign::query()->findOrFail($campaignId);

        return $this->assignProductsAction->execute($campaign, $data);
    }

    public function list(string $campaignId, ?string $name, int $perPage): array
    {
        $campaign = Campaign::query()->findOrFail($campaignId);

        return $this->listProductsAction->execute($campaign, $name, $perPage);
    }

    public function updateAllDiscounts(string $campaignId, CampaignDiscountUpdateData $data): void
    {
        $campaign = Campaign::query()->findOrFail($campaignId);
        $this->updateDiscountAction->execute($campaign, $data);
    }

    public function updateOne(string $campaignProductId, CampaignProductUpdateData $data): CampaignProduct
    {
        return $this->updateCampaignProductAction->execute($campaignProductId, $data);
    }

    public function removeOne(string $campaignProductId): void
    {
        $this->removeCampaignProductAction->execute($campaignProductId);
    }
}

