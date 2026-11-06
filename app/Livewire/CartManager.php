<?php

namespace App\Livewire;

use App\Models\CartItem;
use App\Services\CalculateCartTotalsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Component;

class CartManager extends Component
{
    public string $couponCode = '';
    public ?string $statusMessage = null;
    public ?string $errorMessage = null;

    #[On('cart-updated')]
    public function refreshCart(): void
    {
    }

    public function updateQuantity(int $cartItemId, int $quantity, CalculateCartTotalsService $cartService): void
    {
        $this->statusMessage = null;
        $this->errorMessage = null;

        $item = CartItem::with('variant')->find($cartItemId);

        if (! $item)
        {
            return;
        }

        try
        {
            $cartService->updateItemQuantity($item, $quantity);
            $this->statusMessage = 'Sepet güncellendi.';
        }
        catch (\Throwable $e)
        {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function removeItem(int $cartItemId): void
    {
        $this->statusMessage = null;
        $this->errorMessage = null;

        CartItem::where('id', $cartItemId)->delete();
        $this->statusMessage = 'Ürün sepetten çıkarıldı.';
    }

    public function applyCouponCode(CalculateCartTotalsService $cartService): void
    {
        $this->statusMessage = null;
        $this->errorMessage = null;

        try
        {
            $cart = $cartService->resolveCart(Auth::user(), Session::getId());
            $coupon = $cartService->applyCoupon($cart, $this->couponCode);
            $this->statusMessage = "{$coupon->code} kuponu başarıyla uygulandı.";
        }
        catch (\Throwable $e)
        {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function removeCoupon(CalculateCartTotalsService $cartService): void
    {
        $cart = $cartService->resolveCart(Auth::user(), Session::getId());
        $cart->update(['coupon_code' => null]);
        $this->couponCode = '';
        $this->statusMessage = 'Kupon kaldırıldı.';
    }

    public function render(CalculateCartTotalsService $cartService)
    {
        $cart = $cartService->resolveCart(Auth::user(), Session::getId());
        $summary = $cartService->calculate($cart);

        return view('livewire.cart-manager', [
            'summary' => $summary,
        ]);
    }
}

