<?php

namespace App\Policies;

use App\Enums\SubOrderStatus;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

class ReviewPolicy
{
    public function createForProduct(User $user, Product $product): bool
    {
        return OrderItem::query()
            ->whereHas('variant', function ($query) use ($product)
            {
                $query->where('product_id', $product->id);
            })
            ->whereHas('subOrder', function ($query) use ($user)
            {
                $query->where('status', SubOrderStatus::Delivered->value)
                    ->whereHas('order', function ($orderQuery) use ($user)
                    {
                        $orderQuery->where('user_id', $user->id);
                    });
            })
            ->exists();
    }
}
