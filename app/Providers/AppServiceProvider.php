<?php

namespace App\Providers;

use App\Support\Locales;
use App\Support\Seo;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Seo::class);
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        Route::pattern('locale', Locales::routePattern());
    }
}
