<?php

namespace App\Actions\Products;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpdateSellerProductAction
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<UploadedFile>  $images
     */
    public function execute(Product $product, array $data, array $images = []): Product
    {
        return DB::transaction(function () use ($product, $data, $images): Product {
            $product->update([
                'category_id' => $data['category_id'],
                'brand_id' => $data['brand_id'] ?? null,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
            ]);

            $variant = $product->variants()->first();

            if ($variant !== null) {
                $variant->update([
                    'sku' => $data['sku'],
                    'color' => $data['color'] ?? null,
                    'size' => $data['size'] ?? null,
                    'price' => $data['price'],
                    'discount_price' => $data['discount_price'] ?? null,
                    'stock' => $data['stock'],
                ]);
            } else {
                $product->variants()->create([
                    'sku' => $data['sku'],
                    'color' => $data['color'] ?? null,
                    'size' => $data['size'] ?? null,
                    'price' => $data['price'],
                    'discount_price' => $data['discount_price'] ?? null,
                    'stock' => $data['stock'],
                ]);
            }

            foreach ($images as $image) {
                $product->addMedia($image)->toMediaCollection('products');
            }

            return $product->refresh();
        });
    }
}
