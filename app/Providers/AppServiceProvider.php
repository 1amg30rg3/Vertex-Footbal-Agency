<?php

namespace App\Providers;

use App\Support\Locales;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::useTailwind();

        Route::pattern('locale', Locales::routePattern());

        View::composer('app', function ($view) {
            $view->with('initialTheme', request()->user()?->theme ?? 'dark');
        });
    }
}
