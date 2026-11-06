<?php

namespace App\Policies;

use App\Models\SubOrder;
use App\Models\User;

class SubOrderPolicy
{
    public function view(User $user, SubOrder $subOrder): bool
    {
        if ($user->hasRole('admin'))
        {
            return true;
        }

        if ($user->hasRole('seller') && $user->seller !== null)
        {
            return (int) $subOrder->seller_id === (int) $user->seller->id;
        }

        return (int) $subOrder->order->user_id === (int) $user->id;
    }

    public function update(User $user, SubOrder $subOrder): bool
    {
        if ($user->hasRole('admin'))
        {
            return true;
        }

        if (! $user->hasRole('seller') || $user->seller === null)
        {
            return false;
        }

        return (int) $subOrder->seller_id === (int) $user->seller->id;
    }
}

