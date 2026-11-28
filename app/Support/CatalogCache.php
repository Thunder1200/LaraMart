<?php

namespace App\Support;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CatalogCache
{
    private const VERSION_KEY = 'laramart.catalog.version';

    private const TTL_SECONDS = 600;

    public static function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }

    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }

    /**
     * @return Collection<int, Category>
     */
    public static function megaMenuCategories(): Collection
    {
        $key = 'laramart.catalog.v'.self::version().'.mega_menu';

        return Cache::remember($key, self::TTL_SECONDS, static function (): Collection {
            return Category::query()
                ->whereNull('parent_id')
                ->with('children')
                ->orderBy('sort')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * @return Collection<int, Banner>
     */
    public static function homepageBanners(): Collection
    {
        $key = 'laramart.catalog.v'.self::version().'.banners';

        return Cache::remember($key, self::TTL_SECONDS, static function (): Collection {
            return Banner::query()
                ->where('is_active', true)
                ->orderBy('sort')
                ->get();
        });
    }

    /**
     * @param  list<int>|null  $categoryIds
     * @param  array<string, array{label: string, options: list<string>}>  $attributeFilters
     * @return array{
     *     categories: Collection<int, Category>,
     *     brands: Collection<int, Brand>,
     *     colors: list<string>,
     *     sizes: list<string>,
     *     supports_size: bool,
     *     attribute_filters: array<string, array{label: string, options: list<string>}>
     * }
     */
    public static function filterOptions(
        ?Category $targetCategory,
        ?array $categoryIds,
        bool $includeAllCategories,
        bool $supportsSize,
        array $attributeFilters
    ): array {
        $scopeKey = $targetCategory !== null ? 'cat_'.$targetCategory->id : 'all';
        $catFlag = $includeAllCategories ? '1' : '0';
        $key = 'laramart.catalog.v'.self::version().".filters.{$scopeKey}.{$catFlag}";

        $cached = Cache::remember($key, self::TTL_SECONDS, static function () use ($categoryIds, $includeAllCategories, $supportsSize): array {
            $variantPairsQuery = ProductVariant::query()->select(['color', 'size'])->distinct();
            if ($categoryIds !== null) {
                $variantPairsQuery->whereHas('product', fn (Builder $p) => $p->active()->whereIn('category_id', $categoryIds));
            }
            $variantPairs = $variantPairsQuery->get();

            $brandsQuery = Brand::query()->orderBy('name');
            if ($categoryIds !== null) {
                $brandsQuery->whereHas('products', fn (Builder $p) => $p->active()->whereIn('category_id', $categoryIds));
            }

            return [
                'categories' => $includeAllCategories
                    ? Category::query()->orderBy('sort')->orderBy('name')->get()
                    : collect(),
                'brands' => $brandsQuery->get(),
                'colors' => $variantPairs->pluck('color')->filter()->unique()->sort()->values()->all(),
                'sizes' => $supportsSize
                    ? $variantPairs->pluck('size')->filter(fn (mixed $s): bool => is_string($s) && $s !== '' && $s !== 'Standart')->unique()->sort()->values()->all()
                    : [],
            ];
        });

        return [
            'categories' => $cached['categories'],
            'brands' => $cached['brands'],
            'colors' => $cached['colors'],
            'sizes' => $cached['sizes'],
            'supports_size' => $supportsSize,
            'attribute_filters' => $attributeFilters,
        ];
    }
}
