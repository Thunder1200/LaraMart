<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Services\CatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function __construct(private readonly CatalogService $catalogService)
    {
    }

    public function home(): View
    {
        $blocks = $this->catalogService->getHomePageBlocks();

        return view('storefront.home', $blocks);
    }

    public function search(Request $request): View
    {
        $filters = $request->only(['q', 'kategori', 'marka', 'min', 'max', 'renk', 'beden', 'puan', 'sirala']);
        $products = $this->catalogService->filterProducts($filters, 24);
        $brands = Brand::orderBy('name')->get();
        $categories = $this->catalogService->getMegaMenuCategories();

        return view('storefront.catalog', [
            'products' => $products,
            'brands' => $brands,
            'categories' => $categories,
            'currentCategory' => null,
            'filters' => $filters,
        ]);
    }

    public function category(string $slug, Request $request): View
    {
        $category = Category::where('slug', $slug)->with('children')->firstOrFail();
        $filters = array_merge(
            $request->only(['q', 'marka', 'min', 'max', 'renk', 'beden', 'puan', 'sirala']),
            ['kategori' => $category->slug]
        );

        $products = $this->catalogService->filterProducts($filters, 24);
        $brands = Brand::orderBy('name')->get();
        $categories = $this->catalogService->getMegaMenuCategories();

        return view('storefront.catalog', [
            'products' => $products,
            'brands' => $brands,
            'categories' => $categories,
            'currentCategory' => $category,
            'filters' => $filters,
        ]);
    }

    public function product(string $slug): View
    {
        $product = Product::where('slug', $slug)
            ->with([
                'seller',
                'category',
                'brand',
                'variants',
                'approvedReviews.user',
                'media',
            ])
            ->firstOrFail();

        return view('storefront.product-detail', [
            'product' => $product,
        ]);
    }

    public function sellerStore(string $slug): View
    {
        $seller = Seller::where('slug', $slug)->firstOrFail();
        $products = Product::query()
            ->active()
            ->where('seller_id', $seller->id)
            ->with(['seller', 'category', 'brand', 'variants', 'approvedReviews', 'media'])
            ->latest('id')
            ->paginate(24);

        return view('storefront.seller-store', [
            'seller' => $seller,
            'products' => $products,
        ]);
    }
}

