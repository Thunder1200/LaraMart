<?php

use App\Enums\CouponType;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Enums\ReturnStatus;
use App\Enums\SellerStatus;
use App\Enums\SubOrderStatus;
use App\Models\Address;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\Review;
use App\Models\Seller;
use App\Models\SellerPayout;
use App\Models\SubOrder;
use App\Models\User;

test('sub order status enum tum etiketleri dogru dondurur', function ()
{
    expect(SubOrderStatus::PendingApproval->label())->toBe('Onay Bekliyor')
        ->and(SubOrderStatus::Preparing->label())->toBe('Hazırlanıyor')
        ->and(SubOrderStatus::Shipped->label())->toBe('Kargoya Verildi')
        ->and(SubOrderStatus::Delivered->label())->toBe('Teslim Edildi')
        ->and(SubOrderStatus::Cancelled->label())->toBe('İptal')
        ->and(SubOrderStatus::Returned->label())->toBe('İade');
});

test('sub order status enum iptal ve iade durumlarindan baska duruma gecise izin vermez', function ()
{
    expect(SubOrderStatus::Cancelled->canTransitionTo(SubOrderStatus::Preparing))->toBeFalse()
        ->and(SubOrderStatus::Returned->canTransitionTo(SubOrderStatus::Delivered))->toBeFalse();
});

test('seller status enum etiketleri ve isApproved metodu dogru calisir', function ()
{
    $seller = Seller::factory()->create(['status' => SellerStatus::Approved]);
    expect($seller->isApproved())->toBeTrue()
        ->and(SellerStatus::Pending->label())->toBe('Beklemede');
});

test('product status enum etiketleri dogru dondurur', function ()
{
    expect(ProductStatus::Active->label())->toBe('Aktif')
        ->and(ProductStatus::Draft->label())->toBe('Taslak')
        ->and(ProductStatus::Inactive->label())->toBe('Pasif');
});

test('payment status ve return status enum etiketleri dogru dondurur', function ()
{
    expect(PaymentStatus::Paid->label())->toBe('Ödendi')
        ->and(ReturnStatus::Approved->label())->toBe('Onaylandı')
        ->and(CouponType::Percentage->label())->toBe('Yüzde İndirimi');
});

test('address toSnapshot metodu siparis kopyasi icin gerekli alanlari uretir', function ()
{
    $address = Address::factory()->create([
        'title' => 'Evim',
        'city' => 'İzmir',
        'district' => 'Bornova',
        'full_address' => 'Kazımdirik Mah. 100. Sok.',
    ]);

    $snapshot = $address->toSnapshot();

    expect($snapshot)->toHaveKeys(['id', 'title', 'city', 'district', 'full_address'])
        ->and($snapshot['city'])->toBe('İzmir');
});

test('kupon kullanim limiti doldugunda isUsageLimitReached true doner', function ()
{
    $coupon = Coupon::factory()->create([
        'usage_limit' => 5,
        'used_count' => 5,
    ]);

    expect($coupon->isUsageLimitReached())->toBeTrue();
});

test('gelecek tarihli kupon icin isNotStarted true doner', function ()
{
    $coupon = Coupon::factory()->create([
        'starts_at' => now()->addDays(2),
    ]);

    expect($coupon->isNotStarted())->toBeTrue();
});

