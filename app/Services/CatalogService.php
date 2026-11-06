<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogService
{
    public function getMegaMenuCategories(): Collection
    {
        return Cache::remember('mega_menu_categories', 3600, function ()
        {
            return Category::query()
                ->whereNull('parent_id')
                ->with('children')
                ->orderBy('sort')
                ->get();
        });
    }

    public function getHomePageBlocks(): array
    {
        return Cache::remember('home_page_blocks', 3600, function ()
        {
            $banners = Banner::query()
                ->where('is_active', true)
                ->orderBy('sort')
                ->get();

            $featuredCategories = Category::query()
                ->whereNull('parent_id')
                ->with('children')
                ->orderBy('sort')
                ->take(6)
                ->get();

            $newArrivals = Product::query()
                ->active()
                ->with(['seller', 'category', 'brand', 'variants', 'approvedReviews', 'media'])
                ->latest('id')
                ->take(8)
                ->get();

            $bestSellers = Product::query()
                ->active()
                ->with(['seller', 'category', 'brand', 'variants', 'approvedReviews', 'media'])
                ->orderBy('id')
                ->take(8)
                ->get();

            return [
                'banners' => $banners,
                'featuredCategories' => $featuredCategories,
                'newArrivals' => $newArrivals,
                'bestSellers' => $bestSellers,
            ];
        });
    }

    public function filterProducts(array $filters = [], int $perPage = 24): LengthAwarePaginator
    {
        $query = Product::query()
            ->active()
            ->with(['seller', 'category', 'brand', 'variants', 'approvedReviews', 'media']);

        if (! empty($filters['q']))
        {
            $term = trim((string) $filters['q']);
            $query->where(function ($subQuery) use ($term)
            {
                $subQuery->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhereHas('brand', function ($brandQuery) use ($term)
                    {
                        $brandQuery->where('name', 'like', "%{$term}%");
                    });
            });
        }

        if (! empty($filters['kategori']))
        {
            $categorySlug = (string) $filters['kategori'];
            $category = Category::where('slug', $categorySlug)->with('children')->first();

            if ($category)
            {
                $categoryIds = collect([$category->id])
                    ->merge($category->children->pluck('id'))
                    ->all();

                $query->whereIn('category_id', $categoryIds);
            }
        }

        if (! empty($filters['marka']))
        {
            $brandValue = (string) $filters['marka'];
            $query->whereHas('brand', function ($brandQuery) use ($brandValue)
            {
                $brandQuery->where('slug', $brandValue)
                    ->orWhere('name', $brandValue);
            });
        }

        if (! empty($filters['min']) || ! empty($filters['max']) || ! empty($filters['renk']) || ! empty($filters['beden']))
        {
            $query->whereHas('variants', function ($variantQuery) use ($filters)
            {
                if (! empty($filters['min']))
                {
                    $variantQuery->where('price', '>=', (float) $filters['min']);
                }

                if (! empty($filters['max']))
                {
                    $variantQuery->where('price', '<=', (float) $filters['max']);
                }

                if (! empty($filters['renk']))
                {
                    $variantQuery->where('color', (string) $filters['renk']);
                }

                if (! empty($filters['beden']))
                {
                    $variantQuery->where('size', (string) $filters['beden']);
                }
            });
        }

        if (! empty($filters['puan']))
        {
            $minRating = (float) $filters['puan'];
            $query->whereHas('approvedReviews', function ($reviewQuery) use ($minRating)
            {
                $reviewQuery->select('product_id')
                    ->groupBy('product_id')
                    ->havingRaw('AVG(rating) >= ?', [$minRating]);
            });
        }

        $sort = $filters['sirala'] ?? 'yeni';

        if ($sort === 'fiyat_artan')
        {
            $query->withMin('variants', 'price')->orderBy('variants_min_price', 'asc');
        }
        elseif ($sort === 'fiyat_azalan')
        {
            $query->withMin('variants', 'price')->orderBy('variants_min_price', 'desc');
        }
        elseif ($sort === 'puan')
        {
            $query->withAvg('approvedReviews', 'rating')->orderByDesc('approved_reviews_avg_rating');
        }
        else
        {
            $query->latest('id');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function autocomplete(string $term): Collection
    {
        $clean = trim($term);

        if (mb_strlen($clean) < 2)
        {
            return collect();
        }

        return Product::query()
            ->active()
            ->with(['brand', 'variants'])
            ->where(function ($query) use ($clean)
            {
                $query->where('name', 'like', "%{$clean}%")
                    ->orWhereHas('brand', function ($brandQuery) use ($clean)
                    {
                        $brandQuery->where('name', 'like', "%{$clean}%");
                    });
            })
            ->take(6)
            ->get();
    }

    public function createSellerProduct(int $sellerId, array $data): Product
    {
        return DB::transaction(function () use ($sellerId, $data)
        {
            $product = Product::create([
                'seller_id' => $sellerId,
                'category_id' => $data['category_id'],
                'brand_id' => $data['brand_id'] ?? Brand::first()?->id,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']) . '-' . Str::lower(Str::random(5)),
                'description' => $data['description'],
                'status' => ProductStatus::tryFrom($data['status'] ?? 'active') ?? ProductStatus::Active,
            ]);

            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $data['sku'],
                'color' => $data['color'] ?? 'Standart',
                'size' => $data['size'] ?? 'Standart',
                'price' => number_format((float) $data['price'], 2, '.', ''),
                'discount_price' => ! empty($data['discount_price'])
                    ? number_format((float) $data['discount_price'], 2, '.', '')
                    : null,
                'stock' => (int) $data['stock'],
            ]);

            if (! empty($data['images']) && is_array($data['images']))
            {
                foreach ($data['images'] as $uploadedImage)
                {
                    $product->addMedia($uploadedImage)->toMediaCollection('products');
                }
            }

            return $product->load('variants');
        });
    }

    public function updateSellerProduct(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data)
        {
            $product->update([
                'category_id' => $data['category_id'],
                'brand_id' => $data['brand_id'] ?? $product->brand_id,
                'name' => $data['name'],
                'description' => $data['description'],
                'status' => ProductStatus::tryFrom($data['status'] ?? 'active') ?? $product->status,
            ]);

            $firstVariant = $product->variants()->first();

            if ($firstVariant)
            {
                $variantUpdate = [];

                if (isset($data['price']) && $data['price'] !== '')
                {
                    $variantUpdate['price'] = number_format((float) $data['price'], 2, '.', '');
                }

                if (array_key_exists('discount_price', $data))
                {
                    $variantUpdate['discount_price'] = $data['discount_price'] !== null && $data['discount_price'] !== ''
                        ? number_format((float) $data['discount_price'], 2, '.', '')
                        : null;
                }

                if (isset($data['stock']) && $data['stock'] !== '')
                {
                    $variantUpdate['stock'] = (int) $data['stock'];
                }

                if (! empty($variantUpdate))
                {
                    $firstVariant->update($variantUpdate);
                }
            }

            if (! empty($data['images']) && is_array($data['images']))
            {
                foreach ($data['images'] as $uploadedImage)
                {
                    $product->addMedia($uploadedImage)->toMediaCollection('products');
                }
            }

            return $product->fresh(['variants']);
        });
    }
}

