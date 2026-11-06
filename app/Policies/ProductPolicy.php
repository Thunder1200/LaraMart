<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'seller']);
    }

    public function view(?User $user, Product $product): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('admin'))
        {
            return true;
        }

        return $user->hasRole('seller') && $user->seller !== null && $user->seller->isApproved();
    }

    public function update(User $user, Product $product): bool
    {
        if ($user->hasRole('admin'))
        {
            return true;
        }

        if (! $user->hasRole('seller') || $user->seller === null)
        {
            return false;
        }

        return (int) $product->seller_id === (int) $user->seller->id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }
}

