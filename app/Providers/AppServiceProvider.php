<?php

namespace App\Providers;

use App\Domains\Admin\Models\Admin;
use App\Domains\EmiRequest\Models\EmiRequest;
use App\Domains\Order\Models\Order;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use table names as morph aliases (for ActivityLog entity/actor)
        Relation::morphMap([
            'orders'       => Order::class,
            'emi_requests' => EmiRequest::class,
            'users'        => User::class,
            'admins'       => Admin::class,
            // Legacy morph types (backward compatibility)
            'App\\Models\\User'  => User::class,
            'App\\Models\\Admin' => Admin::class,
        ]);

    }
}
