<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        if ($user->hasRole('admin'))
        {
            return true;
        }

        return (int) $order->user_id === (int) $user->id;
    }
}

