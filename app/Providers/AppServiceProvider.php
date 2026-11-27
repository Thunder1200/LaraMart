<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        ViewFacade::composer('components.layouts.storefront', function (View $view): void {
            $megaMenuCategories = Schema::hasTable('categories')
                ? Category::query()
                    ->whereNull('parent_id')
                    ->with('children')
                    ->orderBy('sort')
                    ->orderBy('name')
                    ->get()
                : collect();

            $view->with('megaMenuCategories', $megaMenuCategories);
        });
    }
}
