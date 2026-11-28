<?php

namespace App\Providers;

use App\Listeners\MergeGuestCartOnLogin;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Support\CatalogCache;
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

        $flushCatalogCache = static function (): void {
            CatalogCache::flush();
        };

        foreach ([Category::class, Product::class, ProductVariant::class, Banner::class, Brand::class] as $modelClass) {
            $modelClass::saved($flushCatalogCache);
            $modelClass::deleted($flushCatalogCache);
        }

        ViewFacade::composer('components.layouts.storefront', function (View $view): void {
            $megaMenuCategories = CatalogCache::megaMenuCategories();

            $user = Auth::user();
            $cartItemCount = app(CartService::class)->totalItemCount($user);

            $view->with([
                'megaMenuCategories' => $megaMenuCategories,
                'cartItemCount' => $cartItemCount,
            ]);
        });

        ViewFacade::composer(
            ['components.layouts.storefront', 'components.storefront.product-card'],
            function (View $view): void {
                $user = Auth::user();

                if ($user === null) {
                    $view->with('favoriteProductIds', []);

                    return;
                }

                $cacheKey = 'laramart.favorite_product_ids.'.$user->getAuthIdentifier();
                $request = request();

                if (! $request->attributes->has($cacheKey)) {
                    $favoriteIds = array_map(
                        static fn (mixed $id): int => (int) $id,
                        $user->favorites()->pluck('product_id')->all(),
                    );
                    $request->attributes->set($cacheKey, $favoriteIds);
                }

                $view->with('favoriteProductIds', $request->attributes->get($cacheKey));
            }
        );
    }
}
