<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        $price = fake()->randomFloat(2, 150, 4500);
        $hasDiscount = fake()->boolean(40);
        $discountPrice = $hasDiscount ? round($price * 0.85, 2) : null;

        return [
            'product_id' => Product::factory(),
            'sku' => 'SKU-' . strtoupper(fake()->unique()->bothify('????-#####')),
            'color' => fake()->randomElement(['Siyah', 'Beyaz', 'Lacivert', 'Gri', 'Kırmızı', 'Yeşil']),
            'size' => fake()->randomElement(['S', 'M', 'L', 'XL', 'Standart']),
            'price' => number_format($price, 2, '.', ''),
            'discount_price' => $discountPrice !== null ? number_format($discountPrice, 2, '.', '') : null,
            'stock' => fake()->numberBetween(5, 100),
        ];
    }
}

