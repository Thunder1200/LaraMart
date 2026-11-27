<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartService
{
    public const string GUEST_CART_SESSION_KEY = 'guest_cart_session_id';

    public const string APPLIED_COUPON_SESSION_KEY = 'applied_coupon_code';

    public function getOrCreateCart(?User $user = null): Cart
    {
        if ($user !== null) {
            return Cart::firstOrCreate(['user_id' => $user->id]);
        }

        $guestSessionId = $this->getOrCreateGuestSessionId();

        return Cart::firstOrCreate(['session_id' => $guestSessionId]);
    }

    public function findCurrentCart(?User $user = null): ?Cart
    {
        if ($user !== null) {
            return Cart::query()->where('user_id', $user->id)->first();
        }

        $guestSessionId = session()->get(self::GUEST_CART_SESSION_KEY);
        if (! is_string($guestSessionId) || $guestSessionId === '') {
            return null;
        }

        return Cart::query()->where('session_id', $guestSessionId)->first();
    }

    public function addItem(?User $user, ProductVariant $variant, int $quantity = 1): CartItem
    {
        if ($quantity < 1) {
            throw new DomainException('Ürün adedi en az 1 olmalıdır.');
        }

        if ($variant->stock < 1) {
            throw new DomainException('Bu ürün varyantı stokta bulunmamaktadır.');
        }

        $cart = $this->getOrCreateCart($user);

        /** @var CartItem|null $existing */
        $existing = $cart->items()->where('variant_id', $variant->id)->first();
        $currentQuantity = $existing !== null ? $existing->quantity : 0;
        $newQuantity = $currentQuantity + $quantity;

        if ($newQuantity > $variant->stock) {
            throw new DomainException(sprintf(
                'Stoktan fazla ürün eklenemez. Bu varyant için en fazla %d adet ekleyebilirsiniz.',
                $variant->stock
            ));
        }

        if ($existing !== null) {
            $existing->update(['quantity' => $newQuantity]);

            return $existing->refresh();
        }

        return $cart->items()->create([
            'variant_id' => $variant->id,
            'quantity' => $quantity,
        ]);
    }

    public function updateItemQuantity(?User $user, CartItem $cartItem, int $quantity): void
    {
        $cart = $this->findCurrentCart($user);

        if ($cart === null || $cartItem->cart_id !== $cart->id) {
            abort(403, 'Bu sepet kalemini düzenleme yetkiniz yok.');
        }

        if ($quantity <= 0) {
            $cartItem->delete();

            return;
        }

        $variant = $cartItem->variant;
        if ($quantity > $variant->stock) {
            throw new DomainException(sprintf(
                'Stoktan fazla ürün seçilemez. Mevcut stok: %d adet.',
                $variant->stock
            ));
        }

        $cartItem->update(['quantity' => $quantity]);
    }

    public function removeItem(?User $user, CartItem $cartItem): void
    {
        $cart = $this->findCurrentCart($user);

        if ($cart === null || $cartItem->cart_id !== $cart->id) {
            abort(403, 'Bu sepet kalemini silme yetkiniz yok.');
        }

        $cartItem->delete();
    }

    public function mergeGuestCartIntoUser(User $user, ?string $guestSessionId = null): void
    {
        $guestSessionId ??= session()->get(self::GUEST_CART_SESSION_KEY);

        if (! is_string($guestSessionId) || $guestSessionId === '') {
            return;
        }

        $guestCart = Cart::query()
            ->with('items.variant')
            ->where('session_id', $guestSessionId)
            ->first();

        if ($guestCart === null) {
            return;
        }

        DB::transaction(function () use ($user, $guestCart): void {
            $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

            foreach ($guestCart->items as $guestItem) {
                $variant = $guestItem->variant;
                if ($variant->stock < 1) {
                    continue;
                }

                /** @var CartItem|null $existing */
                $existing = $userCart->items()->where('variant_id', $variant->id)->first();
                $existingQuantity = $existing !== null ? $existing->quantity : 0;
                $mergedQuantity = min($variant->stock, $existingQuantity + $guestItem->quantity);

                if ($existing !== null) {
                    $existing->update(['quantity' => $mergedQuantity]);
                } else {
                    $userCart->items()->create([
                        'variant_id' => $variant->id,
                        'quantity' => $mergedQuantity,
                    ]);
                }
            }

            $guestCart->delete();
        });

        session()->forget(self::GUEST_CART_SESSION_KEY);
    }

    public function totalItemCount(?User $user = null): int
    {
        $cart = $this->findCurrentCart($user);

        if ($cart === null) {
            return 0;
        }

        return (int) $cart->items()->sum('quantity');
    }

    private function getOrCreateGuestSessionId(): string
    {
        $existing = session()->get(self::GUEST_CART_SESSION_KEY);

        if (is_string($existing) && $existing !== '') {
            return $existing;
        }

        $newId = (string) Str::uuid();
        session()->put(self::GUEST_CART_SESSION_KEY, $newId);

        return $newId;
    }
}
