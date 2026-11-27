<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isApprovedSeller($user) || $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $this->isApprovedSeller($user);
    }

    public function update(User $user, Product $product): bool
    {
        return $this->ownsProduct($user, $product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->ownsProduct($user, $product);
    }

    private function isApprovedSeller(User $user): bool
    {
        return $user->hasRole('seller') && $user->seller?->status === 'approved';
    }

    private function ownsProduct(User $user, Product $product): bool
    {
        return $this->isApprovedSeller($user) && $user->seller->id === $product->seller_id;
    }
}
