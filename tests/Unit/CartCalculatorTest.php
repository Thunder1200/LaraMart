<?php

use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Services\CartCalculator;
use Carbon\Carbon;
use Tests\TestCase;

uses(TestCase::class);

function makeUnitCartItem(int $sellerId, string $storeName, float $price, int $quantity, ?float $discountPrice = null): CartItem
{
    $seller = new Seller([
        'store_name' => $storeName,
        'slug' => 'magaza-'.$sellerId,
    ]);
    $seller->id = $sellerId;

    $product = new Product([
        'seller_id' => $sellerId,
        'name' => 'Test Ürünü',
    ]);
    $product->setRelation('seller', $seller);

    $variant = new ProductVariant([
        'price' => $price,
        'discount_price' => $discountPrice,
        'stock' => 50,
    ]);
    $variant->setRelation('product', $product);

    $item = new CartItem(['quantity' => $quantity]);
    $item->setRelation('variant', $variant);

    return $item;
}

test('calculates subtotal, shipping fee and grand total below free shipping threshold', function () {
    $calculator = new CartCalculator;
    $items = collect([
        makeUnitCartItem(1, 'Satıcı A', 100.00, 2),
    ]);

    $summary = $calculator->calculate($items);

    expect($summary['subtotal'])->toBe(200.00);
    expect($summary['shipping'])->toBe(49.90);
    expect($summary['discount'])->toBe(0.00);
    expect($summary['total'])->toBe(249.90);
    expect($summary['groups'][0]['free_shipping_remaining'])->toBe(100.00);
});

test('grants free shipping when seller subtotal reaches 300 TL threshold', function () {
    $calculator = new CartCalculator;
    $items = collect([
        makeUnitCartItem(1, 'Satıcı A', 150.00, 2),
    ]);

    $summary = $calculator->calculate($items);

    expect($summary['subtotal'])->toBe(300.00);
    expect($summary['shipping'])->toBe(0.00);
    expect($summary['total'])->toBe(300.00);
    expect($summary['groups'][0]['free_shipping_remaining'])->toBe(0.00);
});

test('groups items by seller and calculates shipping separately per seller', function () {
    $calculator = new CartCalculator;
    $items = collect([
        makeUnitCartItem(1, 'Satıcı A', 120.00, 1), // < 300 => 49.90 TL kargo
        makeUnitCartItem(2, 'Satıcı B', 400.00, 1), // >= 300 => 0.00 TL kargo
        makeUnitCartItem(3, 'Satıcı C', 200.00, 1, 150.00), // indirimli 150 TL < 300 => 49.90 TL kargo
    ]);

    $summary = $calculator->calculate($items);

    expect($summary['groups'])->toHaveCount(3);
    expect($summary['subtotal'])->toBe(670.00);
    expect($summary['shipping'])->toBe(99.80);
    expect($summary['total'])->toBe(769.80);
});

test('applies percentage coupon discount accurately', function () {
    $calculator = new CartCalculator;
    $now = Carbon::parse('2026-11-27 12:00:00');
    $items = collect([
        makeUnitCartItem(1, 'Satıcı A', 500.00, 1),
    ]);

    $coupon = new Coupon([
        'code' => 'YUZDE20',
        'type' => 'percentage',
        'value' => 20.00,
        'min_total' => 200.00,
        'starts_at' => $now->copy()->subDay(),
        'ends_at' => $now->copy()->addDays(5),
        'usage_limit' => 50,
        'used_count' => 1,
    ]);

    $summary = $calculator->calculate($items, $coupon, $now);

    expect($summary['subtotal'])->toBe(500.00);
    expect($summary['discount'])->toBe(100.00);
    expect($summary['shipping'])->toBe(0.00);
    expect($summary['total'])->toBe(400.00);
});

test('caps fixed coupon discount at subtotal so total never becomes negative', function () {
    $calculator = new CartCalculator;
    $now = Carbon::parse('2026-11-27 12:00:00');
    $items = collect([
        makeUnitCartItem(1, 'Satıcı A', 100.00, 1),
    ]);

    $coupon = new Coupon([
        'code' => 'SABIT250',
        'type' => 'fixed',
        'value' => 250.00,
        'min_total' => 0.00,
        'starts_at' => $now->copy()->subDay(),
        'ends_at' => $now->copy()->addDay(),
        'usage_limit' => null,
        'used_count' => 0,
    ]);

    $summary = $calculator->calculate($items, $coupon, $now);

    expect($summary['subtotal'])->toBe(100.00);
    expect($summary['discount'])->toBe(100.00);
    expect($summary['shipping'])->toBe(49.90);
    expect($summary['total'])->toBe(49.90);
});

test('rejects expired coupon with Turkish error message', function () {
    $calculator = new CartCalculator;
    $now = Carbon::parse('2026-11-27 12:00:00');

    $expiredCoupon = new Coupon([
        'code' => 'ESKI10',
        'type' => 'percentage',
        'value' => 10.00,
        'min_total' => 100.00,
        'starts_at' => $now->copy()->subDays(10),
        'ends_at' => $now->copy()->subDay(),
        'usage_limit' => 100,
        'used_count' => 0,
    ]);

    expect(fn () => $calculator->validateCoupon($expiredCoupon, 500.00, $now))
        ->toThrow(DomainException::class, 'Bu kuponun kullanım süresi dolmuştur.');
});

test('rejects coupon when minimum cart total is not met or usage limit is reached', function () {
    $calculator = new CartCalculator;
    $now = Carbon::parse('2026-11-27 12:00:00');

    $minTotalCoupon = new Coupon([
        'code' => 'MIN500',
        'type' => 'fixed',
        'value' => 50.00,
        'min_total' => 500.00,
        'starts_at' => $now->copy()->subDay(),
        'ends_at' => $now->copy()->addDay(),
        'usage_limit' => 10,
        'used_count' => 0,
    ]);

    expect(fn () => $calculator->validateCoupon($minTotalCoupon, 250.00, $now))
        ->toThrow(DomainException::class, 'en az 500,00 TL');

    $limitReachedCoupon = new Coupon([
        'code' => 'DOLU',
        'type' => 'fixed',
        'value' => 50.00,
        'min_total' => 0.00,
        'starts_at' => $now->copy()->subDay(),
        'ends_at' => $now->copy()->addDay(),
        'usage_limit' => 5,
        'used_count' => 5,
    ]);

    expect(fn () => $calculator->validateCoupon($limitReachedCoupon, 600.00, $now))
        ->toThrow(DomainException::class, 'kullanım limiti dolmuştur');
});
