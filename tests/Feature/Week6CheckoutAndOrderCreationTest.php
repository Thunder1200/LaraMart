<?php

use App\Actions\Orders\CreateOrderAction;
use App\Mail\OrderConfirmationMail;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\SubOrder;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

test('paying a cart with products from 2 sellers creates 1 order, 2 sub-orders, decrements stock, marks coupon used, clears cart and queues email', function () {
    Mail::fake();

    $customer = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $customer->id, 'is_default' => true]);

    $category = Category::factory()->create(['commission_rate' => 10.00]);
    $sellerA = Seller::factory()->create(['store_name' => 'Satıcı Alfa', 'status' => 'approved', 'commission_rate' => null]);
    $sellerB = Seller::factory()->create(['store_name' => 'Satıcı Beta', 'status' => 'approved', 'commission_rate' => null]);

    $productA = Product::factory()->create([
        'seller_id' => $sellerA->id,
        'category_id' => $category->id,
        'name' => 'Alfa Kulaklık',
        'status' => 'active',
    ]);
    $variantA = ProductVariant::factory()->create([
        'product_id' => $productA->id,
        'price' => 400.00,
        'discount_price' => null,
        'stock' => 10,
    ]);

    $productB = Product::factory()->create([
        'seller_id' => $sellerB->id,
        'category_id' => $category->id,
        'name' => 'Beta Klavye',
        'status' => 'active',
    ]);
    $variantB = ProductVariant::factory()->create([
        'product_id' => $productB->id,
        'price' => 200.00,
        'discount_price' => null,
        'stock' => 8,
    ]);

    $coupon = Coupon::factory()->create([
        'code' => 'KUPON10',
        'type' => 'percentage',
        'value' => 10.00,
        'min_total' => 200.00,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(10),
        'usage_limit' => 50,
        'used_count' => 0,
    ]);

    // 2 farklı satıcıdan ürünleri sepete ekle ve kupon uygula
    $this->actingAs($customer)->post(route('cart.store'), ['variant_id' => $variantA->id, 'quantity' => 1]);
    $this->actingAs($customer)->post(route('cart.store'), ['variant_id' => $variantB->id, 'quantity' => 1]);
    $this->actingAs($customer)->post(route('cart.coupon.apply'), ['code' => 'KUPON10']);

    $idempotencyKey = (string) Str::uuid();

    // Checkout başlat -> 3D Secure ekranına yönlenir
    $startResponse = $this->actingAs($customer)
        ->post(route('checkout.start'), [
            'address_id' => $address->id,
            'idempotency_key' => $idempotencyKey,
            'distance_sales_agreement' => '1',
            'card_holder' => $customer->name,
            'card_number' => '5528790000000008',
            'expire_month' => '12',
            'expire_year' => '2030',
            'cvc' => '123',
        ]);

    $startResponse->assertRedirect();
    $redirectLocation = (string) $startResponse->headers->get('Location');
    $token = basename((string) parse_url($redirectLocation, PHP_URL_PATH));

    // 3D Secure callback onayı
    $callbackResponse = $this->actingAs($customer)
        ->post(route('checkout.iyzico.callback'), [
            'token' => $token,
            'status' => 'success',
            'mdStatus' => '1',
            'paymentId' => $token,
            'sms_code' => '123456',
            'action' => 'approve',
        ]);

    $order = Order::with(['subOrders.items', 'payments'])->firstOrFail();

    $callbackResponse->assertRedirect(route('customer.orders.show', $order));

    expect(Order::count())->toBe(1);
    expect(SubOrder::count())->toBe(2);
    expect(OrderItem::count())->toBe(2);
    expect(Payment::count())->toBe(1);

    expect((float) $order->subtotal)->toBe(600.00);
    expect((float) $order->discount)->toBe(60.00);
    expect((float) $order->shipping)->toBe(49.90);
    expect((float) $order->total)->toBe(589.90);
    expect($order->payment_status)->toBe('paid');

    // Stoklar düştü
    expect($variantA->refresh()->stock)->toBe(9);
    expect($variantB->refresh()->stock)->toBe(7);

    // Kupon kullanılmış sayıldı
    expect($coupon->refresh()->used_count)->toBe(1);

    // Sepet boşaltıldı
    expect(Cart::where('user_id', $customer->id)->firstOrFail()->items()->count())->toBe(0);

    // Sipariş onay e-postası kuyruğa atıldı
    Mail::assertQueued(OrderConfirmationMail::class, fn (OrderConfirmationMail $mail) => $mail->hasTo($customer->email));

    // Snapshot koruması: ürün fiyatı sonradan değişse bile eski sipariş kalemi bozulmaz
    $variantA->update(['price' => 999.00]);
    expect((float) $order->subOrders->firstWhere('seller_id', $sellerA->id)->items->first()->price)->toBe(400.00);
});

test('two users cannot buy the last 1 stock item at the same time', function () {
    Mail::fake();

    $userA = User::factory()->create();
    $addressA = Address::factory()->create(['user_id' => $userA->id]);

    $userB = User::factory()->create();
    $addressB = Address::factory()->create(['user_id' => $userB->id]);

    $product = Product::factory()->create(['name' => 'Son 1 Adet Ürün', 'status' => 'active']);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 350.00,
        'discount_price' => null,
        'stock' => 1,
    ]);

    // Her iki kullanıcı da son 1 stoklu ürünü sepetine ekliyor
    $this->actingAs($userA)->post(route('cart.store'), ['variant_id' => $variant->id, 'quantity' => 1]);
    $this->actingAs($userB)->post(route('cart.store'), ['variant_id' => $variant->id, 'quantity' => 1]);

    // Her ikisi de 3D Secure adımına geçiyor
    $respA = $this->actingAs($userA)->post(route('checkout.start'), [
        'address_id' => $addressA->id,
        'idempotency_key' => (string) Str::uuid(),
        'distance_sales_agreement' => '1',
        'card_holder' => $userA->name,
        'card_number' => '5528790000000008',
        'expire_month' => '12',
        'expire_year' => '2030',
        'cvc' => '123',
    ]);
    $tokenA = basename((string) parse_url((string) $respA->headers->get('Location'), PHP_URL_PATH));

    $respB = $this->actingAs($userB)->post(route('checkout.start'), [
        'address_id' => $addressB->id,
        'idempotency_key' => (string) Str::uuid(),
        'distance_sales_agreement' => '1',
        'card_holder' => $userB->name,
        'card_number' => '5528790000000008',
        'expire_month' => '12',
        'expire_year' => '2030',
        'cvc' => '123',
    ]);
    $tokenB = basename((string) parse_url((string) $respB->headers->get('Location'), PHP_URL_PATH));

    // Kullanıcı A ödemeyi ilk onaylar -> başarılı
    $this->actingAs($userA)->post(route('checkout.iyzico.callback'), [
        'token' => $tokenA,
        'status' => 'success',
        'mdStatus' => '1',
        'paymentId' => $tokenA,
        'sms_code' => '123456',
        'action' => 'approve',
    ])->assertRedirect();

    expect(Order::count())->toBe(1);
    expect($variant->refresh()->stock)->toBe(0);

    // Kullanıcı B hemen ardından ödemeyi onaylamaya çalışır -> stok bittiği için reddedilir!
    $this->actingAs($userB)->post(route('checkout.iyzico.callback'), [
        'token' => $tokenB,
        'status' => 'success',
        'mdStatus' => '1',
        'paymentId' => $tokenB,
        'sms_code' => '123456',
        'action' => 'approve',
    ])->assertRedirect(route('checkout.error'));

    expect(Order::count())->toBe(1);
    expect(SubOrder::count())->toBe(1);
    expect($variant->refresh()->stock)->toBe(0);
});

test('failed payment leaves database completely unchanged and redirects to error page', function () {
    Mail::fake();

    $customer = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $customer->id]);
    $product = Product::factory()->create(['status' => 'active']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 500.00, 'stock' => 5]);

    $this->actingAs($customer)->post(route('cart.store'), ['variant_id' => $variant->id, 'quantity' => 2]);

    // Başarısız test kartı (sonu 0000)
    $this->actingAs($customer)->post(route('checkout.start'), [
        'address_id' => $address->id,
        'idempotency_key' => (string) Str::uuid(),
        'distance_sales_agreement' => '1',
        'card_holder' => $customer->name,
        'card_number' => '4546710000000000',
        'expire_month' => '12',
        'expire_year' => '2030',
        'cvc' => '123',
    ])->assertRedirect(route('checkout.error'));

    expect(Order::count())->toBe(0);
    expect(SubOrder::count())->toBe(0);
    expect(Payment::count())->toBe(0);
    expect($variant->refresh()->stock)->toBe(5);
    expect(Cart::where('user_id', $customer->id)->firstOrFail()->items()->count())->toBe(1);
    Mail::assertNothingQueued();
});

test('submitting the same idempotency key twice does not create duplicate orders', function () {
    Mail::fake();

    $customer = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $customer->id]);
    $product = Product::factory()->create(['status' => 'active']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 320.00, 'stock' => 10]);

    $cart = Cart::create(['user_id' => $customer->id]);
    $cart->items()->create(['variant_id' => $variant->id, 'quantity' => 1]);

    $key = (string) Str::uuid();
    $action = app(CreateOrderAction::class);

    $firstOrder = $action->execute($customer, $address, $key, ['transaction_id' => 'TX-1']);
    $secondOrder = $action->execute($customer, $address, $key, ['transaction_id' => 'TX-1']);

    expect($secondOrder->id)->toBe($firstOrder->id);
    expect(Order::count())->toBe(1);
    expect($variant->refresh()->stock)->toBe(9);
});

test('distance sales agreement checkbox is required during checkout', function () {
    $customer = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $customer->id]);
    $product = Product::factory()->create(['status' => 'active']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 300.00, 'stock' => 5]);

    $this->actingAs($customer)->post(route('cart.store'), ['variant_id' => $variant->id, 'quantity' => 1]);

    $this->actingAs($customer)
        ->post(route('checkout.start'), [
            'address_id' => $address->id,
            'idempotency_key' => (string) Str::uuid(),
            'card_holder' => $customer->name,
            'card_number' => '5528790000000008',
            'expire_month' => '12',
            'expire_year' => '2030',
            'cvc' => '123',
        ])
        ->assertSessionHasErrors('distance_sales_agreement');
});

test('customer can view own orders while another customer receives 403', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $order = Order::factory()->create(['user_id' => $userA->id]);
    SubOrder::factory()->create(['order_id' => $order->id]);

    $this->actingAs($userA)
        ->get(route('customer.orders.index'))
        ->assertOk()
        ->assertSee($order->order_number);

    $this->actingAs($userA)
        ->get(route('customer.orders.show', $order))
        ->assertOk()
        ->assertSee($order->order_number);

    $this->actingAs($userB)
        ->get(route('customer.orders.show', $order))
        ->assertForbidden();
});
