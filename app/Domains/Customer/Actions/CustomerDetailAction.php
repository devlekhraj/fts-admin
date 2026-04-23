<?php

declare(strict_types=1);

namespace App\Domains\Customer\Actions;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class CustomerDetailAction
{
    public function execute(string $id): User
    {
        return $this->withCountColumns(
            User::query()->with('shippingAddress')
        )->findOrFail($id);
    }

    private function withCountColumns(Builder $query): Builder
    {
        $query->select('users.*');

        if (Schema::hasTable('orders')) {
            $query->selectSub(
                DB::table('orders')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('orders.user_id', 'users.id'),
                'total_order'
            );
        } else {
            $query->selectRaw('0 as total_order');
        }

        if (Schema::hasTable('emi_requests')) {
            $query->selectSub(
                DB::table('emi_requests')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('emi_requests.user_id', 'users.id'),
                'total_emi'
            );
        } else {
            $query->selectRaw('0 as total_emi');
        }

        return $query;
    }
}
