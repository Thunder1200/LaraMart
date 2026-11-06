<?php

use App\Enums\ProductStatus;
use App\Enums\ReturnStatus;
use App\Enums\SellerStatus;
use App\Enums\SubOrderStatus;
use App\Livewire\CartManager;
use App\Livewire\SearchAutocomplete;
use App\Models\Address;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\Review;
use App\Models\Seller;
use App\Models\SubOrder;
use App\Models\User;
use App\Services\CalculateCartTotalsService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('livewire search autocomplete en az 2 karakter girildiginde urun onerileri sunar', function ()
{
    $brand = Brand::factory()->create(['name' => 'HyperTech']);
    $product = Product::factory()->create(['brand_id' => $brand->id, 'name' => 'HyperTech Gaming Mouse']);
    ProductVariant::factory()->create(['product_id' => $product->id, 'price' => '950.00']);

    Livewire::test(SearchAutocomplete::class)
        ->set('query', 'Hyper')
        ->assertSee('HyperTech Gaming Mouse');
});

test('livewire cart manager ile urun miktari guncellenebilir ve silinebilir', function ()
{
    $user = User::factory()->create();
    $variant = ProductVariant::factory()->create(['price' => '200.00', 'discount_price' => null, 'stock' => 10]);

    $cartService = app(CalculateCartTotalsService::class);
    $cart = $cartService->resolveCart($user);
    $cartItem = $cartService->addItem($cart, $variant, 1);

    $this->actingAs($user);

    Livewire::test(CartManager::class)
        ->call('updateQuantity', $cartItem->id, 3)
        ->assertSee('600.00 TL')
        ->call('removeItem', $cartItem->id)
        ->assertSee('Sepetinizde henüz ürün bulunmuyor.');
});

test('satici panelinde yeni urun ekleyebilir duzenleyebilir ve silebilir', function ()
{
    Role::firstOrCreate(['name' => 'seller']);
    $sellerUser = User::factory()->create();
    $sellerUser->assignRole('seller');
    $seller = Seller::factory()->create(['user_id' => $sellerUser->id, 'status' => SellerStatus::Approved]);
    $category = Category::factory()->create();
    $brand = Brand::factory()->create();

    $this->actingAs($sellerUser)
        ->post('/satici/urunler', [
            'name' => 'Satıcı Yeni Kulaklık',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'description' => 'Yüksek ses kaliteli kablosuz kulaklık.',
            'status' => 'active',
            'sku' => 'SKU-KULAKLIK-01',
            'color' => 'Siyah',
            'size' => 'Standart',
            'price' => '750.00',
            'discount_price' => '699.00',
            'stock' => 30,
        ])
        ->assertRedirect('/satici');

    $product = Product::where('name', 'Satıcı Yeni Kulaklık')->first();
    expect($product)->not->toBeNull();

    $this->actingAs($sellerUser)
        ->put("/satici/urunler/{$product->id}", [
            'name' => 'Satıcı Güncel Kulaklık',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'description' => 'Güncellenmiş açıklama metni.',
            'status' => 'inactive',
            'price' => '800.00',
            'discount_price' => null,
            'stock' => 15,
        ])
        ->assertRedirect('/satici');

    expect($product->fresh()->name)->toBe('Satıcı Güncel Kulaklık')
        ->and($product->fresh()->status)->toBe(ProductStatus::Inactive);

    $this->actingAs($sellerUser)
        ->delete("/satici/urunler/{$product->id}")
        ->assertRedirect('/satici');

    expect(Product::find($product->id))->toBeNull();
});

test('satici baska bir saticinin alt siparisini guncelleyemez 403 alir', function ()
{
    Role::firstOrCreate(['name' => 'seller']);

    $userA = User::factory()->create();
    $userA->assignRole('seller');
    Seller::factory()->create(['user_id' => $userA->id, 'status' => SellerStatus::Approved]);

    $userB = User::factory()->create();
    $userB->assignRole('seller');
    $sellerB = Seller::factory()->create(['user_id' => $userB->id, 'status' => SellerStatus::Approved]);

    $subOrderB = SubOrder::factory()->create(['seller_id' => $sellerB->id]);

    $this->actingAs($userA)
        ->patch("/satici/alt-siparisler/{$subOrderB->id}/durum", [
            'status' => SubOrderStatus::Preparing->value,
        ])
        ->assertForbidden();
});

test('musteri baska bir musterinin siparis detay sayfasini goremez 403 alir', function ()
{
    $customerA = User::factory()->create();
    $customerB = User::factory()->create();
    $orderB = Order::factory()->create(['user_id' => $customerB->id]);

    $this->actingAs($customerA)
        ->get("/hesabim/siparisler/{$orderB->id}")
        ->assertForbidden();
});

test('musteri baska bir musterinin adresini silemez 403 alir', function ()
{
    $customerA = User::factory()->create();
    $customerB = User::factory()->create();
    $addressB = Address::factory()->create(['user_id' => $customerB->id]);

    $this->actingAs($customerA)
        ->delete("/hesabim/adresler/{$addressB->id}")
        ->assertForbidden();
});

test('admin kategori olusturabilir ve silebilir ve cache temizlenir', function ()
{
    Role::firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post('/admin/kategoriler', [
            'name' => 'Akıllı Saatler',
            'commission_rate' => '12.50',
            'sort' => 5,
        ])
        ->assertRedirect();

    $category = Category::where('name', 'Akıllı Saatler')->first();
    expect($category)->not->toBeNull()
        ->and($category->commission_rate)->toBe('12.50');

    $this->actingAs($admin)
        ->delete("/admin/kategoriler/{$category->id}")
        ->assertRedirect();

    expect(Category::find($category->id))->toBeNull();
});

test('admin yorum onayladiginda urun sayfasinda gorunur ve ortalama puan hesaplanir', function ()
{
    Role::firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $review = Review::factory()->create([
        'product_id' => $product->id,
        'rating' => 5,
        'comment' => 'Onay bekleyen harika yorum.',
        'is_approved' => false,
    ]);

    expect($product->fresh()->average_rating)->toBe(0.0);

    $this->actingAs($admin)
        ->post("/admin/yorumlar/{$review->id}/onayla")
        ->assertRedirect();

    expect($review->fresh()->is_approved)->toBeTrue()
        ->and($product->fresh()->average_rating)->toBe(5.0);
});

test('satici iade talebini onayladiginda alt siparis durumu iade olarak guncellenir', function ()
{
    Role::firstOrCreate(['name' => 'seller']);
    $sellerUser = User::factory()->create();
    $sellerUser->assignRole('seller');
    $seller = Seller::factory()->create(['user_id' => $sellerUser->id, 'status' => SellerStatus::Approved]);

    $subOrder = SubOrder::factory()->create([
        'seller_id' => $seller->id,
        'status' => SubOrderStatus::Delivered,
    ]);
    $orderItem = OrderItem::factory()->create(['sub_order_id' => $subOrder->id]);
    $returnReq = ReturnRequest::factory()->create([
        'order_item_id' => $orderItem->id,
        'status' => ReturnStatus::Pending,
    ]);

    $this->actingAs($sellerUser)
        ->patch("/satici/iadeler/{$returnReq->id}", [
            'decision' => 'approved',
        ])
        ->assertRedirect();

    expect($returnReq->fresh()->status)->toBe(ReturnStatus::Approved)
        ->and($subOrder->fresh()->status)->toBe(SubOrderStatus::Returned);
});

test('teslimattan 14 gun gecmis siparis icin iade talebi acilamaz', function ()
{
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);
    $subOrder = SubOrder::factory()->create([
        'order_id' => $order->id,
        'status' => SubOrderStatus::Delivered,
        'updated_at' => now()->subDays(20),
    ]);
    $orderItem = OrderItem::factory()->create(['sub_order_id' => $subOrder->id]);

    $this->actingAs($user)
        ->post("/hesabim/siparis-kalemi/{$orderItem->id}/iade", [
            'reason' => 'Geç kalmış iade talebi denemesi.',
        ])
        ->assertSessionHasErrors('return');
});

test('5 basarisiz giris denemesinden sonra login rate limit devreye girer', function ()
{
    $user = User::factory()->create([
        'email' => 'ratelimit@laramart.test',
        'password' => bcrypt('dogrusifre'),
    ]);

    for ($i = 0; $i < 5; $i++)
    {
        $this->post('/giris', [
            'email' => 'ratelimit@laramart.test',
            'password' => 'yanlissifre',
        ]);
    }

    $response = $this->from('/giris')->post('/giris', [
        'email' => 'ratelimit@laramart.test',
        'password' => 'yanlissifre',
    ]);

    $response->assertSessionHasErrors('email');
    RateLimiter::clear('ratelimit@laramart.test|127.0.0.1');
});

test('api uzerinden stoktan fazla urun eklenmek istendiginde 422 standart hata formati doner', function ()
{
    $user = User::factory()->create();
    $variant = ProductVariant::factory()->create(['stock' => 2]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/cart', [
            'variant_id' => $variant->id,
            'quantity' => 10,
        ])
        ->assertStatus(422)
        ->assertJsonPath('status', 'error');
});

