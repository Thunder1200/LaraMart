<?php

use App\Actions\Orders\UpdateSubOrderStatusAction;
use App\Enums\SubOrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\Review;
use App\Models\Seller;
use App\Models\SubOrder;
use App\Models\User;
use App\Notifications\ReturnRequestDecidedNotification;
use App\Notifications\SubOrderStatusUpdatedNotification;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
    Role::findOrCreate('seller', 'web');
    Role::findOrCreate('customer', 'web');
});

test('sub-order status Enum enforces valid transitions and blocks invalid transitions like Delivered to Preparing', function () {
    expect(SubOrderStatus::PendingApproval->canTransitionTo(SubOrderStatus::Preparing))->toBeTrue();
    expect(SubOrderStatus::PendingApproval->canTransitionTo(SubOrderStatus::Cancelled))->toBeTrue();
    expect(SubOrderStatus::PendingApproval->canTransitionTo(SubOrderStatus::Delivered))->toBeFalse();

    expect(SubOrderStatus::Preparing->canTransitionTo(SubOrderStatus::Shipped))->toBeTrue();
    expect(SubOrderStatus::Shipped->canTransitionTo(SubOrderStatus::Delivered))->toBeTrue();
    expect(SubOrderStatus::Delivered->canTransitionTo(SubOrderStatus::Returned))->toBeTrue();

    // Geçersiz durum geçişleri engellenir
    expect(SubOrderStatus::Delivered->canTransitionTo(SubOrderStatus::Preparing))->toBeFalse();
    expect(SubOrderStatus::Delivered->canTransitionTo(SubOrderStatus::Shipped))->toBeFalse();
    expect(SubOrderStatus::Cancelled->canTransitionTo(SubOrderStatus::Preparing))->toBeFalse();
    expect(SubOrderStatus::Returned->canTransitionTo(SubOrderStatus::Delivered))->toBeFalse();

    $sellerUser = User::factory()->create();
    $sellerUser->assignRole('seller');
    $seller = Seller::factory()->create(['user_id' => $sellerUser->id, 'status' => 'approved']);
    $customer = User::factory()->create();

    $order = Order::factory()->create(['user_id' => $customer->id]);
    $subOrder = SubOrder::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $seller->id,
        'status' => SubOrderStatus::Delivered,
        'delivered_at' => now(),
    ]);

    // Action doğrudan çağrıldığında DomainException fırlatmalı
    expect(fn () => app(UpdateSubOrderStatusAction::class)->execute($subOrder, SubOrderStatus::Preparing))
        ->toThrow(DomainException::class);

    // HTTP üzerinden geçersiz geçiş denenirse hata mesajı dönmeli ve durum değişmemeli
    $this->actingAs($sellerUser)
        ->patch(route('seller.orders.update-status', $subOrder), [
            'status' => SubOrderStatus::Preparing->value,
        ])
        ->assertRedirect(route('seller.orders.show', $subOrder))
        ->assertSessionHas('error');

    expect($subOrder->refresh()->status)->toBe(SubOrderStatus::Delivered);
});

test('seller advances sub-order through lifecycle with cargo tracking, notifies customer via mail and database, and cross-seller access returns 403', function () {
    Notification::fake();

    $sellerUserA = User::factory()->create();
    $sellerUserA->assignRole('seller');
    $sellerA = Seller::factory()->create(['user_id' => $sellerUserA->id, 'status' => 'approved']);

    $sellerUserB = User::factory()->create();
    $sellerUserB->assignRole('seller');
    Seller::factory()->create(['user_id' => $sellerUserB->id, 'status' => 'approved']);

    $customer = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $customer->id]);
    $subOrder = SubOrder::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $sellerA->id,
        'status' => SubOrderStatus::PendingApproval,
    ]);

    // Satıcı B, Satıcı A'nın siparişini göremez ve güncelleyemez (403)
    $this->actingAs($sellerUserB)->get(route('seller.orders.show', $subOrder))->assertForbidden();
    $this->actingAs($sellerUserB)->patch(route('seller.orders.update-status', $subOrder), [
        'status' => SubOrderStatus::Preparing->value,
    ])->assertForbidden();

    // 1. Onay Bekliyor -> Hazırlanıyor
    $this->actingAs($sellerUserA)
        ->patch(route('seller.orders.update-status', $subOrder), [
            'status' => SubOrderStatus::Preparing->value,
        ])
        ->assertRedirect(route('seller.orders.show', $subOrder))
        ->assertSessionHas('status');

    expect($subOrder->refresh()->status)->toBe(SubOrderStatus::Preparing);

    // 2. Kargo bilgisi olmadan Kargoya Verildi seçilemez
    $this->actingAs($sellerUserA)
        ->patch(route('seller.orders.update-status', $subOrder), [
            'status' => SubOrderStatus::Shipped->value,
        ])
        ->assertSessionHasErrors(['cargo_company', 'tracking_number']);

    // 3. Kargo firması ve takip no ile Kargoya Verildi
    $this->actingAs($sellerUserA)
        ->patch(route('seller.orders.update-status', $subOrder), [
            'status' => SubOrderStatus::Shipped->value,
            'cargo_company' => 'Yurtiçi Kargo',
            'tracking_number' => 'YK9988776655',
        ])
        ->assertRedirect(route('seller.orders.show', $subOrder));

    $subOrder->refresh();
    expect($subOrder->status)->toBe(SubOrderStatus::Shipped);
    expect($subOrder->cargo_company)->toBe('Yurtiçi Kargo');
    expect($subOrder->tracking_number)->toBe('YK9988776655');

    // 4. Teslim Edildi
    $this->actingAs($sellerUserA)
        ->patch(route('seller.orders.update-status', $subOrder), [
            'status' => SubOrderStatus::Delivered->value,
        ])
        ->assertRedirect(route('seller.orders.show', $subOrder));

    $subOrder->refresh();
    expect($subOrder->status)->toBe(SubOrderStatus::Delivered);
    expect($subOrder->delivered_at)->not->toBeNull();

    Notification::assertSentToTimes($customer, SubOrderStatusUpdatedNotification::class, 3);
});

test('customer receives database notifications and can mark them as read', function () {
    $sellerUser = User::factory()->create();
    $sellerUser->assignRole('seller');
    $seller = Seller::factory()->create(['user_id' => $sellerUser->id, 'status' => 'approved']);

    $customer = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $customer->id]);
    $subOrder = SubOrder::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $seller->id,
        'status' => SubOrderStatus::PendingApproval,
    ]);

    app(UpdateSubOrderStatusAction::class)->execute($subOrder, SubOrderStatus::Preparing);

    expect($customer->unreadNotifications()->count())->toBe(1);

    $this->actingAs($customer)
        ->get(route('customer.notifications.index'))
        ->assertOk()
        ->assertSee('Hazırlanıyor');

    $this->actingAs($customer)
        ->post(route('customer.notifications.read-all'))
        ->assertRedirect(route('customer.notifications.index'));

    expect($customer->unreadNotifications()->count())->toBe(0);
});

test('customer can open return request within 14 days of delivery, blocked after 14 days, and seller approval restores stock and marks returned', function () {
    Notification::fake();

    $sellerUser = User::factory()->create();
    $sellerUser->assignRole('seller');
    $seller = Seller::factory()->create(['user_id' => $sellerUser->id, 'status' => 'approved']);

    $customer = User::factory()->create();
    $product = Product::factory()->create(['seller_id' => $seller->id, 'status' => 'active']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450.00, 'stock' => 5]);

    // 15 gün önce teslim edilen sipariş -> 14 gün geçtiği için iade engellenmeli
    $expiredOrder = Order::factory()->create(['user_id' => $customer->id]);
    $expiredSub = SubOrder::factory()->create([
        'order_id' => $expiredOrder->id,
        'seller_id' => $seller->id,
        'status' => SubOrderStatus::Delivered,
        'delivered_at' => now()->subDays(15),
    ]);
    $expiredItem = OrderItem::factory()->create([
        'sub_order_id' => $expiredSub->id,
        'variant_id' => $variant->id,
        'price' => 450.00,
        'quantity' => 1,
    ]);

    $this->actingAs($customer)
        ->post(route('customer.returns.store', $expiredItem), [
            'reason' => 'Ürün beklentimi karşılamadı iade etmek istiyorum.',
        ])
        ->assertSessionHas('error');

    expect(ReturnRequest::count())->toBe(0);

    // 3 gün önce teslim edilen sipariş -> iade talebi açılabilir
    $validOrder = Order::factory()->create(['user_id' => $customer->id]);
    $validSub = SubOrder::factory()->create([
        'order_id' => $validOrder->id,
        'seller_id' => $seller->id,
        'status' => SubOrderStatus::Delivered,
        'delivered_at' => now()->subDays(3),
    ]);
    $validItem = OrderItem::factory()->create([
        'sub_order_id' => $validSub->id,
        'variant_id' => $variant->id,
        'price' => 450.00,
        'quantity' => 2,
    ]);

    $this->actingAs($customer)
        ->post(route('customer.returns.store', $validItem), [
            'reason' => 'Ürün kutusu hasarlı geldiği için iade talep ediyorum.',
        ])
        ->assertRedirect(route('customer.orders.show', $validOrder))
        ->assertSessionHas('status');

    $returnRequest = ReturnRequest::firstOrFail();
    expect($returnRequest->status)->toBe('pending');
    expect((float) $returnRequest->refund_amount)->toBe(900.00);

    // Satıcı iadeyi onaylar -> stok 5 + 2 = 7 olur, alt sipariş Returned olur, müşteriye bildirim gider
    $this->actingAs($sellerUser)
        ->patch(route('seller.returns.decide', $returnRequest), [
            'decision' => 'approved',
        ])
        ->assertSessionHas('status');

    expect($returnRequest->refresh()->status)->toBe('approved');
    expect($variant->refresh()->stock)->toBe(7);
    expect($validSub->refresh()->status)->toBe(SubOrderStatus::Returned);

    Notification::assertSentTo($customer, ReturnRequestDecidedNotification::class);
    Notification::assertSentTo($customer, SubOrderStatusUpdatedNotification::class);
});

test('non-buyer cannot see review form, while verified delivered buyer can submit review and it appears after admin approval', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $buyer = User::factory()->create();
    $buyer->assignRole('customer');

    $nonBuyer = User::factory()->create();
    $nonBuyer->assignRole('customer');

    $product = Product::factory()->create(['name' => 'Test Kablosuz Kulaklık', 'status' => 'active']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 899.00, 'stock' => 10]);

    // Misafir ve satın almamış müşteri yorum formunu GÖREMEZ
    $this->get(route('products.show', $product->slug))
        ->assertOk()
        ->assertDontSee('data-test="review-form"', false);

    $this->actingAs($nonBuyer)
        ->get(route('products.show', $product->slug))
        ->assertOk()
        ->assertDontSee('data-test="review-form"', false);

    // Teslim edilmemiş (shipped) siparişi olan müşteri de yorum formunu GÖREMEZ
    $order = Order::factory()->create(['user_id' => $buyer->id]);
    $subOrder = SubOrder::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $product->seller_id,
        'status' => SubOrderStatus::Shipped,
    ]);
    $orderItem = OrderItem::factory()->create([
        'sub_order_id' => $subOrder->id,
        'variant_id' => $variant->id,
        'product_name' => $product->name,
        'price' => 899.00,
        'quantity' => 1,
    ]);

    $this->actingAs($buyer)
        ->get(route('products.show', $product->slug))
        ->assertOk()
        ->assertDontSee('data-test="review-form"', false);

    // Satın almamış kişi POST atarsa 403 alır
    $this->actingAs($nonBuyer)
        ->post(route('customer.reviews.store', $product->slug), [
            'order_item_id' => $orderItem->id,
            'rating' => 5,
            'comment' => 'Satın almadan yorum denemesi yapıyorum.',
        ])
        ->assertForbidden();

    // Sipariş teslim edilince alıcı yorum formunu görür ve yorum gönderebilir
    $subOrder->update(['status' => SubOrderStatus::Delivered, 'delivered_at' => now()]);

    $this->actingAs($buyer)
        ->get(route('products.show', $product->slug))
        ->assertOk()
        ->assertSee('data-test="review-form"', false);

    $this->actingAs($buyer)
        ->post(route('customer.reviews.store', $product->slug), [
            'order_item_id' => $orderItem->id,
            'rating' => 5,
            'comment' => 'Ses kalitesi harika, paketleme özenli ve kargo çok hızlıydı.',
        ])
        ->assertRedirect(route('products.show', $product->slug))
        ->assertSessionHas('status');

    $review = Review::firstOrFail();
    expect($review->is_approved)->toBeFalse();

    // Admin onaylamadan ürün sayfasında görünmez
    $this->get(route('products.show', $product->slug))
        ->assertOk()
        ->assertDontSee('Ses kalitesi harika, paketleme özenli ve kargo çok hızlıydı.');

    // Admin onaylar -> ürün sayfasında görünür ve ortalama puan güncellenir
    $this->actingAs($admin)
        ->patch(route('admin.reviews.approve', $review))
        ->assertSessionHas('status');

    expect($review->refresh()->is_approved)->toBeTrue();

    $this->get(route('products.show', $product->slug))
        ->assertOk()
        ->assertSee('Ses kalitesi harika, paketleme özenli ve kargo çok hızlıydı.')
        ->assertSee('5.0');
});
