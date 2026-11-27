<?php

use App\Livewire\Storefront\ProductDetail;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('homepage renders banners, featured categories, best sellers and new arrivals without N+1 queries', function () {
    $seller = Seller::factory()->create();
    $category = Category::factory()->create(['parent_id' => null, 'name' => 'Elektronik', 'slug' => 'elektronik']);
    $brand = Brand::factory()->create(['name' => 'NovaTech']);

    Banner::factory()->create(['title' => 'Hafta 3 Kampanyası', 'is_active' => true, 'sort' => 1]);

    for ($i = 1; $i <= 10; $i++) {
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'status' => 'active',
        ]);

        ProductVariant::factory()->count(2)->create(['product_id' => $product->id]);
    }

    DB::flushQueryLog();
    DB::enableQueryLog();

    $response = $this->get('/');

    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    $response
        ->assertOk()
        ->assertSee('Hafta 3 Kampanyası')
        ->assertSee('Öne Çıkan Kategoriler')
        ->assertSee('Çok Satanlar')
        ->assertSee('Yeni Gelenler');

    expect($queryCount)->toBeLessThan(20);
});

test('category page paginates 24 products, sorts properly and has no N+1 queries', function () {
    $seller = Seller::factory()->create();
    $category = Category::factory()->create(['parent_id' => null, 'name' => 'Giyim', 'slug' => 'giyim']);
    $brand = Brand::factory()->create();

    for ($i = 1; $i <= 26; $i++) {
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => sprintf('Ürün %02d', $i),
            'status' => 'active',
        ]);

        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => $i * 100,
            'discount_price' => null,
            'stock' => 10,
        ]);
    }

    DB::flushQueryLog();
    DB::enableQueryLog();

    $response = $this->get(route('categories.show', ['category' => 'giyim', 'sirala' => 'fiyat-artan']));

    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    $response
        ->assertOk()
        ->assertViewHas('products', fn ($paginator) => $paginator->count() === 24 && $paginator->total() === 26);

    $orderedNames = $response->viewData('products')->pluck('name')->all();
    expect($orderedNames[0])->toBe('Ürün 01');
    expect($orderedNames[23])->toBe('Ürün 24');
    expect($queryCount)->toBeLessThan(15);

    $descResponse = $this->get(route('categories.show', ['category' => 'giyim', 'sirala' => 'fiyat-azalan']))
        ->assertOk();

    $descNames = $descResponse->viewData('products')->pluck('name')->all();
    expect($descNames[0])->toBe('Ürün 26');
});

test('product detail Livewire component updates price and stock without page reload when variant changes', function () {
    $seller = Seller::factory()->create(['store_name' => 'TechZone Elektronik']);
    $category = Category::factory()->create(['name' => 'Kulaklıklar', 'slug' => 'kulakliklar']);
    $brand = Brand::factory()->create(['name' => 'AeroSound']);

    $product = Product::factory()->create([
        'seller_id' => $seller->id,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'Pro Bluetooth Kulaklık',
        'slug' => 'pro-bluetooth-kulaklik',
        'status' => 'active',
    ]);

    $variantA = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'sku' => 'SKU-SIYAH-M',
        'color' => 'Siyah',
        'size' => 'M',
        'price' => 499.90,
        'discount_price' => null,
        'stock' => 12,
    ]);

    $variantB = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'sku' => 'SKU-BEYAZ-L',
        'color' => 'Beyaz',
        'size' => 'L',
        'price' => 750.00,
        'discount_price' => 649.50,
        'stock' => 4,
    ]);

    $variantOut = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'sku' => 'SKU-KIRMIZI-XL',
        'color' => 'Kırmızı',
        'size' => 'XL',
        'price' => 800.00,
        'discount_price' => null,
        'stock' => 0,
    ]);

    $this->get(route('products.show', $product->slug))
        ->assertOk()
        ->assertSee('Pro Bluetooth Kulaklık')
        ->assertSee('TechZone Elektronik')
        ->assertSee('Sepete Ekle');

    Livewire::test(ProductDetail::class, ['product' => $product])
        ->assertSet('selectedVariantId', $variantA->id)
        ->assertSee('499,90 TL')
        ->assertSee('Stokta var (12 adet)')
        ->call('selectVariant', $variantB->id)
        ->assertSet('selectedVariantId', $variantB->id)
        ->assertSee('649,50 TL')
        ->assertSee('Stokta var (4 adet)')
        ->call('selectVariant', $variantOut->id)
        ->assertSet('selectedVariantId', $variantOut->id)
        ->assertSee('800,00 TL')
        ->assertSee('Tükendi');
});

test('product supports multiple images and generates thumbnail conversions via medialibrary', function () {
    Storage::fake('public');
    config()->set('media-library.disk_name', 'public');

    $product = Product::factory()->create();

    $image1 = UploadedFile::fake()->image('urun-1.jpg', 800, 800);
    $image2 = UploadedFile::fake()->image('urun-2.jpg', 800, 800);

    $product->addMedia($image1)->toMediaCollection('products');
    $product->addMedia($image2)->toMediaCollection('products');

    $product->refresh();
    $mediaItems = $product->getMedia('products');

    expect($mediaItems)->toHaveCount(2);
    expect($mediaItems->first()->hasGeneratedConversion('thumb'))->toBeTrue();
    expect($mediaItems->first()->hasGeneratedConversion('gallery'))->toBeTrue();
    expect(count($product->galleryImages()))->toBe(2);
});
