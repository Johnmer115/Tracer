<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::composer('*', function ($view) {
            $userType = auth()->check() ? auth()->user()->usertype : null;

            $routePrefix = match ($userType) {
                'Staff_OSA' => 'staff_osa',
                'Branch_OSA' => 'branch_osa',
                default => 'dean_osa',
            };

            $layout = match ($userType) {
                'Staff_OSA' => 'Staff_OSA.layouts.layout',
                'Branch_OSA' => 'Branch_OSA.layouts.layout',
                default => 'Dean_OSA.layouts.layout',
            };

            $view->with([
                'routePrefix' => $routePrefix,
                'layout' => $layout,
            ]);
        });
    }
}
