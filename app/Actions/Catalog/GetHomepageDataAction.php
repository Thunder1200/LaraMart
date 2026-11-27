<?php

namespace App\Actions\Catalog;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

class GetHomepageDataAction
{
    /**
     * @return array{
     *     banners: Collection<int, Banner>,
     *     featuredCategories: Collection<int, Category>,
     *     bestSellers: Collection<int, Product>,
     *     newArrivals: Collection<int, Product>
     * }
     */
    public function execute(): array
    {
        $banners = Banner::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->get();

        $featuredCategories = Category::query()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort')
            ->limit(6)
            ->get();

        $bestSellers = Product::query()
            ->active()
            ->with(['seller', 'category', 'brand', 'variants', 'media'])
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->orderByDesc('approved_reviews_count')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $newArrivals = Product::query()
            ->active()
            ->with(['seller', 'category', 'brand', 'variants', 'media'])
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->latest('id')
            ->limit(8)
            ->get();

        return [
            'banners' => $banners,
            'featuredCategories' => $featuredCategories,
            'bestSellers' => $bestSellers,
            'newArrivals' => $newArrivals,
        ];
    }
}
