<?php

namespace App\Actions\Catalog;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SearchProductsAction
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     products: LengthAwarePaginator<int, Product>,
     *     filterOptions: array{
     *         categories: Collection<int, Category>,
     *         brands: Collection<int, Brand>,
     *         colors: list<string>,
     *         sizes: list<string>
     *     }
     * }
     */
    public function execute(array $filters, ?Category $scopedCategory = null): array
    {
        $query = Product::query()
            ->active()
            ->with(['seller', 'category', 'brand', 'variants', 'media'])
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->addSelect([
                'effective_min_price' => ProductVariant::query()
                    ->selectRaw('MIN(COALESCE(discount_price, price) + 0)')
                    ->whereColumn('product_variants.product_id', 'products.id'),
            ]);

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('products.name', 'like', "%{$search}%")
                    ->orWhereHas('brand', function (Builder $brandQuery) use ($search): void {
                        $brandQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $categorySlug = trim((string) ($filters['kategori'] ?? ''));
        $targetCategory = $scopedCategory;
        if ($targetCategory === null && $categorySlug !== '') {
            $targetCategory = Category::query()->with('children')->where('slug', $categorySlug)->first();
        }

        if ($targetCategory !== null) {
            $targetCategory->loadMissing('children');
            $categoryIds = $targetCategory->children->pluck('id')->prepend($targetCategory->id)->all();
            $query->whereIn('products.category_id', $categoryIds);
        } elseif ($categorySlug !== '') {
            $query->whereRaw('1 = 0');
        }

        $brandSlug = trim((string) ($filters['marka'] ?? ''));
        if ($brandSlug !== '') {
            $query->whereHas('brand', function (Builder $brandQuery) use ($brandSlug): void {
                $brandQuery->where('slug', $brandSlug);
            });
        }

        $minPrice = $filters['min'] ?? null;
        $maxPrice = $filters['max'] ?? null;
        $color = trim((string) ($filters['renk'] ?? ''));
        $size = trim((string) ($filters['beden'] ?? ''));

        if (($minPrice !== null && $minPrice !== '') || ($maxPrice !== null && $maxPrice !== '') || $color !== '' || $size !== '') {
            $query->whereHas('variants', function (Builder $variantQuery) use ($minPrice, $maxPrice, $color, $size): void {
                if ($minPrice !== null && $minPrice !== '' && is_numeric($minPrice)) {
                    $variantQuery->whereRaw('(COALESCE(discount_price, price) + 0) >= (? + 0)', [(float) $minPrice]);
                }

                if ($maxPrice !== null && $maxPrice !== '' && is_numeric($maxPrice)) {
                    $variantQuery->whereRaw('(COALESCE(discount_price, price) + 0) <= (? + 0)', [(float) $maxPrice]);
                }

                if ($color !== '') {
                    $variantQuery->where('color', $color);
                }

                if ($size !== '') {
                    $variantQuery->where('size', $size);
                }
            });
        }

        $minRating = $filters['puan'] ?? null;
        if ($minRating !== null && $minRating !== '' && is_numeric($minRating)) {
            $ratingFloor = (float) $minRating;
            $query->whereRaw(
                '(SELECT COALESCE(AVG(reviews.rating), 0) FROM reviews WHERE reviews.product_id = products.id AND reviews.is_approved = 1) >= (? + 0)',
                [$ratingFloor]
            );
        }

        $sort = (string) ($filters['sirala'] ?? 'en-yeni');

        match ($sort) {
            'fiyat-artan' => $query->orderBy('effective_min_price', 'asc'),
            'fiyat-azalan' => $query->orderBy('effective_min_price', 'desc'),
            'en-cok-degerlendirilen' => $query
                ->orderByDesc('approved_reviews_count')
                ->orderByDesc('approved_reviews_avg_rating')
                ->latest('products.id'),
            default => $query->latest('products.id'),
        };

        $products = $query->paginate(24)->withQueryString();

        $variantPairs = ProductVariant::query()
            ->select(['color', 'size'])
            ->distinct()
            ->get();

        return [
            'products' => $products,
            'filterOptions' => [
                'categories' => $scopedCategory === null
                    ? Category::query()->orderBy('sort')->orderBy('name')->get()
                    : collect(),
                'brands' => Brand::query()->orderBy('name')->get(),
                'colors' => $variantPairs->pluck('color')->filter()->unique()->sort()->values()->all(),
                'sizes' => $variantPairs->pluck('size')->filter()->unique()->sort()->values()->all(),
            ],
        ];
    }
}
