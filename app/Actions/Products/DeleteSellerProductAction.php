<?php

namespace App\Actions\Products;

use App\Models\Product;

class DeleteSellerProductAction
{
    public function execute(Product $product): void
    {
        $product->delete();
    }
}
