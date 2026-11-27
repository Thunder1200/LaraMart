<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        $price = fake()->randomFloat(2, 99, 4999);
        $hasDiscount = fake()->boolean(35);

        return [
            'product_id' => Product::factory(),
            'sku' => 'LM-'.strtoupper(fake()->unique()->bothify('???-#####')),
            'color' => fake()->randomElement(['Siyah', 'Beyaz', 'Lacivert', 'Gri', 'Kırmızı', 'Bej', 'Yeşil']),
            'size' => fake()->randomElement(['S', 'M', 'L', 'XL', 'Standart']),
            'price' => $price,
            'discount_price' => $hasDiscount ? round($price * fake()->randomFloat(2, 0.70, 0.90), 2) : null,
            'stock' => fake()->numberBetween(5, 80),
        ];
    }
}
