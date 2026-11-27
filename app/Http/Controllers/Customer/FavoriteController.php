<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Favorites\ToggleFavoriteAction;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $products = $request->user()
            ->favoriteProducts()
            ->active()
            ->with(['seller', 'category', 'brand', 'variants', 'media'])
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->latest('favorites.created_at')
            ->paginate(24);

        return view('customer.favorites', [
            'products' => $products,
        ]);
    }

    public function toggle(Request $request, Product $product, ToggleFavoriteAction $action): RedirectResponse
    {
        $added = $action->execute($request->user(), $product);

        return back()->with(
            'status',
            $added ? 'Ürün favorilerinize eklendi.' : 'Ürün favorilerinizden çıkarıldı.'
        );
    }
}
