<?php

namespace App\Actions;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MergeGuestCartAction
{
    public function execute(User $user, string $guestSessionId): Cart
    {
        return DB::transaction(function () use ($user, $guestSessionId)
        {
            $userCart = Cart::firstOrCreate(
                ['user_id' => $user->id],
                ['session_id' => $guestSessionId]
            );

            $guestCart = Cart::query()
                ->where('session_id', $guestSessionId)
                ->whereNull('user_id')
                ->where('id', '!=', $userCart->id)
                ->with('items.variant')
                ->first();

            if (! $guestCart)
            {
                return $userCart->load('items.variant');
            }

            foreach ($guestCart->items as $guestItem)
            {
                $variant = $guestItem->variant;

                if (! $variant || $variant->stock <= 0)
                {
                    continue;
                }

                $existingItem = CartItem::query()
                    ->where('cart_id', $userCart->id)
                    ->where('variant_id', $variant->id)
                    ->first();

                if ($existingItem)
                {
                    $mergedQty = min($variant->stock, $existingItem->quantity + $guestItem->quantity);
                    $existingItem->update(['quantity' => $mergedQty]);
                }
                else
                {
                    $mergedQty = min($variant->stock, $guestItem->quantity);
                    CartItem::create([
                        'cart_id' => $userCart->id,
                        'variant_id' => $variant->id,
                        'quantity' => $mergedQty,
                    ]);
                }
            }

            if (! $userCart->coupon_code && $guestCart->coupon_code)
            {
                $userCart->update(['coupon_code' => $guestCart->coupon_code]);
            }

            $guestCart->items()->delete();
            $guestCart->delete();

            return $userCart->fresh(['items.variant']);
        });
    }
}
