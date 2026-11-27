<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Product $product): View
    {
        abort_if($product->status !== 'active', 404);

        $product->load(['seller', 'category.parent', 'brand', 'variants', 'media'])
            ->loadCount('approvedReviews')
            ->loadAvg('approvedReviews', 'rating');

        $relatedProducts = Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->with(['seller', 'category', 'brand', 'variants', 'media'])
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->latest('id')
            ->limit(4)
            ->get();

        return view('storefront.product', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
