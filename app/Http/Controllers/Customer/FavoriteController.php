<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Favorites\ToggleFavoriteAction;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
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

    public function toggle(Request $request, Product $product, ToggleFavoriteAction $action): JsonResponse|RedirectResponse
    {
        $added = $action->execute($request->user(), $product);
        $message = $added ? 'Ürün favorilerinize eklendi.' : 'Ürün favorilerinizden çıkarıldı.';

        if ($request->expectsJson()) {
            return response()->json([
                'favorited' => $added,
                'product_id' => $product->id,
                'message' => $message,
            ]);
        }

        return back()->with('status', $message);
    }
}
