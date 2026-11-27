<?php

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\DatabaseSeeder;

test('electronics category hides size filter and shows electronics-specific technical filters', function () {
    $this->seed(DatabaseSeeder::class);

    $this->get('/kategori/telefon-aksesuar')
        ->assertOk()
        ->assertDontSee('Beden / Numara')
        ->assertSee('Bağlantı Teknolojisi')
        ->assertSee('Pil Ömrü')
        ->assertSee('Suya Dayanıklılık');
});

test('clothing category displays size filter and clothing-specific filters', function () {
    $this->seed(DatabaseSeeder::class);

    $this->get('/kategori/kadin-giyim')
        ->assertOk()
        ->assertSee('Beden / Numara')
        ->assertSee('Kumaş Türü')
        ->assertSee('Kalıp');
});

test('category-specific technical attribute filter and minimum rating filter work together via URL', function () {
    $this->seed(DatabaseSeeder::class);

    $response = $this->get('/kategori/telefon-aksesuar?ozellik[baglanti]=Bluetooth+5.4&puan=4')
        ->assertOk();

    $products = $response->viewData('products');

    expect($products->total())->toBeGreaterThan(0);

    foreach ($products as $product) {
        expect($product->getAttribute('attributes')['baglanti'] ?? null)->toBe('Bluetooth 5.4');
        expect((float) $product->approved_reviews_avg_rating)->toBeGreaterThanOrEqual(4.0);
    }
});

test('all 100 seeded products have studio photos, rich descriptions, attributes and varied review ratings', function () {
    $this->seed(DatabaseSeeder::class);

    $products = Product::with(['variants', 'approvedReviews'])->get();

    expect($products)->toHaveCount(100);
    expect(Category::count())->toBe(15);

    $distinctAverages = [];

    foreach ($products as $product) {
        expect(mb_strlen((string) $product->description))->toBeGreaterThan(100);
        expect($product->formattedAttributes())->not->toBeEmpty();
        expect($product->thumbnailUrl())->toContain('/images/products/variants/');
        expect(count($product->galleryImages()))->toBeGreaterThanOrEqual(2);
        expect($product->approvedReviews->count())->toBeGreaterThanOrEqual(3);

        foreach ($product->variants as $variant) {
            expect($variant->hasUniqueImageFile())->toBeTrue("Missing variant image file: {$variant->image_path}");
            expect(filesize(public_path((string) $variant->image_path)))->toBeGreaterThan(50000);
        }

        $avg = round((float) $product->approvedReviews->avg('rating'), 1);
        $distinctAverages[(string) $avg] = true;
    }

    expect(count($distinctAverages))->toBeGreaterThanOrEqual(5);
});

test('filter sidebar clear buttons point to clean unfiltered URLs and restore full default product list', function () {
    $this->seed(DatabaseSeeder::class);

    $filteredCategoryResponse = $this->get('/kategori/telefon-aksesuar?ozellik[baglanti]=Bluetooth+5.4&puan=4&min=500')
        ->assertOk()
        ->assertSee('data-test="clear-filters-top"', false)
        ->assertSee('data-test="clear-filters-bottom"', false)
        ->assertSee('href="/kategori/telefon-aksesuar"', false);

    $unfilteredCategoryResponse = $this->get('/kategori/telefon-aksesuar')
        ->assertOk();

    expect($unfilteredCategoryResponse->viewData('products')->total())
        ->toBeGreaterThanOrEqual($filteredCategoryResponse->viewData('products')->total());

    $filteredSearchResponse = $this->get('/arama?q=Kulakl%C4%B1k&puan=4&min=100')
        ->assertOk()
        ->assertSee('data-test="clear-filters-top"', false)
        ->assertSee('href="/arama"', false);

    $unfilteredSearchResponse = $this->get('/arama')
        ->assertOk();

    expect($unfilteredSearchResponse->viewData('products')->total())->toBe(100);
    expect($unfilteredSearchResponse->viewData('products')->total())
        ->toBeGreaterThan($filteredSearchResponse->viewData('products')->total());
});
