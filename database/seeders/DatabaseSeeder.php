<?php

namespace Database\Seeders;

use App\Enums\CouponType;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Enums\SellerStatus;
use App\Enums\SubOrderStatus;
use App\Models\Address;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Seller;
use App\Models\SellerPayout;
use App\Models\SubOrder;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $sellerRole = Role::firstOrCreate(['name' => 'seller']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        $admin = User::factory()->create([
            'name' => 'LaraMart Admin',
            'email' => 'admin@laramart.test',
            'phone' => '05550000001',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole($adminRole);

        $storeDefinitions = [
            ['name' => 'TeknoMerkez Resmi Satıcı', 'slug' => 'teknomerkez', 'rate' => '8.00'],
            ['name' => 'ModaVizyon Butik', 'slug' => 'modavizyon-butik', 'rate' => '15.00'],
            ['name' => 'EvДеkor Dünyası', 'slug' => 'evdekor-dunyasi', 'rate' => '12.00'],
            ['name' => 'SporAktif Mağazası', 'slug' => 'sporaktif-magazasi', 'rate' => '10.00'],
            ['name' => 'KozmetikPlus Store', 'slug' => 'kozmetikplus-store', 'rate' => '14.00'],
        ];

        $sellers = collect();

        foreach ($storeDefinitions as $index => $def)
        {
            $num = $index + 1;
            $sellerUser = User::factory()->create([
                'name' => "Satıcı {$num} ({$def['name']})",
                'email' => "satici{$num}@laramart.test",
                'phone' => '0555100000'.$num,
                'password' => Hash::make('password'),
            ]);
            $sellerUser->assignRole($sellerRole);

            $seller = Seller::factory()->create([
                'user_id' => $sellerUser->id,
                'store_name' => $def['name'],
                'slug' => $def['slug'],
                'tax_number' => '100000000'.$num,
                'iban' => 'TR00000000000000000000000'.$num,
                'status' => SellerStatus::Approved,
                'commission_rate' => $def['rate'],
            ]);

            $sellers->push($seller);
        }

        $customers = collect();

        for ($i = 1; $i <= 20; $i++)
        {
            $email = $i === 1 ? 'musteri@laramart.test' : "musteri{$i}@laramart.test";
            $customer = User::factory()->create([
                'name' => $i === 1 ? 'Ahmet Yılmaz (Demo Müşteri)' : fake()->name(),
                'email' => $email,
                'phone' => '055520000'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'password' => Hash::make('password'),
            ]);
            $customer->assignRole($customerRole);

            Address::factory()->create([
                'user_id' => $customer->id,
                'title' => 'Ev Adresim',
                'city' => 'İstanbul',
                'district' => 'Kadıköy',
                'full_address' => 'Caferağa Mah. Moda Cad. No: '.$i,
                'is_default' => true,
            ]);

            $customers->push($customer);
        }

        $rootCategoryData = [
            ['name' => 'Elektronik', 'slug' => 'elektronik', 'rate' => '8.00', 'children' => [
                ['name' => 'Telefon & Aksesuar', 'slug' => 'telefon-aksesuar', 'rate' => '8.00'],
                ['name' => 'Bilgisayar & Tablet', 'slug' => 'bilgisayar-tablet', 'rate' => '8.00'],
            ]],
            ['name' => 'Moda & Giyim', 'slug' => 'moda-giyim', 'rate' => '15.00', 'children' => [
                ['name' => 'Kadın Giyim', 'slug' => 'kadin-giyim', 'rate' => '15.00'],
                ['name' => 'Erkek Giyim', 'slug' => 'erkek-giyim', 'rate' => '15.00'],
            ]],
            ['name' => 'Ev & Yaşam', 'slug' => 'ev-yasam', 'rate' => '12.00', 'children' => [
                ['name' => 'Mobilya', 'slug' => 'mobilya', 'rate' => '12.00'],
                ['name' => 'Ev Tekstili', 'slug' => 'ev-tekstili', 'rate' => '12.00'],
            ]],
            ['name' => 'Spor & Outdoor', 'slug' => 'spor-outdoor', 'rate' => '10.00', 'children' => [
                ['name' => 'Spor Ayakkabı', 'slug' => 'spor-ayakkabi', 'rate' => '10.00'],
                ['name' => 'Fitness Kondisyon', 'slug' => 'fitness-kondisyon', 'rate' => '10.00'],
            ]],
            ['name' => 'Kozmetik & Kişisel Bakım', 'slug' => 'kozmetik-kisisel-bakim', 'rate' => '14.00', 'children' => [
                ['name' => 'Parfüm & Deodorant', 'slug' => 'parfum-deodorant', 'rate' => '14.00'],
                ['name' => 'Cilt Bakımı', 'slug' => 'cilt-bakimi', 'rate' => '14.00'],
            ]],
        ];

        $allCategories = collect();
        $sortOrder = 1;

        foreach ($rootCategoryData as $rootItem)
        {
            $parent = Category::create([
                'parent_id' => null,
                'name' => $rootItem['name'],
                'slug' => $rootItem['slug'],
                'commission_rate' => $rootItem['rate'],
                'sort' => $sortOrder++,
            ]);
            $allCategories->push($parent);

            foreach ($rootItem['children'] as $childItem)
            {
                $child = Category::create([
                    'parent_id' => $parent->id,
                    'name' => $childItem['name'],
                    'slug' => $childItem['slug'],
                    'commission_rate' => $childItem['rate'],
                    'sort' => $sortOrder++,
                ]);
                $allCategories->push($child);
            }
        }

        $brandNames = ['NovaTech', 'UrbanStyle', 'ZenHome', 'AeroSport', 'PureGlow', 'ApexPro', 'VelvetLine', 'EcoSmart'];
        $brands = collect();

        foreach ($brandNames as $brandName)
        {
            $brands->push(Brand::create([
                'name' => $brandName,
                'slug' => Str::slug($brandName),
                'logo' => 'https://placehold.co/200x80/e2e8f0/1e293b?text='.urlencode($brandName),
            ]));
        }

        $colors = ['Siyah', 'Beyaz', 'Lacivert', 'Gri', 'Kırmızı', 'Yeşil'];
        $sizes = ['S', 'M', 'L', 'XL'];

        $products = collect();

        for ($i = 1; $i <= 100; $i++)
        {
            $seller = $sellers[($i - 1) % $sellers->count()];
            $category = $allCategories[($i - 1) % $allCategories->count()];
            $brand = $brands[($i - 1) % $brands->count()];

            $productName = "{$brand->name} {$category->name} Özel Seri #{$i}";

            $product = Product::create([
                'seller_id' => $seller->id,
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'name' => $productName,
                'slug' => Str::slug($productName).'-'.$i,
                'description' => "{$productName} üstün malzeme kalitesi, hızlı kargo ve LaraMart güvencesiyle {$seller->store_name} mağazasında satışta.",
                'status' => ProductStatus::Active,
            ]);

            $basePrice = round(150 + ($i * 22.50), 2);

            for ($v = 0; $v < 2; $v++)
            {
                $variantPrice = $basePrice + ($v * 50);
                $discountPrice = ($i % 3 === 0) ? round($variantPrice * 0.85, 2) : null;

                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => sprintf('LM-%03d-V%d', $i, $v + 1),
                    'color' => $colors[($i + $v) % count($colors)],
                    'size' => $sizes[($i + $v) % count($sizes)],
                    'price' => number_format($variantPrice, 2, '.', ''),
                    'discount_price' => $discountPrice !== null ? number_format($discountPrice, 2, '.', '') : null,
                    'stock' => 25 + ($i % 20),
                ]);
            }

            $products->push($product);
        }

        Coupon::create([
            'code' => 'MERHABA10',
            'type' => CouponType::Percentage,
            'value' => '10.00',
            'min_total' => '200.00',
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->addMonths(3),
            'usage_limit' => 500,
            'used_count' => 0,
        ]);

        Coupon::create([
            'code' => 'INDIRIM100',
            'type' => CouponType::Fixed,
            'value' => '100.00',
            'min_total' => '500.00',
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->addMonths(3),
            'usage_limit' => 200,
            'used_count' => 0,
        ]);

        Coupon::create([
            'code' => 'ESKIKUPON',
            'type' => CouponType::Percentage,
            'value' => '20.00',
            'min_total' => '100.00',
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subDays(2),
            'usage_limit' => 50,
            'used_count' => 0,
        ]);

        Banner::create([
            'title' => 'LaraMart Büyük Bahar İndirimi - %40 Varan Fırsatlar',
            'image' => 'https://placehold.co/1200x400/0f172a/38bdf8?text=LaraMart+Buyuk+Indirim',
            'url' => '/kategori/elektronik',
            'sort' => 1,
            'is_active' => true,
        ]);

        Banner::create([
            'title' => '300 TL Üzeri Alışverişlerde Kargo Bedava',
            'image' => 'https://placehold.co/1200x400/1e1b4b/a78bfa?text=300+TL+Ustu+Ucretsiz+Kargo',
            'url' => '/kategori/moda-giyim',
            'sort' => 2,
            'is_active' => true,
        ]);

        $demoCustomer = $customers->first();
        $firstVariant = $products[0]->variants()->first();
        $secondVariant = $products[1]->variants()->first();

        Favorite::create([
            'user_id' => $demoCustomer->id,
            'product_id' => $products[0]->id,
        ]);

        $order = Order::create([
            'user_id' => $demoCustomer->id,
            'order_number' => 'LM-DEMO1001',
            'idempotency_key' => (string) Str::uuid(),
            'address' => $demoCustomer->addresses()->first()->toSnapshot(),
            'subtotal' => '800.00',
            'discount' => '0.00',
            'shipping' => '0.00',
            'total' => '800.00',
            'payment_status' => PaymentStatus::Paid,
            'coupon_code' => null,
        ]);

        $subOrder1 = SubOrder::create([
            'order_id' => $order->id,
            'seller_id' => $products[0]->seller_id,
            'status' => SubOrderStatus::Delivered,
            'cargo_company' => 'Yurtiçi Kargo',
            'tracking_number' => 'YK9988776655',
            'subtotal' => '400.00',
            'shipping' => '0.00',
            'commission_amount' => '32.00',
        ]);

        $orderItem1 = OrderItem::create([
            'sub_order_id' => $subOrder1->id,
            'variant_id' => $firstVariant->id,
            'product_name' => $products[0]->name,
            'price' => '400.00',
            'quantity' => 1,
        ]);

        $subOrder2 = SubOrder::create([
            'order_id' => $order->id,
            'seller_id' => $products[1]->seller_id,
            'status' => SubOrderStatus::Preparing,
            'cargo_company' => null,
            'tracking_number' => null,
            'subtotal' => '400.00',
            'shipping' => '0.00',
            'commission_amount' => '60.00',
        ]);

        OrderItem::create([
            'sub_order_id' => $subOrder2->id,
            'variant_id' => $secondVariant->id,
            'product_name' => $products[1]->name,
            'price' => '400.00',
            'quantity' => 1,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'iyzico',
            'transaction_id' => 'IYZ-DEMO-1001',
            'amount' => '800.00',
            'status' => PaymentStatus::Paid,
            'raw_response' => ['status' => 'success', 'paymentId' => 'IYZ-DEMO-1001'],
        ]);

        Review::create([
            'user_id' => $demoCustomer->id,
            'product_id' => $products[0]->id,
            'order_item_id' => $orderItem1->id,
            'rating' => 5,
            'comment' => 'Ürün çok hızlı kargolandı ve paketleme harikaydı, kesinlikle tavsiye ederim.',
            'is_approved' => true,
        ]);

        SellerPayout::create([
            'seller_id' => $sellers[0]->id,
            'period' => now()->format('Y-m'),
            'gross' => '400.00',
            'commission' => '32.00',
            'net' => '368.00',
            'paid_at' => now(),
        ]);
    }
}
