<?php

use App\Livewire\Storefront\ProductDetail;
use App\Models\Product;
use App\Models\ProductVariant;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;

test('changing color variant in ProductDetail immediately switches the main product image to that color photo', function () {
    $variantDir = public_path('images/products/variants');
    File::ensureDirectoryExists($variantDir);

    $blackRel = 'images/products/variants/test-color-siyah.jpg';
    $whiteRel = 'images/products/variants/test-color-beyaz.jpg';

    $blackAbs = public_path($blackRel);
    $whiteAbs = public_path($whiteRel);

    File::put($blackAbs, 'fake-black-image');
    File::put($whiteAbs, 'fake-white-image');

    try {
        $product = Product::factory()->create(['status' => 'active']);

        $blackVariant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'color' => 'Siyah',
            'size' => null,
            'image_path' => $blackRel,
            'price' => 1500.00,
            'stock' => 10,
        ]);

        $whiteVariant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'color' => 'Beyaz',
            'size' => null,
            'image_path' => $whiteRel,
            'price' => 1600.00,
            'stock' => 8,
        ]);

        $component = Livewire::test(ProductDetail::class, ['product' => $product])
            ->assertSet('selectedVariantId', $blackVariant->id);

        expect($component->instance()->gallery()[0]['url'])->toContain($blackRel);

        $component
            ->call('selectVariant', $whiteVariant->id)
            ->assertSet('selectedVariantId', $whiteVariant->id)
            ->assertSet('activeImageIndex', 0);

        expect($component->instance()->gallery()[0]['url'])->toContain($whiteRel);

        // Listeleme kartında her iki renk swatch'ı kendi görseliyle üretilir
        $swatches = $product->fresh('variants')->colorSwatches();
        expect($swatches)->toHaveCount(2);
        expect($swatches[0]['image'])->toContain($blackRel);
        expect($swatches[1]['image'])->toContain($whiteRel);
    } finally {
        File::delete([$blackAbs, $whiteAbs]);
    }
});

test('seeder assigns deterministic unique image_path per product and color variant', function () {
    $this->seed(DatabaseSeeder::class);

    $variants = ProductVariant::with('product')->get();
    expect($variants)->not->toBeEmpty();

    $pathsByProduct = [];
    foreach ($variants as $variant) {
        expect($variant->image_path)->toStartWith('images/products/variants/p');
        expect($variant->image_path)->toEndWith('.webp');
        $pathsByProduct[$variant->product_id][$variant->image_path] = true;
    }

    // 100 farklı ürün arasında hiç çakışan image_path olmamalı
    $allPaths = [];
    foreach ($pathsByProduct as $productId => $paths) {
        foreach (array_keys($paths) as $path) {
            expect(isset($allPaths[$path]))->toBeFalse("Duplicate image_path across products: {$path}");
            $allPaths[$path] = $productId;
        }
    }

    expect(count($allPaths))->toBeGreaterThanOrEqual(200);
});
