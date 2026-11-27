<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function show(Request $request, Seller $seller): View
    {
        abort_if($seller->status !== 'approved', 404);

        $sort = (string) $request->query('sirala', 'en-yeni');

        $query = Product::query()
            ->active()
            ->where('seller_id', $seller->id)
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
            'en-cok-degerlendirilen' => $query->orderByDesc('approved_reviews_count')->latest('products.id'),
            default => $query->latest('products.id'),
        };

        return view('storefront.store', [
            'seller' => $seller,
            'sort' => $sort,
            'products' => $query->paginate(24)->withQueryString(),
        ]);
    }
}
