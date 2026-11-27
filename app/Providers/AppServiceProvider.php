<?php

namespace App\Providers;

use App\Listeners\MergeGuestCartOnLogin;
use App\Models\Category;
use App\Services\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
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
        Event::listen(Login::class, MergeGuestCartOnLogin::class);

        ViewFacade::composer('components.layouts.storefront', function (View $view): void {
            $megaMenuCategories = Category::query()
                ->whereNull('parent_id')
                ->with('children')
                ->orderBy('sort')
                ->orderBy('name')
                ->get();

            $user = Auth::user();
            $cartItemCount = app(CartService::class)->totalItemCount($user);

            $favoriteProductIds = $user !== null
                ? $user->favorites()->pluck('product_id')->all()
                : [];

            $view->with([
                'megaMenuCategories' => $megaMenuCategories,
                'cartItemCount' => $cartItemCount,
                'favoriteProductIds' => $favoriteProductIds,
            ]);
        });
    }
}
