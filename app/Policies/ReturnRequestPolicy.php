<?php

namespace App\Policies;

use App\Enums\SubOrderStatus;
use App\Models\OrderItem;
use App\Models\ReturnRequest;
use App\Models\User;

class ReturnRequestPolicy
{
    public function create(User $user, OrderItem $orderItem): bool
    {
        $subOrder = $orderItem->subOrder;

        if ((int) $subOrder->order->user_id !== (int) $user->id)
        {
            return false;
        }

        if ($subOrder->status !== SubOrderStatus::Delivered)
        {
            return false;
        }

        if ($subOrder->updated_at->diffInDays(now()) > 14)
        {
            return false;
        }

        return $orderItem->returnRequest === null;
    }

    public function update(User $user, ReturnRequest $returnRequest): bool
    {
        if ($user->hasRole('admin'))
        {
            return true;
        }

        if (! $user->hasRole('seller') || $user->seller === null)
        {
            return false;
        }

        return (int) $returnRequest->orderItem->subOrder->seller_id === (int) $user->seller->id;
    }
}
