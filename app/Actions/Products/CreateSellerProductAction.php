<?php

namespace App\Actions\Products;

use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateSellerProductAction
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<UploadedFile>  $images
     */
    public function execute(Seller $seller, array $data, array $images = []): Product
    {
        return DB::transaction(function () use ($seller, $data, $images): Product {
            $baseSlug = Str::slug((string) $data['name']);
            $slug = $baseSlug;
            $counter = 1;

            while (Product::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$counter++;
            }

            $product = Product::create([
                'seller_id' => $seller->id,
                'category_id' => $data['category_id'],
                'brand_id' => $data['brand_id'] ?? null,
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'active',
            ]);

            $product->variants()->create([
                'sku' => $data['sku'],
                'color' => $data['color'] ?? null,
                'size' => $data['size'] ?? null,
                'price' => $data['price'],
                'discount_price' => $data['discount_price'] ?? null,
                'stock' => $data['stock'],
            ]);

            foreach ($images as $image) {
                $product->addMedia($image)->toMediaCollection('products');
            }

            return $product;
        });
    }
}
