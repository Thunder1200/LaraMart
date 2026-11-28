<?php

namespace App\Policies;

use App\Models\SubOrder;
use App\Models\User;

class SubOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin')
            || ($user->hasRole('seller') && $user->seller?->status === 'approved');
    }

    public function view(User $user, SubOrder $subOrder): bool
    {
        return $user->hasRole('admin')
            || ($user->seller !== null && $user->seller->id === $subOrder->seller_id);
    }

    public function update(User $user, SubOrder $subOrder): bool
    {
        return $user->hasRole('admin')
            || ($user->seller !== null && $user->seller->id === $subOrder->seller_id);
    }
}
