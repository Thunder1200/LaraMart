<?php

namespace App\Actions\Products;

use App\Models\Product;

class ToggleProductStatusAction
{
    public function execute(Product $product): Product
    {
        $product->update([
            'status' => $product->status === 'active' ? 'inactive' : 'active',
        ]);

        return $product->refresh();
    }
}
