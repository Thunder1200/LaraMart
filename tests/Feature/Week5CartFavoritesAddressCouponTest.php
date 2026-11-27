<?php

use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\User;
use Livewire\Volt\Volt;

test('user cannot add more items to cart than available stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['status' => 'active']);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 200.00,
        'stock' => 3,
    ]);

    $this->actingAs($user)
        ->post(route('cart.store'), [
            'variant_id' => $variant->id,
            'quantity' => 5,
        ])
        ->assertSessionHas('error');

    expect(Cart::where('user_id', $user->id)->first()?->items()->count() ?? 0)->toBe(0);

    $this->actingAs($user)
        ->post(route('cart.store'), [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ])
        ->assertSessionHas('status');

    $cartItem = Cart::where('user_id', $user->id)->firstOrFail()->items()->firstOrFail();

    $this->actingAs($user)
        ->patch(route('cart.update', $cartItem), ['quantity' => 4])
        ->assertSessionHas('error');

    expect($cartItem->refresh()->quantity)->toBe(2);
});

test('guest session cart merges into user cart upon login', function () {
    $user = User::factory()->create();
    $productA = Product::factory()->create(['status' => 'active']);
    $variantA = ProductVariant::factory()->create(['product_id' => $productA->id, 'stock' => 10, 'price' => 120.00]);

    $productB = Product::factory()->create(['status' => 'active']);
    $variantB = ProductVariant::factory()->create(['product_id' => $productB->id, 'stock' => 10, 'price' => 250.00]);

    // Kullanıcının mevcut sepetinde 1 adet variantA olsun
    $userCart = Cart::create(['user_id' => $user->id]);
    $userCart->items()->create(['variant_id' => $variantA->id, 'quantity' => 1]);

    // Misafir olarak 2 adet variantA ve 1 adet variantB ekle
    $this->post(route('cart.store'), ['variant_id' => $variantA->id, 'quantity' => 2]);
    $this->post(route('cart.store'), ['variant_id' => $variantB->id, 'quantity' => 1]);

    // Giriş yap (Login event'i tetiklenir ve sepetler birleşir)
    Volt::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertHasNoErrors();

    $mergedCart = Cart::where('user_id', $user->id)->with('items')->firstOrFail();

    expect($mergedCart->items->firstWhere('variant_id', $variantA->id)->quantity)->toBe(3);
    expect($mergedCart->items->firstWhere('variant_id', $variantB->id)->quantity)->toBe(1);
    expect(Cart::whereNotNull('session_id')->count())->toBe(0);
});

test('cart page groups products by seller and shows separate shipping fees', function () {
    $user = User::factory()->create();
    $sellerA = Seller::factory()->create(['store_name' => 'Mağaza Alfa', 'status' => 'approved']);
    $sellerB = Seller::factory()->create(['store_name' => 'Mağaza Beta', 'status' => 'approved']);

    $productA = Product::factory()->create(['seller_id' => $sellerA->id, 'name' => 'Alfa Ürün', 'status' => 'active']);
    $variantA = ProductVariant::factory()->create(['product_id' => $productA->id, 'price' => 150.00, 'discount_price' => null, 'stock' => 10]);

    $productB = Product::factory()->create(['seller_id' => $sellerB->id, 'name' => 'Beta Ürün', 'status' => 'active']);
    $variantB = ProductVariant::factory()->create(['product_id' => $productB->id, 'price' => 400.00, 'discount_price' => null, 'stock' => 10]);

    $this->actingAs($user)->post(route('cart.store'), ['variant_id' => $variantA->id, 'quantity' => 1]);
    $this->actingAs($user)->post(route('cart.store'), ['variant_id' => $variantB->id, 'quantity' => 1]);

    $this->actingAs($user)
        ->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Mağaza Alfa')
        ->assertSee('Mağaza Beta')
        ->assertSee('Kargo Bedava')
        ->assertSee('49,90 TL')
        ->assertSee('599,90 TL');
});

test('expired coupon returns error message while valid coupon applies discount to cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['status' => 'active']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 500.00, 'discount_price' => null, 'stock' => 10]);

    Coupon::factory()->create([
        'code' => 'ESKIKUPON',
        'type' => 'percentage',
        'value' => 15.00,
        'min_total' => 100.00,
        'starts_at' => now()->subDays(10),
        'ends_at' => now()->subDay(),
    ]);

    Coupon::factory()->create([
        'code' => 'GECERLI10',
        'type' => 'percentage',
        'value' => 10.00,
        'min_total' => 200.00,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(10),
    ]);

    $this->actingAs($user)->post(route('cart.store'), ['variant_id' => $variant->id, 'quantity' => 1]);

    $this->actingAs($user)
        ->post(route('cart.coupon.apply'), ['code' => 'ESKIKUPON'])
        ->assertRedirect(route('cart.index'))
        ->assertSessionHas('error', 'Bu kuponun kullanım süresi dolmuştur.');

    $this->actingAs($user)
        ->post(route('cart.coupon.apply'), ['code' => 'GECERLI10'])
        ->assertRedirect(route('cart.index'))
        ->assertSessionHas('status');

    $this->actingAs($user)
        ->get(route('cart.index'))
        ->assertOk()
        ->assertSee('-50,00 TL')
        ->assertSee('450,00 TL');
});

test('customer can toggle favorites and view them in account favorites page', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['name' => 'Favori Kulaklık', 'status' => 'active']);
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->actingAs($user)
        ->post(route('customer.favorites.toggle', $product))
        ->assertSessionHas('status');

    $this->actingAs($user)
        ->get(route('customer.favorites.index'))
        ->assertOk()
        ->assertSee('Favori Kulaklık');

    $this->actingAs($user)
        ->post(route('customer.favorites.toggle', $product))
        ->assertSessionHas('status');

    expect($user->favorites()->count())->toBe(0);
});

test('customer can manage own addresses with city/district and default selection while others get 403', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $this->actingAs($userA)
        ->post(route('customer.addresses.store'), [
            'title' => 'Ev Adresim',
            'city' => 'İstanbul',
            'district' => 'Kadıköy',
            'full_address' => 'Caferağa Mahallesi Moda Caddesi No:15 Daire:4',
            'is_default' => true,
        ])
        ->assertRedirect(route('customer.addresses.index'));

    $address1 = Address::where('user_id', $userA->id)->firstOrFail();
    expect($address1->is_default)->toBeTrue();

    $this->actingAs($userA)
        ->post(route('customer.addresses.store'), [
            'title' => 'Ofis Adresim',
            'city' => 'Ankara',
            'district' => 'Çankaya',
            'full_address' => 'Tunalı Hilmi Caddesi No:42 Kat:3',
            'is_default' => false,
        ])
        ->assertRedirect(route('customer.addresses.index'));

    $address2 = Address::where('user_id', $userA->id)->where('title', 'Ofis Adresim')->firstOrFail();

    $this->actingAs($userA)
        ->patch(route('customer.addresses.default', $address2))
        ->assertRedirect(route('customer.addresses.index'));

    expect($address1->refresh()->is_default)->toBeFalse();
    expect($address2->refresh()->is_default)->toBeTrue();

    // Başka kullanıcı (userB) userA'nın adresini düzenleyemez/silemez
    $this->actingAs($userB)
        ->get(route('customer.addresses.edit', $address1))
        ->assertForbidden();

    $this->actingAs($userB)
        ->delete(route('customer.addresses.destroy', $address1))
        ->assertForbidden();
});
