<?php

namespace App\Policies;

use App\Models\OrderItem;
use App\Models\ReturnRequest;
use App\Models\User;

class ReturnRequestPolicy
{
    public function create(User $user, OrderItem $orderItem): bool
    {
        $orderItem->loadMissing('subOrder.order');

        return $orderItem->subOrder->order->user_id === $user->id;
    }

    public function decide(User $user, ReturnRequest $returnRequest): bool
    {
        $returnRequest->loadMissing('orderItem.subOrder');

        return $user->hasRole('admin')
            || ($user->seller !== null && $user->seller->id === $returnRequest->orderItem->subOrder->seller_id);
    }
}
