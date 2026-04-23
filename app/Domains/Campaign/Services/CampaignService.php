<?php

declare(strict_types=1);

namespace App\Domains\Campaign\Services;

use App\Domains\Campaign\Actions\CampaignCreateAction;
use App\Domains\Campaign\Actions\CampaignDeleteAction;
use App\Domains\Campaign\Actions\CampaignDetailAction;
use App\Domains\Campaign\Actions\CampaignListAction;
use App\Domains\Campaign\Actions\CampaignTogglePublishedAction;
use App\Domains\Campaign\Actions\CampaignUpdateAction;
use App\Domains\Campaign\DTOs\CampaignCreateData;
use App\Domains\Campaign\DTOs\CampaignUpdateData;
use App\Domains\Campaign\Models\Campaign;
use Illuminate\Database\Eloquent\Collection;

final class CampaignService
{
    public function __construct(
        private readonly CampaignListAction $listAction,
        private readonly CampaignDetailAction $detailAction,
        private readonly CampaignCreateAction $createAction,
        private readonly CampaignUpdateAction $updateAction,
        private readonly CampaignDeleteAction $deleteAction,
        private readonly CampaignTogglePublishedAction $togglePublishedAction,
    ) {
    }

    public function list(?string $name): Collection
    {
        return $this->listAction->execute($name);
    }

    public function detail(string $id): Campaign
    {
        return $this->detailAction->execute($id);
    }

    public function create(CampaignCreateData $data): Campaign
    {
        return $this->createAction->execute($data);
    }

    public function update(string $id, CampaignUpdateData $data): Campaign
    {
        $campaign = Campaign::query()->findOrFail($id);

        return $this->updateAction->execute($campaign, $data);
    }

    public function delete(string $id): void
    {
        $campaign = Campaign::query()->findOrFail($id);
        $this->deleteAction->execute($campaign);
    }

    public function togglePublished(string $id, bool $isPublished): Campaign
    {
        $campaign = Campaign::query()->findOrFail($id);

        return $this->togglePublishedAction->execute($campaign, $isPublished);
    }
}

