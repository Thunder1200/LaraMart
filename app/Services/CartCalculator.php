<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Coupon;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Support\Collection;

class CartCalculator
{
    public const float FREE_SHIPPING_THRESHOLD = 300.00;

    public const float SHIPPING_FEE_PER_SELLER = 49.90;

    /**
     * @param  Collection<int, CartItem>  $items
     * @return array{
     *     groups: list<array{
     *         seller_id: int,
     *         seller_name: string,
     *         seller_slug: string,
     *         items: list<CartItem>,
     *         subtotal: float,
     *         shipping: float,
     *         free_shipping_remaining: float
     *     }>,
     *     subtotal: float,
     *     shipping: float,
     *     discount: float,
     *     total: float,
     *     item_count: int
     * }
     */
    public function calculate(Collection $items, ?Coupon $coupon = null, ?CarbonInterface $now = null): array
    {
        $now ??= now();

        $groups = [];
        $subtotal = 0.00;
        $shippingTotal = 0.00;
        $itemCount = 0;

        $groupedBySeller = $items->groupBy(fn (CartItem $item): int => (int) $item->variant->product->seller_id);

        foreach ($groupedBySeller as $sellerId => $sellerItems) {
            /** @var CartItem $firstItem */
            $firstItem = $sellerItems->first();
            $seller = $firstItem->variant->product->seller;

            $sellerSubtotal = 0.00;
            foreach ($sellerItems as $cartItem) {
                $unitPrice = $cartItem->variant->effectivePrice();
                $sellerSubtotal += $unitPrice * $cartItem->quantity;
                $itemCount += $cartItem->quantity;
            }

            $sellerSubtotal = round($sellerSubtotal, 2);
            $sellerShipping = $sellerSubtotal >= self::FREE_SHIPPING_THRESHOLD
                ? 0.00
                : self::SHIPPING_FEE_PER_SELLER;

            $freeShippingRemaining = max(0.00, round(self::FREE_SHIPPING_THRESHOLD - $sellerSubtotal, 2));

            $subtotal += $sellerSubtotal;
            $shippingTotal += $sellerShipping;

            $groups[] = [
                'seller_id' => (int) $sellerId,
                'seller_name' => $seller->store_name,
                'seller_slug' => $seller->slug,
                'items' => $sellerItems->values()->all(),
                'subtotal' => $sellerSubtotal,
                'shipping' => round($sellerShipping, 2),
                'free_shipping_remaining' => $freeShippingRemaining,
            ];
        }

        $subtotal = round($subtotal, 2);
        $shippingTotal = round($shippingTotal, 2);

        $discount = 0.00;
        if ($coupon !== null && $subtotal > 0) {
            $discount = $this->calculateDiscount($coupon, $subtotal, $now);
        }

        $total = max(0.00, round($subtotal - $discount + $shippingTotal, 2));

        return [
            'groups' => $groups,
            'subtotal' => $subtotal,
            'shipping' => $shippingTotal,
            'discount' => $discount,
            'total' => $total,
            'item_count' => $itemCount,
        ];
    }

    public function validateCoupon(Coupon $coupon, float $subtotal, ?CarbonInterface $now = null): void
    {
        $now ??= now();

        if ($coupon->starts_at !== null && $now->lt($coupon->starts_at)) {
            throw new DomainException('Bu kupon henüz aktif değildir.');
        }

        if ($coupon->ends_at !== null && $now->gt($coupon->ends_at)) {
            throw new DomainException('Bu kuponun kullanım süresi dolmuştur.');
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw new DomainException('Bu kuponun kullanım limiti dolmuştur.');
        }

        if ($subtotal < (float) $coupon->min_total) {
            throw new DomainException(sprintf(
                'Bu kuponu kullanmak için sepet tutarı en az %s TL olmalıdır.',
                number_format((float) $coupon->min_total, 2, ',', '.')
            ));
        }
    }

    public function calculateDiscount(Coupon $coupon, float $subtotal, ?CarbonInterface $now = null): float
    {
        $this->validateCoupon($coupon, $subtotal, $now);

        $rawDiscount = $coupon->type === 'percentage'
            ? $subtotal * ((float) $coupon->value / 100)
            : (float) $coupon->value;

        return round(min($subtotal, max(0.00, $rawDiscount)), 2);
    }
}
