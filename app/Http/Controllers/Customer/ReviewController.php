<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Reviews\CreateReviewAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreReviewRequest;
use App\Models\OrderItem;
use App\Models\Product;
use DomainException;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function store(
        StoreReviewRequest $request,
        Product $product,
        CreateReviewAction $action
    ): RedirectResponse {
        $validated = $request->validated();
        $orderItem = OrderItem::query()->findOrFail((int) $validated['order_item_id']);

        try {
            $action->execute(
                $request->user(),
                $product,
                $orderItem,
                (int) $validated['rating'],
                (string) $validated['comment']
            );
        } catch (DomainException $exception) {
            return redirect()
                ->route('products.show', $product->slug)
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('products.show', $product->slug)
            ->with('status', 'Değerlendirmeniz alındı. Yönetici onayının ardından ürün sayfasında yayınlanacaktır.');
    }
}
