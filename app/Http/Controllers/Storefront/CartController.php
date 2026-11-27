<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\AddToCartRequest;
use App\Http\Requests\Storefront\ApplyCouponRequest;
use App\Http\Requests\Storefront\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Services\CartCalculator;
use App\Services\CartService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request, CartService $cartService, CartCalculator $calculator): View
    {
        $cart = $cartService->findCurrentCart($request->user());

        $items = $cart !== null
            ? $cart->items()
                ->with(['variant.product.seller', 'variant.product.brand', 'variant.product.media'])
                ->get()
            : collect();

        $appliedCouponCode = session()->get(CartService::APPLIED_COUPON_SESSION_KEY);
        $coupon = is_string($appliedCouponCode) && $appliedCouponCode !== ''
            ? Coupon::query()->where('code', $appliedCouponCode)->first()
            : null;

        $couponError = null;
        if ($coupon !== null) {
            try {
                $summary = $calculator->calculate($items, $coupon);
            } catch (DomainException $exception) {
                $couponError = $exception->getMessage();
                $coupon = null;
                session()->forget(CartService::APPLIED_COUPON_SESSION_KEY);
                $summary = $calculator->calculate($items);
            }
        } else {
            $summary = $calculator->calculate($items);
        }

        return view('storefront.cart', [
            'summary' => $summary,
            'appliedCoupon' => $coupon,
            'couponError' => $couponError,
        ]);
    }

    public function store(AddToCartRequest $request, CartService $cartService): RedirectResponse
    {
        $validated = $request->validated();
        $variant = ProductVariant::query()->findOrFail($validated['variant_id']);

        try {
            $cartService->addItem($request->user(), $variant, (int) $validated['quantity']);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('status', 'Ürün sepetinize eklendi.');
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem, CartService $cartService): RedirectResponse
    {
        try {
            $cartService->updateItemQuantity($request->user(), $cartItem, (int) $request->validated()['quantity']);
        } catch (DomainException $exception) {
            return redirect()->route('cart.index')->with('error', $exception->getMessage());
        }

        return redirect()->route('cart.index')->with('status', 'Sepet güncellendi.');
    }

    public function destroy(Request $request, CartItem $cartItem, CartService $cartService): RedirectResponse
    {
        $cartService->removeItem($request->user(), $cartItem);

        return redirect()->route('cart.index')->with('status', 'Ürün sepetten çıkarıldı.');
    }

    public function applyCoupon(
        ApplyCouponRequest $request,
        CartService $cartService,
        CartCalculator $calculator
    ): RedirectResponse {
        $code = (string) $request->validated()['code'];
        $coupon = Coupon::query()->where('code', $code)->first();

        if ($coupon === null) {
            return redirect()->route('cart.index')->with('error', 'Geçersiz kupon kodu.');
        }

        $cart = $cartService->findCurrentCart($request->user());
        $items = $cart !== null
            ? $cart->items()->with(['variant.product.seller'])->get()
            : collect();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Kupon uygulamak için sepetinizde ürün olmalıdır.');
        }

        $baseSummary = $calculator->calculate($items);

        try {
            $calculator->validateCoupon($coupon, $baseSummary['subtotal']);
        } catch (DomainException $exception) {
            return redirect()->route('cart.index')->with('error', $exception->getMessage());
        }

        session()->put(CartService::APPLIED_COUPON_SESSION_KEY, $coupon->code);

        return redirect()->route('cart.index')->with('status', "{$coupon->code} kuponu sepetinize uygulandı.");
    }

    public function removeCoupon(): RedirectResponse
    {
        session()->forget(CartService::APPLIED_COUPON_SESSION_KEY);

        return redirect()->route('cart.index')->with('status', 'Kupon sepetten kaldırıldı.');
    }
}
