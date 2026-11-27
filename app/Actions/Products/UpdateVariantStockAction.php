<?php

namespace App\Actions\Products;

use App\Models\Product;
use App\Models\ProductVariant;

class UpdateVariantStockAction
{
    public function execute(Product $product, int $variantId, int $stock): ProductVariant
    {
        /** @var ProductVariant $variant */
        $variant = $product->variants()->whereKey($variantId)->firstOrFail();

        $variant->update([
            'stock' => $stock,
        ]);

        return $variant->refresh();
    }
}
