<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\SubOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Product $product): View
    {
        abort_if($product->status !== 'active', 404);

        $product->load([
            'seller',
            'category.parent',
            'brand',
            'variants',
            'media',
            'approvedReviews.user',
        ])
            ->loadCount('approvedReviews')
            ->loadAvg('approvedReviews', 'rating');

        $eligibleOrderItem = null;
        $pendingUserReview = null;
        $user = Auth::user();

        if ($user !== null) {
            $eligibleOrderItem = OrderItem::query()
                ->whereHas('variant', fn (Builder $q) => $q->where('product_id', $product->id))
                ->whereHas('subOrder', function (Builder $subQuery) use ($user): void {
                    $subQuery
                        ->where('status', SubOrderStatus::Delivered->value)
                        ->whereHas('order', fn (Builder $orderQuery) => $orderQuery->where('user_id', $user->id));
                })
                ->whereDoesntHave('review')
                ->latest('id')
                ->first();

            if ($eligibleOrderItem === null) {
                $pendingUserReview = Review::query()
                    ->where('user_id', $user->id)
                    ->where('product_id', $product->id)
                    ->where('is_approved', false)
                    ->latest('id')
                    ->first();
            }
        }

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
            'eligibleOrderItem' => $eligibleOrderItem,
            'pendingUserReview' => $pendingUserReview,
        ]);
    }
}
