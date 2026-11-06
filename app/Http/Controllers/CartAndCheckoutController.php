<?php

namespace App\Http\Controllers;

use App\Actions\CreateOrderAction;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\CheckoutRequest;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Services\CalculateCartTotalsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CartAndCheckoutController extends Controller
{
    public function __construct(
        private readonly CalculateCartTotalsService $cartService,
        private readonly CreateOrderAction $createOrderAction,
    ) {
    }

    public function showCart(): View
    {
        $cart = $this->cartService->resolveCart(Auth::user(), Session::getId());
        $summary = $this->cartService->calculate($cart);

        return view('storefront.cart', [
            'summary' => $summary,
        ]);
    }

    public function addToCart(AddToCartRequest $request): RedirectResponse
    {
        $variant = ProductVariant::findOrFail($request->validated('variant_id'));
        $cart = $this->cartService->resolveCart(Auth::user(), Session::getId());

        try
        {
            $this->cartService->addItem($cart, $variant, (int) $request->validated('quantity'));

            return back()->with('success', 'Ürün sepetinize eklendi.');
        }
        catch (\Throwable $e)
        {
            return back()->withErrors(['cart' => $e->getMessage()]);
        }
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);
        $cart = $this->cartService->resolveCart(Auth::user(), Session::getId());

        try
        {
            $coupon = $this->cartService->applyCoupon($cart, (string) $request->input('code'));

            return back()->with('success', "{$coupon->code} kuponu sepetinize uygulandı.");
        }
        catch (\Throwable $e)
        {
            return back()->withErrors(['coupon' => $e->getMessage()]);
        }
    }

    public function showCheckout(): View|RedirectResponse
    {
        $user = Auth::user();
        $cart = $this->cartService->resolveCart($user, Session::getId());
        $summary = $this->cartService->calculate($cart);

        if ($summary['item_count'] === 0)
        {
            return redirect('/sepet')->withErrors(['cart' => 'Sepetiniz boş.']);
        }

        $addresses = $user->addresses()->orderByDesc('is_default')->get();
        $idempotencyKey = (string) Str::uuid();

        return view('storefront.checkout', [
            'summary' => $summary,
            'addresses' => $addresses,
            'idempotencyKey' => $idempotencyKey,
        ]);
    }

    public function processCheckout(CheckoutRequest $request): RedirectResponse
    {
        $user = $request->user();
        $cart = $this->cartService->resolveCart($user, Session::getId());
        $address = Address::where('user_id', $user->id)->findOrFail($request->validated('address_id'));

        try
        {
            $order = $this->createOrderAction->execute(
                $user,
                $cart,
                $address,
                $request->validated()
            );

            return redirect('/hesabim/siparisler/' . $order->id)
                ->with('success', "Siparişiniz (#{$order->order_number}) başarıyla oluşturuldu!");
        }
        catch (\Throwable $e)
        {
            return redirect('/odeme/hata')->with('payment_error', $e->getMessage());
        }
    }

    public function paymentError(): View
    {
        return view('storefront.payment-error', [
            'errorMessage' => session('payment_error', 'Ödeme işlemi sırasında bir hata oluştu.'),
        ]);
    }
}

