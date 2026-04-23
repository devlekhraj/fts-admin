<?php

declare(strict_types=1);

namespace App\Domains\Campaign\Actions;

use App\Domains\Campaign\Models\Campaign;
use Illuminate\Database\Eloquent\Collection;

final class CampaignListAction
{
    public function execute(?string $name): Collection
    {
        $query = Campaign::query()
            ->with(['defaultFile'])
            ->withCount('products')
            ->orderByDesc('created_at');

        if (is_string($name) && trim($name) !== '') {
            $query->where('title', 'like', '%'.trim($name).'%');
        }

        return $query->get();
    }
}

