<?php

namespace App\Actions\Catalog;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetCategoryProductsAction
{
    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function execute(Category $category, string $sort = 'en-yeni'): LengthAwarePaginator
    {
        $category->loadMissing('children');

        $categoryIds = $category->children
            ->pluck('id')
            ->prepend($category->id)
            ->all();

        $query = Product::query()
            ->active()
            ->whereIn('category_id', $categoryIds)
            ->with(['seller', 'category', 'brand', 'variants', 'media'])
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->addSelect([
                'effective_min_price' => ProductVariant::query()
                    ->selectRaw('MIN(COALESCE(discount_price, price))')
                    ->whereColumn('product_variants.product_id', 'products.id'),
            ]);

        match ($sort) {
            'fiyat-artan' => $query->orderBy('effective_min_price', 'asc'),
            'fiyat-azalan' => $query->orderBy('effective_min_price', 'desc'),
            'en-cok-degerlendirilen' => $query
                ->orderByDesc('approved_reviews_count')
                ->orderByDesc('approved_reviews_avg_rating')
                ->latest('id'),
            default => $query->latest('id'),
        };

        return $query->paginate(24)->withQueryString();
    }
}
