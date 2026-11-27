<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $adjectives = ['Premium', 'Klasik', 'Akıllı', 'Ergonomik', 'Organik', 'Modern', 'Kompakt', 'Pro', 'Ultra', 'Konfor'];
        $nouns = ['Kulaklık', 'Tişört', 'Ayakkabı', 'Sırt Çantası', 'Kahve Makinesi', 'Akıllı Saat', 'Ceket', 'Masa Lambası', 'Spor Çanta', 'Termos', 'Klavye', 'Güneş Gözlüğü'];

        $name = fake()->randomElement($adjectives).' '.fake()->randomElement($nouns).' '.strtoupper(Str::random(3));

        return [
            'seller_id' => Seller::factory(),
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 99999),
            'description' => fake()->paragraphs(2, true),
            'status' => 'active',
        ];
    }
}
