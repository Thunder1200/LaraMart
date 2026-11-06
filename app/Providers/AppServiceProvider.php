<?php

namespace App\Providers;

use App\Services\CatalogService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        View::composer('layouts.app', function ($view)
        {
            $catalogService = app(CatalogService::class);
            $view->with('megaMenuCategories', $catalogService->getMegaMenuCategories());
        });
    }
}
