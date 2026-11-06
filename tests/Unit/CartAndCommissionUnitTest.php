<?php

use App\Enums\CouponType;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\User;
use App\Services\CalculateCartTotalsService;
use App\Services\CommissionCalculatorService;

test('ara toplam tek satici ve birden fazla urun icin dogru hesaplanir', function ()
{
    $service = app(CalculateCartTotalsService::class);
    $seller = Seller::factory()->create();
    $product = Product::factory()->create(['seller_id' => $seller->id]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => '120.00',
        'discount_price' => null,
        'stock' => 10,
    ]);

    $cart = Cart::factory()->create();
    $service->addItem($cart, $variant, 2);

    $totals = $service->calculate($cart->fresh());

    expect($totals['subtotal'])->toBe('240.00')
        ->and($totals['shipping'])->toBe('49.90')
        ->and($totals['total'])->toBe('289.90');
});

test('300 tl ustu alisveriste satici kargosu ucretsiz olur', function ()
{
    $service = app(CalculateCartTotalsService::class);
    $seller = Seller::factory()->create();
    $product = Product::factory()->create(['seller_id' => $seller->id]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => '350.00',
        'discount_price' => null,
        'stock' => 10,
    ]);

    $cart = Cart::factory()->create();
    $service->addItem($cart, $variant, 1);

    $totals = $service->calculate($cart->fresh());

    expect($totals['subtotal'])->toBe('350.00')
        ->and($totals['shipping'])->toBe('0.00')
        ->and($totals['total'])->toBe('350.00');
});

test('iki farkli saticidan urun eklendiginde kargo satici basina ayri hesaplanir', function ()
{
    $service = app(CalculateCartTotalsService::class);
    $sellerA = Seller::factory()->create();
    $sellerB = Seller::factory()->create();

    $productA = Product::factory()->create(['seller_id' => $sellerA->id]);
    $productB = Product::factory()->create(['seller_id' => $sellerB->id]);

    $variantA = ProductVariant::factory()->create([
        'product_id' => $productA->id,
        'price' => '100.00',
        'discount_price' => null,
        'stock' => 10,
    ]);

    $variantB = ProductVariant::factory()->create([
        'product_id' => $productB->id,
        'price' => '400.00',
        'discount_price' => null,
        'stock' => 10,
    ]);

    $cart = Cart::factory()->create();
    $service->addItem($cart, $variantA, 1);
    $service->addItem($cart, $variantB, 1);

    $totals = $service->calculate($cart->fresh());

    expect(count($totals['seller_groups']))->toBe(2)
        ->and($totals['subtotal'])->toBe('500.00')
        ->and($totals['shipping'])->toBe('49.90')
        ->and($totals['total'])->toBe('549.90');
});

test('yuzdelik kupon indirimi genel toplamdan dogru dusulur', function ()
{
    $service = app(CalculateCartTotalsService::class);
    $seller = Seller::factory()->create();
    $product = Product::factory()->create(['seller_id' => $seller->id]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => '500.00',
        'discount_price' => null,
        'stock' => 10,
    ]);

    Coupon::factory()->create([
        'code' => 'YUZDE20',
        'type' => CouponType::Percentage,
        'value' => '20.00',
        'min_total' => '200.00',
    ]);

    $cart = Cart::factory()->create();
    $service->addItem($cart, $variant, 1);
    $service->applyCoupon($cart, 'YUZDE20');

    $totals = $service->calculate($cart->fresh());

    expect($totals['discount'])->toBe('100.00')
        ->and($totals['total'])->toBe('400.00');
});

test('sabit tutarli kupon indirimi dogru uygulanir', function ()
{
    $service = app(CalculateCartTotalsService::class);
    $seller = Seller::factory()->create();
    $product = Product::factory()->create(['seller_id' => $seller->id]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => '600.00',
        'discount_price' => null,
        'stock' => 10,
    ]);

    Coupon::factory()->create([
        'code' => 'SABIT150',
        'type' => CouponType::Fixed,
        'value' => '150.00',
        'min_total' => '300.00',
    ]);

    $cart = Cart::factory()->create();
    $service->addItem($cart, $variant, 1);
    $service->applyCoupon($cart, 'SABIT150');

    $totals = $service->calculate($cart->fresh());

    expect($totals['discount'])->toBe('150.00')
        ->and($totals['total'])->toBe('450.00');
});

test('suresi dolmus kupon uygulandiginda hata mesaji firlatilir', function ()
{
    $service = app(CalculateCartTotalsService::class);
    $seller = Seller::factory()->create();
    $product = Product::factory()->create(['seller_id' => $seller->id]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => '400.00',
        'discount_price' => null,
        'stock' => 10,
    ]);

    Coupon::factory()->create([
        'code' => 'BITMISKUPON',
        'type' => CouponType::Percentage,
        'value' => '15.00',
        'min_total' => '100.00',
        'starts_at' => now()->subDays(20),
        'ends_at' => now()->subDay(),
    ]);

    $cart = Cart::factory()->create();
    $service->addItem($cart, $variant, 1);

    expect(fn () => $service->applyCoupon($cart, 'BITMISKUPON'))
        ->toThrow(InvalidArgumentException::class, 'Bu kuponun kullanım süresi dolmuştur.');
});

test('minimum sepet tutari saglanmadiginda kupon hata verir', function ()
{
    $service = app(CalculateCartTotalsService::class);
    $seller = Seller::factory()->create();
    $product = Product::factory()->create(['seller_id' => $seller->id]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => '150.00',
        'discount_price' => null,
        'stock' => 10,
    ]);

    Coupon::factory()->create([
        'code' => 'MIN500',
        'type' => CouponType::Fixed,
        'value' => '50.00',
        'min_total' => '500.00',
    ]);

    $cart = Cart::factory()->create();
    $service->addItem($cart, $variant, 1);

    expect(fn () => $service->applyCoupon($cart, 'MIN500'))
        ->toThrow(InvalidArgumentException::class);
});

test('stoktan fazla urun sepete eklenemez', function ()
{
    $service = app(CalculateCartTotalsService::class);
    $variant = ProductVariant::factory()->create(['stock' => 3]);
    $cart = Cart::factory()->create();

    expect(fn () => $service->addItem($cart, $variant, 4))
        ->toThrow(InvalidArgumentException::class, 'Stoktan fazla ürün sepete eklenemez.');
});

test('kategori bazli komisyon tutari ve satici net kazanci dogru hesaplanir', function ()
{
    $commissionService = app(CommissionCalculatorService::class);
    $category = Category::factory()->create(['commission_rate' => '15.00']);
    $seller = Seller::factory()->create(['commission_rate' => '8.00']);

    $result = $commissionService->calculateForSubOrder(1000.00, $category, $seller);

    expect($result['rate'])->toBe('15.00')
        ->and($result['gross'])->toBe('1000.00')
        ->and($result['commission'])->toBe('150.00')
        ->and($result['net'])->toBe('850.00');
});

test('indirimli varyant fiyati varsa efektif fiyat olarak indirimli fiyat kullanilir', function ()
{
    $variant = ProductVariant::factory()->create([
        'price' => '500.00',
        'discount_price' => '399.90',
    ]);

    expect($variant->effectivePrice())->toBe('399.90');
});
