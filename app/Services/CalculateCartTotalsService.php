<?php

namespace App\Services;

use App\Enums\CouponType;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class CalculateCartTotalsService
{
    public const FREE_SHIPPING_THRESHOLD = 300.00;

    public const STANDARD_SHIPPING_FEE = 49.90;

    public function resolveCart(?User $user = null, ?string $sessionId = null): Cart
    {
        $sessionId = $sessionId ?? Session::getId();

        if ($user !== null)
        {
            return Cart::firstOrCreate(
                ['user_id' => $user->id],
                ['session_id' => $sessionId]
            );
        }

        return Cart::firstOrCreate(
            ['session_id' => $sessionId, 'user_id' => null]
        );
    }

    public function addItem(Cart $cart, ProductVariant $variant, int $quantity = 1): CartItem
    {
        if ($quantity < 1)
        {
            throw new InvalidArgumentException('Miktar en az 1 olmalıdır.');
        }

        $existing = CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('variant_id', $variant->id)
            ->first();

        $targetQuantity = ($existing ? $existing->quantity : 0) + $quantity;

        if ($targetQuantity > $variant->stock)
        {
            throw new InvalidArgumentException("Stoktan fazla ürün sepete eklenemez. Mevcut stok: {$variant->stock}");
        }

        if ($existing)
        {
            $existing->update(['quantity' => $targetQuantity]);

            return $existing->fresh();
        }

        return CartItem::create([
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => $quantity,
        ]);
    }

    public function updateItemQuantity(CartItem $item, int $quantity): ?CartItem
    {
        if ($quantity <= 0)
        {
            $item->delete();

            return null;
        }

        $variant = $item->variant;

        if ($quantity > $variant->stock)
        {
            throw new InvalidArgumentException("Stoktan fazla ürün sepete eklenemez. Mevcut stok: {$variant->stock}");
        }

        $item->update(['quantity' => $quantity]);

        return $item->fresh();
    }

    public function applyCoupon(Cart $cart, string $code): Coupon
    {
        $normalizedCode = strtoupper(trim($code));
        $coupon = Coupon::where('code', $normalizedCode)->first();

        if (! $coupon)
        {
            throw new InvalidArgumentException('Geçersiz kupon kodu.');
        }

        if ($coupon->isExpired())
        {
            throw new InvalidArgumentException('Bu kuponun kullanım süresi dolmuştur.');
        }

        if ($coupon->isNotStarted())
        {
            throw new InvalidArgumentException('Bu kupon henüz aktif olmamıştır.');
        }

        if ($coupon->isUsageLimitReached())
        {
            throw new InvalidArgumentException('Bu kuponun kullanım sınırı dolmuştur.');
        }

        $summary = $this->calculate($cart, null);

        if ((float) $summary['subtotal'] < (float) $coupon->min_total)
        {
            throw new InvalidArgumentException("Bu kuponu kullanmak için minimum sepet tutarı {$coupon->min_total} TL olmalıdır.");
        }

        $cart->update(['coupon_code' => $coupon->code]);

        return $coupon;
    }

    public function calculate(Cart $cart, ?string $overrideCouponCode = null): array
    {
        $cart->loadMissing([
            'items.variant.product.seller',
            'items.variant.product.category',
        ]);

        $sellerGroups = [];
        $subtotal = 0.00;

        foreach ($cart->items as $item)
        {
            $variant = $item->variant;
            if (! $variant || ! $variant->product || ! $variant->product->seller)
            {
                continue;
            }

            $seller = $variant->product->seller;
            $unitPrice = (float) $variant->effectivePrice();
            $lineTotal = round($unitPrice * $item->quantity, 2);
            $subtotal = round($subtotal + $lineTotal, 2);

            if (! isset($sellerGroups[$seller->id]))
            {
                $sellerGroups[$seller->id] = [
                    'seller_id' => $seller->id,
                    'store_name' => $seller->store_name,
                    'slug' => $seller->slug,
                    'subtotal' => 0.00,
                    'shipping' => 0.00,
                    'items' => [],
                ];
            }

            $sellerGroups[$seller->id]['subtotal'] = round($sellerGroups[$seller->id]['subtotal'] + $lineTotal, 2);
            $sellerGroups[$seller->id]['items'][] = [
                'cart_item_id' => $item->id,
                'variant_id' => $variant->id,
                'product_id' => $variant->product->id,
                'product_name' => $variant->product->name,
                'product_slug' => $variant->product->slug,
                'color' => $variant->color,
                'size' => $variant->size,
                'unit_price' => number_format($unitPrice, 2, '.', ''),
                'quantity' => $item->quantity,
                'stock' => $variant->stock,
                'line_total' => number_format($lineTotal, 2, '.', ''),
                'category_commission_rate' => (float) ($variant->product->category->commission_rate ?? $seller->commission_rate),
            ];
        }

        $totalShipping = 0.00;

        foreach ($sellerGroups as $sellerId => &$group)
        {
            $shipping = $group['subtotal'] >= self::FREE_SHIPPING_THRESHOLD
                ? 0.00
                : self::STANDARD_SHIPPING_FEE;

            $group['shipping'] = round($shipping, 2);
            $totalShipping = round($totalShipping + $group['shipping'], 2);
        }
        unset($group);

        $couponCode = $overrideCouponCode !== null ? $overrideCouponCode : $cart->coupon_code;
        $discount = 0.00;
        $appliedCoupon = null;

        if ($couponCode && $subtotal > 0)
        {
            $coupon = Coupon::where('code', strtoupper(trim($couponCode)))->first();

            if (
                $coupon &&
                ! $coupon->isExpired() &&
                ! $coupon->isNotStarted() &&
                ! $coupon->isUsageLimitReached() &&
                $subtotal >= (float) $coupon->min_total
            ) {
                $appliedCoupon = $coupon;

                if ($coupon->type === CouponType::Percentage)
                {
                    $discount = round($subtotal * ((float) $coupon->value / 100), 2);
                }
                else
                {
                    $discount = min($subtotal, (float) $coupon->value);
                }
            }
        }

        $total = max(0.00, round($subtotal - $discount + $totalShipping, 2));

        return [
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'shipping' => number_format($totalShipping, 2, '.', ''),
            'discount' => number_format($discount, 2, '.', ''),
            'total' => number_format($total, 2, '.', ''),
            'coupon_code' => $appliedCoupon?->code,
            'seller_groups' => $sellerGroups,
            'item_count' => $cart->items->sum('quantity'),
        ];
    }
}
