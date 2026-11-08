<?php

namespace App\Providers;

use App\Models\Requisition;
use App\Policies\RequisitionPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useTailwind();
        Gate::policy(Requisition::class, RequisitionPolicy::class);

        View::composer('layouts.app', function ($view) {
            $user = auth()->user();
            $view->with('navItems', $user ? \App\Support\Navigation::items($user) : []);
            $view->with('unreadNotifications', $user ? $user->unreadNotifications()->count() : 0);
        });
    }
}
