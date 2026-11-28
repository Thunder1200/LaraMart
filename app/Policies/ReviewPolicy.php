<?php

namespace App\Policies;

use App\Enums\SubOrderStatus;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user, OrderItem $orderItem): bool
    {
        $orderItem->loadMissing('subOrder.order');

        return $orderItem->subOrder->order->user_id === $user->id
            && $orderItem->subOrder->status === SubOrderStatus::Delivered;
    }

    public function moderate(User $user, ?Review $review = null): bool
    {
        return $user->hasRole('admin');
    }
}
