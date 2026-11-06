<?php

use App\Actions\CreateOrderAction;
use App\Actions\MergeGuestCartAction;
use App\Actions\PostOrderInteractionAction;
use App\Actions\SellerManagementAction;
use App\Actions\TransitionSubOrderStatusAction;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Enums\ReturnStatus;
use App\Enums\SellerStatus;
use App\Enums\SubOrderStatus;
use App\Livewire\ProductVariantSelector;
use App\Mail\OrderConfirmedMail;
use App\Mail\SellerApprovedMail;
use App\Models\Address;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Favorite;
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
use App\Notifications\SubOrderStatusChangedNotification;
use App\Services\CalculateCartTotalsService;
use App\Services\ReportService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('hafta 1: veritabani seeder 1 admin 5 satici 20 musteri 15 kategori ve 100 urun olusturur', function ()
{
    $this->seed(DatabaseSeeder::class);

    expect(User::role('admin')->count())->toBe(1)
        ->and(Seller::count())->toBe(5)
        ->and(User::role('customer')->count())->toBe(20)
        ->and(Category::count())->toBe(15)
        ->and(Product::count())->toBe(100)
        ->and(ProductVariant::count())->toBe(200);
});

test('hafta 1: 20 zorunlu tablo iliskileri ve decimal alanlari dogru calisir', function ()
{
    $this->seed(DatabaseSeeder::class);

    $order = Order::with(['subOrders.items', 'payments', 'user'])->first();
    expect($order)->not->toBeNull()
        ->and($order->total)->toBeString()
        ->and($order->subOrders)->not->toBeEmpty()
        ->and(Banner::count())->toBeGreaterThan(0)
        ->and(SellerPayout::count())->toBeGreaterThan(0);
});

test('hafta 2: yeni kullanici kayit olabilir ve otomatik customer rolu alir', function ()
{
    $response = $this->post('/kayit', [
        'name' => 'Yeni Müşteri',
        'email' => 'yeni@laramart.test',
        'phone' => '05559998877',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertRedirect('/hesabim');
    $user = User::where('email', 'yeni@laramart.test')->first();
    expect($user)->not->toBeNull()
        ->and($user->hasRole('customer'))->toBeTrue();
});

test('hafta 2: musteri /admin adresine girmeye calisinca 403 alir', function ()
{
    Role::firstOrCreate(['name' => 'customer']);
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    $this->actingAs($customer)
        ->get('/admin')
        ->assertForbidden();
});

test('hafta 2: onaysiz satici /satici adresine girmeye calisinca 403 alir', function ()
{
    Role::firstOrCreate(['name' => 'seller']);
    $sellerUser = User::factory()->create();
    $sellerUser->assignRole('seller');

    Seller::factory()->create([
        'user_id' => $sellerUser->id,
        'status' => SellerStatus::Pending,
    ]);

    $this->actingAs($sellerUser)
        ->get('/satici')
        ->assertForbidden();
});

test('hafta 2: form dogrulama hatalari turkce doner', function ()
{
    $response = $this->from('/kayit')->post('/kayit', [
        'name' => '',
        'email' => 'gecersiz-mail',
        'password' => '123',
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'password']);
    $errors = session('errors')->all();
    expect(implode(' ', $errors))->toContain('Ad Soyad alanı zorunludur.');
});

test('hafta 2: mega menu kategorileri veritabanindan cekilir ve ana sayfada gorunur', function ()
{
    Category::factory()->create([
        'parent_id' => null,
        'name' => 'Mega Elektronik',
        'slug' => 'mega-elektronik',
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Mega Elektronik');
});

test('hafta 3: urun detay sayfasinda livewire ile varyant degisince fiyat ve stok sayfa yenilenmeden guncellenir', function ()
{
    $product = Product::factory()->create();
    $variant1 = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'color' => 'Siyah',
        'size' => 'M',
        'price' => '250.00',
        'discount_price' => null,
        'stock' => 12,
    ]);
    $variant2 = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'color' => 'Kırmızı',
        'size' => 'XL',
        'price' => '390.00',
        'discount_price' => null,
        'stock' => 4,
    ]);

    Livewire::test(ProductVariantSelector::class, ['product' => $product])
        ->assertSet('currentPrice', '250.00')
        ->assertSet('currentStock', 12)
        ->call('selectVariant', $variant2->id)
        ->assertSet('currentPrice', '390.00')
        ->assertSet('currentStock', 4);
});

test('hafta 3: seo uyumlu kategori ve urun urlleri 200 doner', function ()
{
    $category = Category::factory()->create(['slug' => 'spor-giyim']);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'slug' => 'profesyonel-kosu-ayakkabisi',
    ]);
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->get('/kategori/spor-giyim')->assertOk()->assertSee($product->name);
    $this->get('/urun/profesyonel-kosu-ayakkabisi')->assertOk()->assertSee($product->name);
});

test('hafta 4: satici A satici Bnin urun duzenleme adresine girerse 403 alir', function ()
{
    Role::firstOrCreate(['name' => 'seller']);

    $userA = User::factory()->create();
    $userA->assignRole('seller');
    $sellerA = Seller::factory()->create(['user_id' => $userA->id, 'status' => SellerStatus::Approved]);

    $userB = User::factory()->create();
    $userB->assignRole('seller');
    $sellerB = Seller::factory()->create(['user_id' => $userB->id, 'status' => SellerStatus::Approved]);

    $productB = Product::factory()->create(['seller_id' => $sellerB->id]);

    $this->actingAs($userA)
        ->get("/satici/urunler/{$productB->id}/duzenle")
        ->assertForbidden();

    $this->actingAs($userA)
        ->put("/satici/urunler/{$productB->id}", [
            'name' => 'Hack Ürün',
            'category_id' => $productB->category_id,
            'description' => 'Yetkisiz güncelleme denemesi',
            'status' => 'active',
        ])
        ->assertForbidden();
});

test('hafta 4: filtreli arama url query parametreleri ile ayni sonuclari getirir', function ()
{
    $brandX = Brand::factory()->create(['name' => 'TechX', 'slug' => 'techx']);
    $brandY = Brand::factory()->create(['name' => 'OtherBrand', 'slug' => 'other']);

    $matchingProduct = Product::factory()->create(['brand_id' => $brandX->id, 'name' => 'TechX Pro Laptop']);
    ProductVariant::factory()->create(['product_id' => $matchingProduct->id, 'price' => '2500.00', 'discount_price' => null]);

    $otherProduct = Product::factory()->create(['brand_id' => $brandY->id, 'name' => 'Ucuz Kulaklık']);
    ProductVariant::factory()->create(['product_id' => $otherProduct->id, 'price' => '50.00', 'discount_price' => null]);

    $this->get('/arama?marka=techx&min=1000')
        ->assertOk()
        ->assertSee('TechX Pro Laptop')
        ->assertDontSee('Ucuz Kulaklık');
});

test('hafta 4: satici basvurusu yapildiginda durum beklemede olarak kaydedilir', function ()
{
    Role::firstOrCreate(['name' => 'customer']);
    $user = User::factory()->create();
    $user->assignRole('customer');

    $this->actingAs($user)
        ->post('/hesabim/satici-basvuru', [
            'store_name' => 'Anadolu Ticaret',
            'tax_number' => '1234567890',
            'iban' => 'TR120000100010001000100012',
        ])
        ->assertRedirect();

    $seller = Seller::where('user_id', $user->id)->first();
    expect($seller)->not->toBeNull()
        ->and($seller->status)->toBe(SellerStatus::Pending);
});

test('hafta 4: herkese acik satici magaza sayfasi calisir', function ()
{
    $seller = Seller::factory()->create(['store_name' => 'Süper Mağaza', 'slug' => 'super-magaza']);
    $product = Product::factory()->create(['seller_id' => $seller->id, 'name' => 'Mağaza Özel Ürünü']);
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->get('/magaza/super-magaza')
        ->assertOk()
        ->assertSee('Süper Mağaza')
        ->assertSee('Mağaza Özel Ürünü');
});

test('hafta 5: misafir sepeti kullanici giris yaptiginda kullanici sepetine birlesir', function ()
{
    $user = User::factory()->create();
    $variant1 = ProductVariant::factory()->create(['stock' => 20]);
    $variant2 = ProductVariant::factory()->create(['stock' => 20]);

    $guestSessionId = 'guest-session-12345';
    $guestCart = Cart::create(['session_id' => $guestSessionId, 'user_id' => null]);
    $guestCart->items()->create(['variant_id' => $variant1->id, 'quantity' => 2]);

    $userCart = Cart::create(['user_id' => $user->id, 'session_id' => 'old-session']);
    $userCart->items()->create(['variant_id' => $variant2->id, 'quantity' => 1]);

    $mergedCart = app(MergeGuestCartAction::class)->execute($user, $guestSessionId);

    expect($mergedCart->items()->count())->toBe(2)
        ->and(Cart::where('session_id', $guestSessionId)->whereNull('user_id')->exists())->toBeFalse();
});

test('hafta 5: musteri adres ekleyebilir ve favori urunlerini yonetebilir', function ()
{
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)
        ->post('/hesabim/adresler', [
            'title' => 'Merkez Ofis',
            'city' => 'Ankara',
            'district' => 'Çankaya',
            'full_address' => 'Atatürk Bulvarı No: 100',
            'is_default' => true,
        ])
        ->assertRedirect();

    expect($user->addresses()->count())->toBe(1);

    $this->actingAs($user)
        ->post("/hesabim/favoriler/{$product->id}")
        ->assertRedirect();

    expect(Favorite::where('user_id', $user->id)->where('product_id', $product->id)->exists())->toBeTrue();
});

test('hafta 6: 2 farkli saticidan urun iceren sepet odenince 1 ana siparis ve 2 alt siparis olusur', function ()
{
    Mail::fake();

    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);

    $seller1 = Seller::factory()->create();
    $seller2 = Seller::factory()->create();

    $product1 = Product::factory()->create(['seller_id' => $seller1->id, 'name' => 'Birinci Satıcı Ürünü']);
    $product2 = Product::factory()->create(['seller_id' => $seller2->id, 'name' => 'İkinci Satıcı Ürünü']);

    $variant1 = ProductVariant::factory()->create(['product_id' => $product1->id, 'price' => '300.00', 'discount_price' => null, 'stock' => 10]);
    $variant2 = ProductVariant::factory()->create(['product_id' => $product2->id, 'price' => '450.00', 'discount_price' => null, 'stock' => 8]);

    $cartService = app(CalculateCartTotalsService::class);
    $cart = $cartService->resolveCart($user);
    $cartService->addItem($cart, $variant1, 1);
    $cartService->addItem($cart, $variant2, 2);

    $order = app(CreateOrderAction::class)->execute($user, $cart, $address, [
        'idempotency_key' => 'idem-split-order-test',
    ]);

    expect(Order::count())->toBe(1)
        ->and($order->subOrders()->count())->toBe(2)
        ->and($variant1->fresh()->stock)->toBe(9)
        ->and($variant2->fresh()->stock)->toBe(6)
        ->and($cart->fresh()->items()->count())->toBe(0);

    Mail::assertQueued(OrderConfirmedMail::class);
});

test('hafta 6: son 1 stoklu urunu iki kullanici ayni anda alamaz', function ()
{
    Mail::fake();

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $address1 = Address::factory()->create(['user_id' => $user1->id]);
    $address2 = Address::factory()->create(['user_id' => $user2->id]);

    $variant = ProductVariant::factory()->create([
        'price' => '500.00',
        'discount_price' => null,
        'stock' => 1,
    ]);

    $cartService = app(CalculateCartTotalsService::class);
    $cart1 = $cartService->resolveCart($user1, 'sess-1');
    $cart2 = $cartService->resolveCart($user2, 'sess-2');

    $cartService->addItem($cart1, $variant, 1);
    $cartService->addItem($cart2, $variant, 1);

    $action = app(CreateOrderAction::class);

    $firstOrder = $action->execute($user1, $cart1, $address1, ['idempotency_key' => 'race-user-1']);

    expect($firstOrder->payment_status)->toBe(PaymentStatus::Paid)
        ->and($variant->fresh()->stock)->toBe(0);

    expect(fn () => $action->execute($user2, $cart2, $address2, ['idempotency_key' => 'race-user-2']))
        ->toThrow(RuntimeException::class);

    expect(Order::count())->toBe(1)
        ->and($variant->fresh()->stock)->toBe(0);
});

test('hafta 6: ayni idempotency anahtari ile iki kez odeme gonderilirse tek siparis olusur', function ()
{
    Mail::fake();

    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $variant = ProductVariant::factory()->create(['stock' => 10, 'price' => '200.00', 'discount_price' => null]);

    $cartService = app(CalculateCartTotalsService::class);
    $cart = $cartService->resolveCart($user);
    $cartService->addItem($cart, $variant, 1);

    $action = app(CreateOrderAction::class);
    $orderA = $action->execute($user, $cart, $address, ['idempotency_key' => 'same-key-999']);
    $orderB = $action->execute($user, $cart, $address, ['idempotency_key' => 'same-key-999']);

    expect($orderA->id)->toBe($orderB->id)
        ->and(Order::count())->toBe(1)
        ->and($variant->fresh()->stock)->toBe(9);
});

test('hafta 6: satici sonradan urun fiyatini degistirse bile eski siparisin snapshot fiyati bozulmaz', function ()
{
    Mail::fake();

    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create(['name' => 'İlk Ürün Adı']);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => '350.00',
        'discount_price' => null,
        'stock' => 10,
    ]);

    $cartService = app(CalculateCartTotalsService::class);
    $cart = $cartService->resolveCart($user);
    $cartService->addItem($cart, $variant, 1);

    $order = app(CreateOrderAction::class)->execute($user, $cart, $address);

    $product->update(['name' => 'Değişen Ürün Adı']);
    $variant->update(['price' => '999.00']);

    $orderItem = $order->subOrders()->first()->items()->first();

    expect($orderItem->product_name)->toBe('İlk Ürün Adı')
        ->and($orderItem->price)->toBe('350.00');
});

test('hafta 6: odeme basarisiz oldugunda hicbir kayit olusmaz ve stok dusmez', function ()
{
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $variant = ProductVariant::factory()->create(['stock' => 5, 'price' => '400.00', 'discount_price' => null]);

    $cartService = app(CalculateCartTotalsService::class);
    $cart = $cartService->resolveCart($user);
    $cartService->addItem($cart, $variant, 2);

    expect(fn () => app(CreateOrderAction::class)->execute($user, $cart, $address, [
        'simulate_failure' => true,
    ]))->toThrow(RuntimeException::class);

    expect(Order::count())->toBe(0)
        ->and($variant->fresh()->stock)->toBe(5)
        ->and($cart->fresh()->items()->count())->toBe(1);
});

test('hafta 7: alt siparis durum gecisi gecerli ise guncellenir ve musteriye bildirim gider', function ()
{
    Notification::fake();

    $subOrder = SubOrder::factory()->create([
        'status' => SubOrderStatus::PendingApproval,
    ]);

    $updated = app(TransitionSubOrderStatusAction::class)->execute(
        $subOrder,
        SubOrderStatus::Preparing
    );

    expect($updated->status)->toBe(SubOrderStatus::Preparing);
    Notification::assertSentTo($subOrder->order->user, SubOrderStatusChangedNotification::class);
});

test('hafta 7: gecersiz durum gecisi teslim edildi durumundan hazirlaniyor durumuna engellenir', function ()
{
    $subOrder = SubOrder::factory()->create([
        'status' => SubOrderStatus::Delivered,
    ]);

    expect(fn () => app(TransitionSubOrderStatusAction::class)->execute($subOrder, SubOrderStatus::Preparing))
        ->toThrow(InvalidArgumentException::class);
});

test('hafta 7: urunu satin almamis veya teslim almamis biri yorum yazamaz', function ()
{
    $otherUser = User::factory()->create();
    $subOrder = SubOrder::factory()->create(['status' => SubOrderStatus::Preparing]);
    $orderItem = OrderItem::factory()->create(['sub_order_id' => $subOrder->id]);

    expect(fn () => app(PostOrderInteractionAction::class)->createReview(
        $otherUser,
        $orderItem,
        5,
        'Yetkisiz yorum'
    ))->toThrow(InvalidArgumentException::class);
});

test('hafta 7: teslim edilmis urun icin musteri 14 gun icinde iade talebi acabilir', function ()
{
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);
    $subOrder = SubOrder::factory()->create([
        'order_id' => $order->id,
        'status' => SubOrderStatus::Delivered,
    ]);
    $orderItem = OrderItem::factory()->create([
        'sub_order_id' => $subOrder->id,
        'price' => '300.00',
        'quantity' => 1,
    ]);

    $returnRequest = app(PostOrderInteractionAction::class)->createReturnRequest(
        $user,
        $orderItem,
        'Ürün beklediğim gibi çıkmadı.'
    );

    expect($returnRequest->status)->toBe(ReturnStatus::Pending)
        ->and($returnRequest->refund_amount)->toBe('300.00');
});

test('hafta 8: admin panelindeki toplam ciro ile veritabanindaki odenmis siparislerin toplami birebir tutar', function ()
{
    Order::factory()->create(['total' => '450.00', 'payment_status' => PaymentStatus::Paid]);
    Order::factory()->create(['total' => '550.00', 'payment_status' => PaymentStatus::Paid]);
    Order::factory()->create(['total' => '999.00', 'payment_status' => PaymentStatus::Failed]);

    $metrics = app(ReportService::class)->getAdminMetrics();
    $dbSum = number_format((float) Order::where('payment_status', PaymentStatus::Paid->value)->sum('total'), 2, '.', '');

    expect($metrics['total_revenue'])->toBe('1000.00')
        ->and($metrics['total_revenue'])->toBe($dbSum);
});

test('hafta 8: admin satici basvurusunu onaylayinca saticiya eposta gonderilir ve seller rolu atanir', function ()
{
    Mail::fake();

    $seller = Seller::factory()->create(['status' => SellerStatus::Pending]);
    app(SellerManagementAction::class)->approve($seller);

    expect($seller->fresh()->status)->toBe(SellerStatus::Approved)
        ->and($seller->user->fresh()->hasRole('seller'))->toBeTrue();

    Mail::assertQueued(SellerApprovedMail::class);
});

test('hafta 8: admin siparisleri csv olarak disa aktarabilir', function ()
{
    Role::firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Order::factory()->create(['order_number' => 'LM-CSVTEST1']);

    $this->actingAs($admin)
        ->get('/admin/siparisler/disa-aktar')
        ->assertOk()
        ->assertSee('LM-CSVTEST1');
});

test('hafta 9: ana sayfa 15ten az veritabani sorgusu ile acilir', function ()
{
    $this->seed(DatabaseSeeder::class);

    $this->get('/');

    DB::enableQueryLog();
    DB::flushQueryLog();

    $this->get('/')->assertOk();

    $queryCount = count(DB::getQueryLog());
    expect($queryCount)->toBeLessThan(15);
});

test('hafta 9: sanctum rest api ile giris yapilip sepete urun eklenip siparis listesi cekilebilir', function ()
{
    $user = User::factory()->create([
        'email' => 'api@laramart.test',
        'password' => bcrypt('password'),
    ]);
    $variant = ProductVariant::factory()->create(['stock' => 15, 'price' => '250.00', 'discount_price' => null]);
    Order::factory()->create(['user_id' => $user->id, 'order_number' => 'LM-APIORDER']);

    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'email' => 'api@laramart.test',
        'password' => 'password',
    ])->assertOk();

    $token = $loginResponse->json('token');
    expect($token)->not->toBeEmpty();

    $this->withToken($token)
        ->postJson('/api/v1/cart', [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ])
        ->assertCreated()
        ->assertJsonPath('data.subtotal', '500.00');

    $this->withToken($token)
        ->getJson('/api/v1/orders')
        ->assertOk()
        ->assertJsonFragment(['order_number' => 'LM-APIORDER']);
});

test('hafta 9: rest api urun ve kategori uc noktalari dogru calisir', function ()
{
    $category = Category::factory()->create(['name' => 'API Kategori']);
    $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'API Ürün', 'slug' => 'api-urun']);
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonFragment(['name' => 'API Kategori']);

    $this->getJson('/api/v1/products')
        ->assertOk()
        ->assertJsonFragment(['name' => 'API Ürün']);

    $this->getJson('/api/v1/products/api-urun')
        ->assertOk()
        ->assertJsonFragment(['slug' => 'api-urun']);
});

test('hafta 10: uctan uca final kabul senaryosu musteri uye olur 2 saticidan urun alir satici kargolar admin raporu gorur', function ()
{
    Mail::fake();
    Notification::fake();

    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'seller']);
    Role::firstOrCreate(['name' => 'customer']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $sellerUser1 = User::factory()->create();
    $sellerUser1->assignRole('seller');
    $seller1 = Seller::factory()->create(['user_id' => $sellerUser1->id, 'status' => SellerStatus::Approved]);

    $sellerUser2 = User::factory()->create();
    $sellerUser2->assignRole('seller');
    $seller2 = Seller::factory()->create(['user_id' => $sellerUser2->id, 'status' => SellerStatus::Approved]);

    $product1 = Product::factory()->create(['seller_id' => $seller1->id]);
    $variant1 = ProductVariant::factory()->create(['product_id' => $product1->id, 'price' => '400.00', 'discount_price' => null, 'stock' => 10]);

    $product2 = Product::factory()->create(['seller_id' => $seller2->id]);
    $variant2 = ProductVariant::factory()->create(['product_id' => $product2->id, 'price' => '600.00', 'discount_price' => null, 'stock' => 10]);

    $this->post('/kayit', [
        'name' => 'Final Demo Müşteri',
        'email' => 'final@laramart.test',
        'phone' => '05551112233',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ])->assertRedirect('/hesabim');

    $customer = User::where('email', 'final@laramart.test')->first();
    $address = Address::factory()->create(['user_id' => $customer->id]);

    $this->actingAs($customer)->post('/sepet/ekle', ['variant_id' => $variant1->id, 'quantity' => 1]);
    $this->actingAs($customer)->post('/sepet/ekle', ['variant_id' => $variant2->id, 'quantity' => 1]);

    $this->actingAs($customer)->post('/odeme', [
        'address_id' => $address->id,
        'terms_accepted' => '1',
        'idempotency_key' => 'final-demo-key-1',
    ])->assertRedirect();

    $order = Order::where('user_id', $customer->id)->first();
    expect($order)->not->toBeNull()
        ->and($order->subOrders()->count())->toBe(2);

    $subOrder1 = $order->subOrders()->where('seller_id', $seller1->id)->first();

    $this->actingAs($sellerUser1)->patch("/satici/alt-siparisler/{$subOrder1->id}/durum", [
        'status' => SubOrderStatus::Preparing->value,
    ])->assertRedirect();

    $this->actingAs($sellerUser1)->patch("/satici/alt-siparisler/{$subOrder1->id}/durum", [
        'status' => SubOrderStatus::Shipped->value,
        'cargo_company' => 'Aras Kargo',
        'tracking_number' => 'ARAS123456',
    ])->assertRedirect();

    expect($subOrder1->fresh()->status)->toBe(SubOrderStatus::Shipped)
        ->and($subOrder1->fresh()->tracking_number)->toBe('ARAS123456');

    $this->actingAs($admin)->get('/admin')
        ->assertOk()
        ->assertSee('1000.00 TL');
});

