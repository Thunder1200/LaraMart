<?php

namespace App\Actions\Favorites;

use App\Models\Favorite;
use App\Models\Product;
use App\Models\User;

class ToggleFavoriteAction
{
    public function execute(User $user, Product $product): bool
    {
        $existing = Favorite::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing !== null) {
            $existing->delete();

            return false;
        }

        Favorite::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return true;
    }
}
