<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Hafta 1 kabul kriteri:
     * 1 admin, 5 satıcı, 20 müşteri, 15 kategori, 100 ürün (+ varyant, marka, adres, kupon, banner).
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $sellerRole = Role::firstOrCreate(['name' => 'seller']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        // 1 Admin
        $admin = User::factory()->create([
            'name' => 'LaraMart Yönetici',
            'email' => 'admin@laramart.test',
            'phone' => '05000000001',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole($adminRole);

        // 5 Satıcı (Kullanıcı + Mağaza)
        $storeDefinitions = [
            ['name' => 'TechZone Elektronik', 'tax' => '1000000001'],
            ['name' => 'ModaEvim Giyim', 'tax' => '1000000002'],
            ['name' => 'EvTrend Yaşam', 'tax' => '1000000003'],
            ['name' => 'SporPlus Outdoor', 'tax' => '1000000004'],
            ['name' => 'KozmoDünya Bakım', 'tax' => '1000000005'],
        ];

        $sellers = collect();

        foreach ($storeDefinitions as $index => $store) {
            $sellerNumber = $index + 1;
            $sellerUser = User::factory()->create([
                'name' => $store['name'].' Yetkilisi',
                'email' => "satici{$sellerNumber}@laramart.test",
                'phone' => '0530000000'.$sellerNumber,
                'password' => Hash::make('password'),
            ]);
            $sellerUser->assignRole($sellerRole);

            $sellers->push(Seller::create([
                'user_id' => $sellerUser->id,
                'store_name' => $store['name'],
                'slug' => Str::slug($store['name']),
                'tax_number' => $store['tax'],
                'iban' => 'TR'.str_pad((string) (1000000000000000 + $sellerNumber), 24, '0', STR_PAD_LEFT),
                'status' => 'approved',
                'commission_rate' => null,
            ]));
        }

        // 20 Müşteri (+ her müşteriye 1 varsayılan adres)
        for ($i = 1; $i <= 20; $i++) {
            $customer = User::factory()->create([
                'email' => "musteri{$i}@laramart.test",
                'password' => Hash::make('password'),
            ]);
            $customer->assignRole($customerRole);

            Address::factory()->create([
                'user_id' => $customer->id,
                'title' => 'Ev Adresim',
                'is_default' => true,
            ]);
        }

        // 15 Kategori (5 Ana Kategori + 10 Alt Kategori = 15)
        $categoryTree = [
            ['name' => 'Elektronik', 'commission' => 8.00, 'children' => ['Telefon & Aksesuar', 'Bilgisayar & Tablet']],
            ['name' => 'Giyim & Moda', 'commission' => 15.00, 'children' => ['Kadın Giyim', 'Erkek Giyim']],
            ['name' => 'Ev & Yaşam', 'commission' => 12.00, 'children' => ['Mutfak Gereçleri', 'Aydınlatma & Dekor']],
            ['name' => 'Spor & Outdoor', 'commission' => 10.00, 'children' => ['Fitness & Koşu', 'Kamp Malzemeleri']],
            ['name' => 'Kozmetik & Bakım', 'commission' => 14.00, 'children' => ['Cilt Bakımı', 'Parfüm']],
        ];

        $leafCategories = collect();
        $sort = 1;

        foreach ($categoryTree as $root) {
            $parent = Category::create([
                'parent_id' => null,
                'name' => $root['name'],
                'slug' => Str::slug($root['name']),
                'commission_rate' => $root['commission'],
                'sort' => $sort++,
            ]);

            foreach ($root['children'] as $childName) {
                $leafCategories->push(Category::create([
                    'parent_id' => $parent->id,
                    'name' => $childName,
                    'slug' => Str::slug($childName),
                    'commission_rate' => $root['commission'],
                    'sort' => $sort++,
                ]));
            }
        }

        // 8 Örnek Marka
        $brandNames = ['NovaTech', 'UrbanWear', 'HomeCraft', 'PeakFit', 'PureGlow', 'AeroSound', 'VeloStyle', 'ZenLiving'];
        $brands = collect($brandNames)->map(fn (string $name) => Brand::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'logo' => null,
        ]));

        // 100 Ürün (+ her ürüne 2-3 varyant)
        for ($i = 1; $i <= 100; $i++) {
            $product = Product::factory()->create([
                'seller_id' => $sellers->random()->id,
                'category_id' => $leafCategories->random()->id,
                'brand_id' => $brands->random()->id,
                'status' => 'active',
            ]);

            ProductVariant::factory()->count(fake()->numberBetween(2, 3))->create([
                'product_id' => $product->id,
            ]);
        }

        // Örnek Kuponlar
        Coupon::create([
            'code' => 'HOSGELDIN10',
            'type' => 'percentage',
            'value' => 10.00,
            'min_total' => 250.00,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonths(3),
            'usage_limit' => 500,
            'used_count' => 0,
        ]);

        Coupon::create([
            'code' => 'INDIRIM100',
            'type' => 'fixed',
            'value' => 100.00,
            'min_total' => 750.00,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonths(2),
            'usage_limit' => 200,
            'used_count' => 0,
        ]);

        // Örnek Ana Sayfa Bannerları
        Banner::create([
            'title' => 'Elektronikte Süper İndirimler',
            'image' => 'banners/elektronik-kampanya.jpg',
            'url' => '/kategori/elektronik',
            'sort' => 1,
            'is_active' => true,
        ]);

        Banner::create([
            'title' => 'Yeni Sezon Moda Koleksiyonu',
            'image' => 'banners/moda-sezon.jpg',
            'url' => '/kategori/giyim-moda',
            'sort' => 2,
            'is_active' => true,
        ]);

        Banner::create([
            'title' => '300 TL Üzeri Siparişlerde Ücretsiz Kargo',
            'image' => 'banners/ucretsiz-kargo.jpg',
            'url' => '/',
            'sort' => 3,
            'is_active' => true,
        ]);
    }
}
