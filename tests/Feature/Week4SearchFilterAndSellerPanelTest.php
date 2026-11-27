<?php

use App\Livewire\Storefront\SearchBar;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Seller;
use App\Models\SubOrder;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'seller']);
    Role::firstOrCreate(['name' => 'customer']);
});

test('search bar Livewire autocomplete suggests matching products and brands while typing', function () {
    $brand = Brand::factory()->create(['name' => 'AeroSound', 'slug' => 'aerosound']);
    $product = Product::factory()->create([
        'brand_id' => $brand->id,
        'name' => 'Kablosuz Kulaklık X1',
        'status' => 'active',
    ]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 1299.00]);

    Livewire::test(SearchBar::class)
        ->set('query', 'Aero')
        ->assertSet('showSuggestions', true)
        ->assertSee('AeroSound')
        ->assertSee('Kablosuz Kulaklık X1');
});

test('shared URL with filters returns identical filtered results', function () {
    $category = Category::factory()->create(['name' => 'Ayakkabı', 'slug' => 'ayakkabi']);
    $brandX = Brand::factory()->create(['name' => 'PeakFit', 'slug' => 'peakfit']);
    $brandY = Brand::factory()->create(['name' => 'OtherBrand', 'slug' => 'otherbrand']);

    $matchingProduct = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brandX->id,
        'name' => 'Koşu Ayakkabısı Pro',
        'status' => 'active',
    ]);
    $matchingVariant = ProductVariant::factory()->create([
        'product_id' => $matchingProduct->id,
        'color' => 'Siyah',
        'size' => '42',
        'price' => 550.00,
        'discount_price' => null,
    ]);

    $otherProduct = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brandY->id,
        'name' => 'Ucuz Terlik',
        'status' => 'active',
    ]);
    ProductVariant::factory()->create([
        'product_id' => $otherProduct->id,
        'color' => 'Beyaz',
        'size' => '40',
        'price' => 80.00,
        'discount_price' => null,
    ]);

    // Onaylı 5 yıldız yorum ekle (puan filtresi testi)
    $buyer = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $buyer->id]);
    $subOrder = SubOrder::factory()->create(['order_id' => $order->id, 'seller_id' => $matchingProduct->seller_id]);
    $orderItem = OrderItem::factory()->create(['sub_order_id' => $subOrder->id, 'variant_id' => $matchingVariant->id]);
    Review::factory()->create([
        'user_id' => $buyer->id,
        'product_id' => $matchingProduct->id,
        'order_item_id' => $orderItem->id,
        'rating' => 5,
        'is_approved' => true,
    ]);

    $sharedUrl = '/arama?marka=peakfit&min=200&max=800&renk=Siyah&beden=42&puan=4';

    $firstVisit = $this->get($sharedUrl)->assertOk();
    $secondVisit = $this->get($sharedUrl)->assertOk();

    $firstVisit->assertSee('Koşu Ayakkabısı Pro')->assertDontSee('Ucuz Terlik');
    $secondVisit->assertSee('Koşu Ayakkabısı Pro')->assertDontSee('Ucuz Terlik');
    expect($firstVisit->viewData('products')->pluck('id')->all())
        ->toBe($secondVisit->viewData('products')->pluck('id')->all());
});

test('seller application stays pending until admin approval and cannot access seller panel', function () {
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    $this->actingAs($customer)
        ->post(route('seller.apply.store'), [
            'store_name' => 'Yeni Mağaza',
            'tax_number' => '9876543210',
            'iban' => 'TR120006200000000012345678',
        ])
        ->assertRedirect(route('seller.apply.create'));

    $seller = Seller::where('user_id', $customer->id)->firstOrFail();
    expect($seller->status)->toBe('pending');

    $this->actingAs($customer)
        ->get('/satici')
        ->assertForbidden();
});

test('seller A receives 403 when attempting to edit, update, toggle or delete seller B product', function () {
    $sellerUserA = User::factory()->create();
    $sellerUserA->assignRole('seller');
    Seller::factory()->create(['user_id' => $sellerUserA->id, 'status' => 'approved']);

    $sellerUserB = User::factory()->create();
    $sellerUserB->assignRole('seller');
    $sellerB = Seller::factory()->create(['user_id' => $sellerUserB->id, 'status' => 'approved']);

    $productB = Product::factory()->create(['seller_id' => $sellerB->id]);
    $variantB = ProductVariant::factory()->create(['product_id' => $productB->id, 'stock' => 15]);

    $this->actingAs($sellerUserA)
        ->get(route('seller.products.edit', $productB))
        ->assertForbidden();

    $this->actingAs($sellerUserA)
        ->put(route('seller.products.update', $productB), [
            'category_id' => $productB->category_id,
            'name' => 'Yetkisiz Değişiklik',
            'status' => 'active',
            'sku' => $variantB->sku,
            'price' => 100,
            'stock' => 1,
        ])
        ->assertForbidden();

    $this->actingAs($sellerUserA)
        ->patch(route('seller.products.toggle-status', $productB))
        ->assertForbidden();

    $this->actingAs($sellerUserA)
        ->patch(route('seller.products.update-stock', $productB), [
            'variant_id' => $variantB->id,
            'stock' => 999,
        ])
        ->assertForbidden();

    $this->actingAs($sellerUserA)
        ->delete(route('seller.products.destroy', $productB))
        ->assertForbidden();
});

test('approved seller can create, update, toggle status, update stock and delete own product', function () {
    $sellerUser = User::factory()->create();
    $sellerUser->assignRole('seller');
    $seller = Seller::factory()->create(['user_id' => $sellerUser->id, 'status' => 'approved']);

    $category = Category::factory()->create();
    $brand = Brand::factory()->create();

    $this->actingAs($sellerUser)
        ->post(route('seller.products.store'), [
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Satıcı Test Ürünü',
            'description' => 'Harika bir ürün.',
            'status' => 'active',
            'sku' => 'SKU-TEST-401',
            'color' => 'Lacivert',
            'size' => 'L',
            'price' => 450.00,
            'discount_price' => 399.00,
            'stock' => 25,
        ])
        ->assertRedirect(route('seller.products.index'));

    $product = Product::where('seller_id', $seller->id)->firstOrFail();
    $variant = $product->variants()->firstOrFail();

    $this->actingAs($sellerUser)
        ->patch(route('seller.products.update-stock', $product), [
            'variant_id' => $variant->id,
            'stock' => 42,
        ])
        ->assertRedirect(route('seller.products.index'));

    expect($variant->refresh()->stock)->toBe(42);

    $this->actingAs($sellerUser)
        ->patch(route('seller.products.toggle-status', $product))
        ->assertRedirect(route('seller.products.index'));

    expect($product->refresh()->status)->toBe('inactive');

    $this->actingAs($sellerUser)
        ->delete(route('seller.products.destroy', $product))
        ->assertRedirect(route('seller.products.index'));

    expect(Product::find($product->id))->toBeNull();
});

test('public store page displays approved seller products and hides pending sellers', function () {
    $approvedSeller = Seller::factory()->create([
        'store_name' => 'TechZone Elektronik',
        'slug' => 'techzone-elektronik',
        'status' => 'approved',
    ]);
    $product = Product::factory()->create([
        'seller_id' => $approvedSeller->id,
        'name' => 'Mağaza Özel Kulaklık',
        'status' => 'active',
    ]);
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $pendingSeller = Seller::factory()->create([
        'slug' => 'onaysiz-magaza',
        'status' => 'pending',
    ]);

    $this->get(route('stores.show', $approvedSeller->slug))
        ->assertOk()
        ->assertSee('TechZone Elektronik')
        ->assertSee('Mağaza Özel Kulaklık');

    $this->get(route('stores.show', $pendingSeller->slug))
        ->assertNotFound();
});
