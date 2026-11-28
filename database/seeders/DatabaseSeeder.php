<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Seller;
use App\Models\SubOrder;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * 1 admin, 5 satıcı, 20 müşteri, 15 kategori,
     * 100 gerçekçi & teknik özellikli ürün (+ varyant, görsel anahtarı, onaylı değerlendirmeler/puanlar, kupon, banner).
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

        // 5 Onaylı Satıcı
        $storeDefinitions = [
            ['key' => 'tech', 'name' => 'TechZone Elektronik', 'tax' => '1000000001'],
            ['key' => 'fashion', 'name' => 'ModaEvim Giyim', 'tax' => '1000000002'],
            ['key' => 'home', 'name' => 'EvTrend Yaşam', 'tax' => '1000000003'],
            ['key' => 'sport', 'name' => 'SporPlus Outdoor', 'tax' => '1000000004'],
            ['key' => 'beauty', 'name' => 'KozmoDünya Bakım', 'tax' => '1000000005'],
        ];

        $sellersByKey = [];
        foreach ($storeDefinitions as $index => $store) {
            $sellerNumber = $index + 1;
            $sellerUser = User::factory()->create([
                'name' => $store['name'].' Yetkilisi',
                'email' => "satici{$sellerNumber}@laramart.test",
                'phone' => '0530000000'.$sellerNumber,
                'password' => Hash::make('password'),
            ]);
            $sellerUser->assignRole($sellerRole);

            $sellersByKey[$store['key']] = Seller::create([
                'user_id' => $sellerUser->id,
                'store_name' => $store['name'],
                'slug' => Str::slug($store['name']),
                'tax_number' => $store['tax'],
                'iban' => 'TR'.str_pad((string) (1000000000000000 + $sellerNumber), 24, '0', STR_PAD_LEFT),
                'status' => 'approved',
                'commission_rate' => null,
            ]);
        }

        // 20 Müşteri (+ her müşteriye 1 varsayılan adres)
        $customers = collect();
        $customerNames = [
            'Ayşe Yılmaz', 'Mehmet Demir', 'Zeynep Kaya', 'Emre Çelik', 'Elif Şahin',
            'Burak Yıldız', 'Selin Arslan', 'Caner Aydın', 'Merve Öztürk', 'Kaan Polat',
            'Derya Koç', 'Oğuzhan Kurt', 'İrem Özdemir', 'Furkan Aksoy', 'Büşra Aslan',
            'Taha Erdoğan', 'Gizem Güneş', 'Umut Yavuz', 'Ceren Korkmaz', 'Kerem Şimşek',
        ];

        for ($i = 1; $i <= 20; $i++) {
            $customer = User::factory()->create([
                'name' => $customerNames[$i - 1],
                'email' => "musteri{$i}@laramart.test",
                'password' => Hash::make('password'),
            ]);
            $customer->assignRole($customerRole);

            Address::factory()->create([
                'user_id' => $customer->id,
                'title' => 'Ev Adresim',
                'is_default' => true,
            ]);

            $customers->push($customer);
        }

        // 15 Kategori (5 Ana + 10 Alt)
        $categoryTree = [
            ['name' => 'Elektronik', 'commission' => 8.00, 'children' => ['Telefon & Aksesuar', 'Bilgisayar & Tablet']],
            ['name' => 'Giyim & Moda', 'commission' => 15.00, 'children' => ['Kadın Giyim', 'Erkek Giyim']],
            ['name' => 'Ev & Yaşam', 'commission' => 12.00, 'children' => ['Mutfak Gereçleri', 'Aydınlatma & Dekor']],
            ['name' => 'Spor & Outdoor', 'commission' => 10.00, 'children' => ['Fitness & Koşu', 'Kamp Malzemeleri']],
            ['name' => 'Kozmetik & Bakım', 'commission' => 14.00, 'children' => ['Cilt Bakımı', 'Parfüm']],
        ];

        $categoriesBySlug = [];
        $sort = 1;

        foreach ($categoryTree as $root) {
            $parent = Category::create([
                'parent_id' => null,
                'name' => $root['name'],
                'slug' => Str::slug($root['name']),
                'commission_rate' => $root['commission'],
                'sort' => $sort++,
            ]);
            $categoriesBySlug[$parent->slug] = $parent;

            foreach ($root['children'] as $childName) {
                $child = Category::create([
                    'parent_id' => $parent->id,
                    'name' => $childName,
                    'slug' => Str::slug($childName),
                    'commission_rate' => $root['commission'],
                    'sort' => $sort++,
                ]);
                $categoriesBySlug[$child->slug] = $child;
            }
        }

        // 8 Gerçekçi Marka
        $brandNames = ['NovaTech', 'AeroSound', 'UrbanWear', 'VeloStyle', 'HomeCraft', 'ZenLiving', 'PeakFit', 'PureGlow'];
        $brandsByName = [];
        foreach ($brandNames as $name) {
            $brandsByName[$name] = Brand::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'logo' => null,
            ]);
        }

        // 100 Detaylı, Gerçekçi, Kategoriye Özel Teknik Özellikli ve Görselli Ürün
        $catalogBlueprints = $this->catalogBlueprints();

        // Gerçekçi onaylı yorumlar için müşteri bazlı örnek teslim edilmiş sipariş havuzu
        $seedOrderByCustomer = [];
        $skuCounter = 1001;
        $productIndex = 0;

        $reviewTemplates = [
            5 => [
                'Beklediğimden çok daha kaliteli çıktı, paketleme özenli ve kargo ertesi gün ulaştı.',
                'Malzeme kalitesi ve tasarımı harika, kesinlikle tavsiye ederim.',
                'Fiyatını sonuna kadar hak ediyor. Uzun süredir aradığım performansı yakaladım.',
                'Satıcı çok ilgiliydi, ürün orijinal kutusunda eksiksiz geldi. 5 yıldız!',
            ],
            4 => [
                'Genel olarak çok memnun kaldım, günlük kullanımda oldukça rahat ve şık.',
                'Kalitesi gayet güzel, sadece kargo bir gün geç geldi ama ürün kusursuz.',
                'Fiyat-performans açısından başarılı, beklentilerimi büyük ölçüde karşıladı.',
            ],
            3 => [
                'İdare eder, fiyatına göre makul bir ürün ancak daha iyi detaylar olabilirdi.',
                'Görseldekiyle uyumlu fakat teslimat süreci biraz uzun sürdü.',
            ],
            2 => [
                'Beklentimin biraz altında kaldı, yine de temel ihtiyacı görüyor.',
            ],
        ];

        // Puan dağılımı profilleri (Minimum puan filtresini zenginleştirmek için 3.0'dan 5.0'a çeşitlilik)
        $ratingProfiles = [
            [5, 5, 5, 5],       // 5.0
            [5, 5, 5, 4],       // 4.8
            [5, 5, 4, 4, 5],    // 4.6
            [5, 4, 4, 5],       // 4.5
            [4, 5, 4, 4],       // 4.3
            [4, 4, 4, 4],       // 4.0
            [4, 4, 3, 4],       // 3.8
            [4, 3, 4, 3],       // 3.5
            [3, 4, 3, 3],       // 3.3
            [3, 3, 2, 4],       // 3.0
        ];

        foreach ($catalogBlueprints as $categorySlug => $items) {
            $category = $categoriesBySlug[$categorySlug];

            foreach ($items as $item) {
                $productIndex++;
                $seller = $sellersByKey[$item['seller']];
                $brand = $brandsByName[$item['brand']];

                $product = Product::create([
                    'seller_id' => $seller->id,
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'name' => $item['name'],
                    'slug' => Str::slug($item['name']).'-'.$productIndex,
                    'image_key' => $item['image_key'],
                    'description' => $item['description'],
                    'attributes' => $item['attributes'],
                    'status' => 'active',
                ]);

                $firstVariant = null;
                $productCode = 'p'.str_pad((string) $productIndex, 3, '0', STR_PAD_LEFT);
                foreach ($item['variants'] as $vIndex => $variantSpec) {
                    $colorSlug = Str::slug((string) ($variantSpec['color'] ?? 'standart'));
                    $variantImagePath = "images/products/variants/{$productCode}-{$colorSlug}.webp";

                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => 'LM-'.strtoupper(substr($item['image_key'], 0, 3)).'-'.($skuCounter++),
                        'color' => $variantSpec['color'],
                        'size' => $variantSpec['size'],
                        'image_path' => $variantImagePath,
                        'price' => $variantSpec['price'],
                        'discount_price' => $variantSpec['discount_price'],
                        'stock' => $variantSpec['stock'],
                    ]);

                    if ($vIndex === 0) {
                        $firstVariant = $variant;
                    }
                }

                // Her ürüne gerçekçi puan dağılımıyla doğrulanmış onaylı değerlendirmeler ekle
                $profile = $ratingProfiles[($productIndex - 1) % count($ratingProfiles)];

                foreach ($profile as $rIndex => $rating) {
                    /** @var User $reviewer */
                    $reviewer = $customers[($productIndex + $rIndex * 3) % $customers->count()];

                    if (! isset($seedOrderByCustomer[$reviewer->id])) {
                        $seedOrderByCustomer[$reviewer->id] = Order::create([
                            'user_id' => $reviewer->id,
                            'order_number' => 'LM-SEED-'.str_pad((string) $reviewer->id, 4, '0', STR_PAD_LEFT),
                            'idempotency_key' => 'seed-order-'.$reviewer->id,
                            'address' => [
                                'title' => 'Ev Adresim',
                                'city' => 'İstanbul',
                                'district' => 'Kadıköy',
                                'full_address' => 'Örnek Mah. Demokrasi Cad. No:12',
                            ],
                            'subtotal' => 1000.00,
                            'discount' => 0.00,
                            'shipping' => 0.00,
                            'total' => 1000.00,
                            'payment_status' => 'paid',
                        ]);
                    }

                    $order = $seedOrderByCustomer[$reviewer->id];

                    $subOrder = SubOrder::firstOrCreate(
                        [
                            'order_id' => $order->id,
                            'seller_id' => $seller->id,
                        ],
                        [
                            'status' => 'delivered',
                            'cargo_company' => 'Yurtiçi Kargo',
                            'tracking_number' => 'YK'.str_pad((string) ($order->id * 100 + $seller->id), 10, '0', STR_PAD_LEFT),
                            'delivered_at' => now()->subDays(3),
                            'subtotal' => (float) $firstVariant->effectivePrice(),
                            'shipping_amount' => 0.00,
                            'commission_amount' => round((float) $firstVariant->effectivePrice() * ((float) $category->commission_rate / 100), 2),
                        ]
                    );

                    $orderItem = OrderItem::create([
                        'sub_order_id' => $subOrder->id,
                        'variant_id' => $firstVariant->id,
                        'product_name' => $product->name,
                        'price' => $firstVariant->effectivePrice(),
                        'quantity' => 1,
                    ]);

                    $pool = $reviewTemplates[$rating];
                    Review::create([
                        'user_id' => $reviewer->id,
                        'product_id' => $product->id,
                        'order_item_id' => $orderItem->id,
                        'rating' => $rating,
                        'comment' => $pool[($productIndex + $rIndex) % count($pool)],
                        'is_approved' => true,
                    ]);
                }
            }
        }

        // Demo Kuponlar
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

        Coupon::create([
            'code' => 'ESKIKUPON',
            'type' => 'percentage',
            'value' => 20.00,
            'min_total' => 100.00,
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subDay(),
            'usage_limit' => 100,
            'used_count' => 0,
        ]);

        // Ana Sayfa Kampanya Bannerları
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
            'url' => '/arama',
            'sort' => 3,
            'is_active' => true,
        ]);
    }

    /**
     * 10 alt kategori × 10 ürün = 100 detaylı, kategoriye özel ürün tanımı.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private function catalogBlueprints(): array
    {
        $catalog = [];

        // 1. Telefon & Aksesuar (10 ürün: 5 Kulaklık + 5 Akıllı Saat)
        $phoneItems = [
            ['AeroSound Studio ANC Kablosuz Kulaklık', 'AeroSound', 'kulaklik', 3499, 2999, 'Bluetooth 5.4', '48 Saat', 'IPX4', '24 Ay'],
            ['NovaTech Wave Pro Bluetooth Kulaklık', 'NovaTech', 'kulaklik', 2499, null, 'Bluetooth 5.3', '36 Saat', 'IPX4', '24 Ay'],
            ['AeroSound PureBass Kablosuz Kulak Üstü Kulaklık', 'AeroSound', 'kulaklik', 1899, 1599, 'Bluetooth 5.3', '24 Saat', 'IPX4', '24 Ay'],
            ['NovaTech QuietMax Aktif Gürültü Engelleyici Kulaklık', 'NovaTech', 'kulaklik', 4499, 3999, 'Bluetooth 5.4', '48 Saat', 'IPX7', '36 Ay'],
            ['AeroSound AirLite Stüdyo Bluetooth Kulaklık', 'AeroSound', 'kulaklik', 2199, null, 'USB-C & Bluetooth', '36 Saat', 'IPX4', '24 Ay'],
            ['NovaTech Titan AMOLED Akıllı Saat', 'NovaTech', 'akilli-saat', 5499, 4899, 'Bluetooth 5.4', '14 Gün', '5 ATM', '24 Ay'],
            ['NovaTech FitPulse GPS Akıllı Saat', 'NovaTech', 'akilli-saat', 3799, 3299, 'Bluetooth 5.3', '7 Gün', 'IP68', '24 Ay'],
            ['AeroSound Chrono Pro Spor Akıllı Saat', 'AeroSound', 'akilli-saat', 4299, null, 'Bluetooth 5.3', '14 Gün', '5 ATM', '24 Ay'],
            ['NovaTech Active S2 Nabız Ölçerli Akıllı Saat', 'NovaTech', 'akilli-saat', 2899, 2499, 'Bluetooth 5.3', '7 Gün', 'IP68', '24 Ay'],
            ['NovaTech Ultra Titanium Akıllı Saat', 'NovaTech', 'akilli-saat', 6999, 6299, 'Bluetooth 5.4', '14 Gün', '5 ATM', '36 Ay'],
        ];

        foreach ($phoneItems as $idx => $p) {
            $isWatch = $p[2] === 'akilli-saat';
            $catalog['telefon-aksesuar'][] = [
                'seller' => 'tech',
                'brand' => $p[1],
                'name' => $p[0],
                'image_key' => $p[2],
                'description' => $isWatch
                    ? "{$p[0]}, yüksek parlaklıklı AMOLED ekranı, 7/24 kalp atış hızı, kandaki oksijen (SpO2) ve uyku takibi özellikleriyle gün boyu yanınızda. {$p[6]} uzun pil ömrü ve {$p[7]} suya dayanıklılık sertifikası ile spor ve iş hayatına tam uyum sağlar.\n\nHızlı manyetik şarj desteği, 100'den fazla antrenman modu ve akıllı bildirim yönetimi ile tek bilekte tam kontrol sunar."
                    : "{$p[0]}, hibrit aktif gürültü engelleme (ANC) teknolojisi ve 40 mm yüksek çözünürlüklü sürücüleriyle stüdyo kalitesinde ses deneyimi sunar. {$p[5]} bağlantısı sayesinde düşük gecikmeli kesintisiz bağlantı ve {$p[6]} pil ömrü sağlar.\n\nHafızalı sünger kulak yastıkları uzun süreli kullanımlarda dahi üst düzey konfor sunarken, hızlı şarj özelliği 10 dakikalık şarjla saatlerce müzik dinlemenize imkân verir.",
                'attributes' => [
                    'baglanti' => $p[5],
                    'pil_omru' => $p[6],
                    'suya_dayaniklilik' => $p[7],
                    'garanti' => $p[8],
                ],
                'variants' => [
                    ['color' => 'Siyah', 'size' => null, 'price' => $p[3], 'discount_price' => $p[4], 'stock' => 15 + ($idx * 3)],
                    ['color' => $isWatch ? 'Titanyum Gri' : 'Gümüş Beyaz', 'size' => null, 'price' => $p[3] + 150, 'discount_price' => $p[4] ? $p[4] + 150 : null, 'stock' => 12 + ($idx * 2)],
                ],
            ];
        }

        // 2. Bilgisayar & Tablet (10 ürün: Mekanik Klavye & Pro Donanım)
        $pcItems = [
            ['NovaTech KeyPro 75% Kablosuz Mekanik Klavye', 'NovaTech', 2699, 2299, '16 GB', '512 GB SSD', 'Kablosuz 2.4 GHz & Bluetooth', '24 Ay'],
            ['AeroSound Tactile RGB Mekanik Klavye', 'AeroSound', 3199, null, '16 GB', '1 TB SSD', 'USB-C Kablolu', '24 Ay'],
            ['NovaTech Compact White Mekanik Klavye', 'NovaTech', 2399, 1999, '8 GB', '256 GB SSD', 'Kablosuz 2.4 GHz & Bluetooth', '24 Ay'],
            ['NovaTech Creator Pro Alüminyum Klavye Seti', 'NovaTech', 4199, 3699, '32 GB', '1 TB SSD', 'Wi-Fi 6E & USB-C', '36 Ay'],
            ['AeroSound SilentSwitch Ofis Mekanik Klavye', 'AeroSound', 2199, null, '8 GB', '512 GB SSD', 'Kablosuz 2.4 GHz & Bluetooth', '24 Ay'],
            ['NovaTech HotSwap 82 Tuşlu Mekanik Klavye', 'NovaTech', 2899, 2499, '16 GB', '512 GB SSD', 'USB-C Kablolu', '24 Ay'],
            ['AeroSound StudioDesk Pro Kablosuz Klavye', 'AeroSound', 3499, 2999, '32 GB', '1 TB SSD', 'Wi-Fi 6E & USB-C', '36 Ay'],
            ['NovaTech Mini65 Taşınabilir Mekanik Klavye', 'NovaTech', 1899, null, '8 GB', '256 GB SSD', 'Kablosuz 2.4 GHz & Bluetooth', '24 Ay'],
            ['NovaTech Carbon Series Oyuncu Mekanik Klavye', 'NovaTech', 3899, 3399, '32 GB', '1 TB SSD', 'USB-C Kablolu', '24 Ay'],
            ['AeroSound UltraSlim Bluetooth Klavye', 'AeroSound', 2099, 1799, '16 GB', '512 GB SSD', 'Wi-Fi 6E & USB-C', '24 Ay'],
        ];

        foreach ($pcItems as $idx => $p) {
            $catalog['bilgisayar-tablet'][] = [
                'seller' => 'tech',
                'brand' => $p[1],
                'name' => $p[0],
                'image_key' => 'klavye',
                'description' => "{$p[0]}, PBT çift enjeksiyon tuş kapakları, değiştirilebilir (hot-swappable) mekanik anahtarları ve ergonomik eğimli gövdesiyle hem yazılım geliştirme hem de oyun deneyimi için tasarlandı.\n\n{$p[6]} bağlantı desteği sayesinde bilgisayar, tablet ve iş istasyonları arasında tek tuşla geçiş yapabilir; {$p[7]} distribütör garantisiyle güvenle kullanabilirsiniz.",
                'attributes' => [
                    'ram' => $p[4],
                    'depolama' => $p[5],
                    'baglanti' => $p[6],
                    'garanti' => $p[7],
                ],
                'variants' => [
                    ['color' => 'Antrasit', 'size' => null, 'price' => $p[2], 'discount_price' => $p[3], 'stock' => 14 + ($idx * 2)],
                    ['color' => 'Mat Beyaz', 'size' => null, 'price' => $p[2] + 100, 'discount_price' => $p[3] ? $p[3] + 100 : null, 'stock' => 10 + $idx],
                ],
            ];
        }

        // 3. Kadın Giyim (10 ürün — Bedenli!)
        $womenItems = [
            ['UrbanWear Klasik Kemerli Bej Trençkot', 'UrbanWear', 2899, 2399, 'Su İtici Gabardin', 'Regular Fit'],
            ['VeloStyle Oversize Yün Karışım Blazer Ceket', 'VeloStyle', 2499, null, 'Yün Karışım', 'Oversize'],
            ['UrbanWear Kruvaze Düğmeli Lacivert Ceket', 'UrbanWear', 2199, 1899, 'Yün Karışım', 'Slim Fit'],
            ['VeloStyle Mevsimlik Pamuklu Trençkot', 'VeloStyle', 2699, 2199, '%100 Pamuk', 'Regular Fit'],
            ['UrbanWear Minimalist Keten Yazlık Ceket', 'UrbanWear', 1799, null, 'Keten Dokuma', 'Oversize'],
            ['VeloStyle Astarlı Taş Rengi Trençkot', 'VeloStyle', 3199, 2699, 'Su İtici Gabardin', 'Regular Fit'],
            ['UrbanWear Belden Bağlamalı Şık Blazer', 'UrbanWear', 1999, 1699, '%100 Pamuk', 'Slim Fit'],
            ['VeloStyle Çift Cepli Keten Blazer Ceket', 'VeloStyle', 2099, null, 'Keten Dokuma', 'Regular Fit'],
            ['UrbanWear Su İtici Kapüşonlu Şehir Trençkotu', 'UrbanWear', 3399, 2899, 'Su İtici Gabardin', 'Oversize'],
            ['VeloStyle Modern Kesim Yün Karışım Kaban', 'VeloStyle', 3899, 3299, 'Yün Karışım', 'Regular Fit'],
        ];

        foreach ($womenItems as $idx => $p) {
            $catalog['kadin-giyim'][] = [
                'seller' => 'fashion',
                'brand' => $p[1],
                'name' => $p[0],
                'image_key' => 'ceket',
                'description' => "{$p[0]}, {$p[4]} kumaşı ve {$p[5]} modern kalıbıyla mevsim geçişlerinde hem ofis şıklığı hem günlük kombinler için ideal bir parça sunar.\n\nNefes alan iç astarı, özenli dikiş detayları ve formunu uzun süre koruyan dokusuyla gardırobunuzun zamansız favorisi olacak.",
                'attributes' => [
                    'kumas' => $p[4],
                    'kalip' => $p[5],
                ],
                'variants' => [
                    ['color' => 'Bej', 'size' => 'S', 'price' => $p[2], 'discount_price' => $p[3], 'stock' => 12 + $idx],
                    ['color' => 'Bej', 'size' => 'M', 'price' => $p[2], 'discount_price' => $p[3], 'stock' => 18 + $idx],
                    ['color' => 'Lacivert', 'size' => 'L', 'price' => $p[2], 'discount_price' => $p[3], 'stock' => 10 + $idx],
                ],
            ];
        }

        // 4. Erkek Giyim (10 ürün: 5 Ceket + 5 Günlük Sneaker — Bedenli!)
        $menItems = [
            ['UrbanWear Klasik Kesim Erkek Mevsimlik Ceket', 'UrbanWear', 2299, 1899, 'ceket', '%100 Organik Pamuk', 'Regular Fit', ['M', 'L', 'XL']],
            ['VeloStyle Slim Fit Lacivert Şehir Ceketi', 'VeloStyle', 2599, null, 'ceket', 'Penye Pamuk', 'Slim Fit', ['S', 'M', 'L']],
            ['UrbanWear Oversize Gabardin Erkek Trençkot', 'UrbanWear', 2999, 2499, 'ceket', '%100 Organik Pamuk', 'Oversize', ['M', 'L', 'XL']],
            ['VeloStyle Günlük Pamuklu Erkek Ceket', 'VeloStyle', 1899, 1599, 'ceket', 'Penye Pamuk', 'Regular Fit', ['M', 'L', 'XL']],
            ['UrbanWear Modern Dik Yaka Erkek Ceket', 'UrbanWear', 2199, null, 'ceket', '%100 Organik Pamuk', 'Slim Fit', ['S', 'M', 'L']],
            ['VeloStyle AirMesh Günlük Erkek Sneaker', 'VeloStyle', 2399, 1999, 'ayakkabi', 'Nefes Alan Mesh', 'Anatomik Kalıp', ['41', '42', '43']],
            ['UrbanWear StreetRun Beyaz Sneaker Ayakkabı', 'UrbanWear', 2199, null, 'ayakkabi', 'Nefes Alan Mesh', 'Regular Fit', ['40', '41', '42']],
            ['VeloStyle UrbanFlex Antrasit Spor Ayakkabı', 'VeloStyle', 2699, 2299, 'ayakkabi', 'Nefes Alan Mesh', 'Anatomik Kalıp', ['41', '42', '43']],
            ['UrbanWear CloudStep Hafif Günlük Ayakkabı', 'UrbanWear', 1999, 1699, 'ayakkabi', 'Nefes Alan Mesh', 'Regular Fit', ['40', '42', '43']],
            ['VeloStyle AeroStride Premium Sneaker', 'VeloStyle', 2999, null, 'ayakkabi', 'Nefes Alan Mesh', 'Anatomik Kalıp', ['41', '42', '43']],
        ];

        foreach ($menItems as $idx => $p) {
            $catalog['erkek-giyim'][] = [
                'seller' => 'fashion',
                'brand' => $p[1],
                'name' => $p[0],
                'image_key' => $p[4],
                'description' => "{$p[0]}, {$p[5]} malzemesi ve {$p[6]} tasarımıyla gün boyu konfor ve modern görünüm sunar.\n\nGünlük şehir temposuna dayanıklı dikiş ve taban yapısı, kolay kombinlenebilir renk seçenekleriyle stilinizi tamamlar.",
                'attributes' => [
                    'kumas' => $p[5],
                    'kalip' => $p[6],
                ],
                'variants' => [
                    ['color' => $p[4] === 'ayakkabi' ? 'Beyaz' : 'Lacivert', 'size' => $p[7][0], 'price' => $p[2], 'discount_price' => $p[3], 'stock' => 14 + $idx],
                    ['color' => $p[4] === 'ayakkabi' ? 'Kömür Gri' : 'Bej', 'size' => $p[7][1], 'price' => $p[2], 'discount_price' => $p[3], 'stock' => 18 + $idx],
                    ['color' => 'Siyah', 'size' => $p[7][2], 'price' => $p[2], 'discount_price' => $p[3], 'stock' => 11 + $idx],
                ],
            ];
        }

        // 5. Mutfak Gereçleri (10 ürün: Kahve Makineleri)
        $kitchenItems = [
            ['HomeCraft Barista Pro 15 Bar Espresso Makinesi', 'HomeCraft', 6999, 5999, 'Paslanmaz Çelik', '15 Bar / 1450W', '24 Ay'],
            ['ZenLiving AromaMaster Filtre Kahve Makinesi', 'ZenLiving', 2799, null, 'Fırçalanmış Krom', '1200W Filtre', '24 Ay'],
            ['HomeCraft CremaMax 20 Bar Süt Köpürtücülü Espresso', 'HomeCraft', 9499, 8299, 'Paslanmaz Çelik', '20 Bar / 1600W', '36 Ay'],
            ['ZenLiving BrewPro Cam Karaf Kahve Makinesi', 'ZenLiving', 3199, 2699, 'Mat Siyah Çelik', '1200W Filtre', '24 Ay'],
            ['HomeCraft Compact Espresso & Cappuccino Makinesi', 'HomeCraft', 5499, null, 'Mat Siyah Çelik', '15 Bar / 1450W', '24 Ay'],
            ['ZenLiving ThermalPot Zamanlayıcılı Filtre Kahve', 'ZenLiving', 3699, 3199, 'Fırçalanmış Krom', '1200W Filtre', '24 Ay'],
            ['HomeCraft Artisan Dual Boiler Espresso Makinesi', 'HomeCraft', 12499, 10999, 'Paslanmaz Çelik', '20 Bar / 1600W', '36 Ay'],
            ['ZenLiving MorningCup Dijital Filtre Kahve', 'ZenLiving', 2499, 2199, 'Mat Siyah Çelik', '1200W Filtre', '24 Ay'],
            ['HomeCraft Milano Chrome 15 Bar Espresso', 'HomeCraft', 7499, null, 'Fırçalanmış Krom', '15 Bar / 1450W', '24 Ay'],
            ['ZenLiving CafeLine Pro 20 Bar Espresso Makinesi', 'ZenLiving', 10299, 8999, 'Paslanmaz Çelik', '20 Bar / 1600W', '36 Ay'],
        ];

        foreach ($kitchenItems as $idx => $p) {
            $catalog['mutfak-gerecleri'][] = [
                'seller' => 'home',
                'brand' => $p[1],
                'name' => $p[0],
                'image_key' => 'kahve-makinesi',
                'description' => "{$p[0]}, {$p[4]} dayanıklı gövdesi ve {$p[5]} ısıtma sistemiyle evinizde kafe kalitesinde aromatik kahveler hazırlamanızı sağlar.\n\nÇıkarılabilir su haznesi, damlama önleyici sistem ve kolay temizlenebilir filtre yapısıyla her sabah zahmetsiz kahve keyfi sunar. {$p[6]} resmi garanti kapsamındadır.",
                'attributes' => [
                    'materyal' => $p[4],
                    'guc' => $p[5],
                    'garanti' => $p[6],
                ],
                'variants' => [
                    ['color' => 'İnoks Çelik', 'size' => null, 'price' => $p[2], 'discount_price' => $p[3], 'stock' => 9 + $idx],
                    ['color' => 'Mat Siyah', 'size' => null, 'price' => $p[2] + 200, 'discount_price' => $p[3] ? $p[3] + 200 : null, 'stock' => 7 + $idx],
                ],
            ];
        }

        // 6. Aydınlatma & Dekor (10 ürün: Masa Lambaları)
        $lightItems = [
            ['ZenLiving Nordic Meşe & Metal LED Masa Lambası', 'ZenLiving', 1499, 1199, 'Alüminyum & Meşe Ahşap', 'LED 12W Sıcak Işık', '24 Ay'],
            ['HomeCraft Lumina Dimmerli Çalışma Lambası', 'HomeCraft', 1899, null, 'Mat Metal', 'LED 18W Dimmerli', '24 Ay'],
            ['ZenLiving Aura Pirinç & Buzlu Cam Abajur', 'ZenLiving', 2199, 1849, 'Pirinç & Buzlu Cam', 'LED 12W Sıcak Işık', '24 Ay'],
            ['HomeCraft StudioDesk Dokunmatik LED Lamba', 'HomeCraft', 1299, 999, 'Mat Metal', 'LED 18W Dimmerli', '24 Ay'],
            ['ZenLiving Oslo Doğal Ahşap Gövdeli Masa Lambası', 'ZenLiving', 1699, null, 'Alüminyum & Meşe Ahşap', 'LED 12W Sıcak Işık', '24 Ay'],
            ['HomeCraft DomeGlass Modern Başucu Lambası', 'HomeCraft', 1999, 1649, 'Pirinç & Buzlu Cam', 'LED 10W USB Şarjlı', '24 Ay'],
            ['ZenLiving FlexArm Göz Yormayan Çalışma Lambası', 'ZenLiving', 1599, 1349, 'Mat Metal', 'LED 18W Dimmerli', '36 Ay'],
            ['HomeCraft WarmGlow Kablosuz Şarjlı Masa Lambası', 'HomeCraft', 1399, null, 'Alüminyum & Meşe Ahşap', 'LED 10W USB Şarjlı', '24 Ay'],
            ['ZenLiving Minimalist Siyah Metal Masa Lambası', 'ZenLiving', 1149, 899, 'Mat Metal', 'LED 12W Sıcak Işık', '24 Ay'],
            ['HomeCraft Milano Pirinç Tasarım Masa Lambası', 'HomeCraft', 2699, 2299, 'Pirinç & Buzlu Cam', 'LED 18W Dimmerli', '36 Ay'],
        ];

        foreach ($lightItems as $idx => $p) {
            $catalog['aydinlatma-dekor'][] = [
                'seller' => 'home',
                'brand' => $p[1],
                'name' => $p[0],
                'image_key' => 'masa-lambasi',
                'description' => "{$p[0]}, {$p[4]} malzemesi ve göz yormayan {$p[5]} aydınlatma modülüyle çalışma masanıza ve yaşam alanlarınıza sıcak İskandinav estetiği katar.\n\nDüşük enerji tüketimi, uzun LED ömrü ve dengeli ağırlık tabanı sayesinde hem dekoratif hem de işlevsel bir aydınlatma çözümüdür.",
                'attributes' => [
                    'materyal' => $p[4],
                    'guc' => $p[5],
                    'garanti' => $p[6],
                ],
                'variants' => [
                    ['color' => 'Mat Siyah', 'size' => null, 'price' => $p[2], 'discount_price' => $p[3], 'stock' => 16 + $idx],
                    ['color' => 'Pirinç / Meşe', 'size' => null, 'price' => $p[2] + 120, 'discount_price' => $p[3] ? $p[3] + 120 : null, 'stock' => 11 + $idx],
                ],
            ];
        }

        // 7. Fitness & Koşu (10 ürün: Performans Koşu Ayakkabıları — Numaralı!)
        $fitnessItems = [
            ['PeakFit Velocity Pro Karbon Koşu Ayakkabısı', 'PeakFit', 3899, 3299, 'Karbon Destekli EVA', 'Nefes Alan Mesh'],
            ['PeakFit StrideMax Yastıklamalı Koşu Ayakkabısı', 'PeakFit', 2999, null, 'Yüksek Yastıklamalı Köpük', 'Nefes Alan Mesh'],
            ['VeloStyle PaceRunner Hafif Antrenman Ayakkabısı', 'VeloStyle', 2499, 2099, 'Yüksek Yastıklamalı Köpük', 'Nefes Alan Mesh'],
            ['PeakFit TrailGrip Suya Dayanıklı Koşu Ayakkabısı', 'PeakFit', 3499, 2999, 'Kaymaz Kauçuk', 'Suya Dayanıklı'],
            ['PeakFit TempoFly Maraton Koşu Ayakkabısı', 'PeakFit', 4499, 3899, 'Karbon Destekli EVA', 'Nefes Alan Mesh'],
            ['VeloStyle GymForce Salon Antrenman Ayakkabısı', 'VeloStyle', 2199, null, 'Kaymaz Kauçuk', 'Nefes Alan Mesh'],
            ['PeakFit CloudBounce Günlük Koşu Ayakkabısı', 'PeakFit', 2799, 2399, 'Yüksek Yastıklamalı Köpük', 'Nefes Alan Mesh'],
            ['PeakFit RainShield Outdoor Koşu Ayakkabısı', 'PeakFit', 3699, null, 'Kaymaz Kauçuk', 'Suya Dayanıklı'],
            ['VeloStyle SprintX Ultra Hafif Koşu Ayakkabısı', 'VeloStyle', 3199, 2699, 'Karbon Destekli EVA', 'Nefes Alan Mesh'],
            ['PeakFit Endurance Pro Uzun Mesafe Ayakkabısı', 'PeakFit', 4199, 3599, 'Yüksek Yastıklamalı Köpük', 'Suya Dayanıklı'],
        ];

        foreach ($fitnessItems as $idx => $p) {
            $catalog['fitness-kosu'][] = [
                'seller' => 'sport',
                'brand' => $p[1],
                'name' => $p[0],
                'image_key' => 'ayakkabi',
                'description' => "{$p[0]}, {$p[4]} orta tabanı ve {$p[5]} üst yüzeyiyle her adımda yüksek enerji geri dönüşü ve üstün zemin tutuşu sağlar.\n\nUzun koşular, tempolu yürüyüşler ve salon antrenmanları için ayak anatomisine tam oturan hafif yapısıyla performansınızı bir üst seviyeye taşır.",
                'attributes' => [
                    'taban' => $p[4],
                    'su_gecirmezlik' => $p[5],
                ],
                'variants' => [
                    ['color' => 'Beyaz / Lacivert', 'size' => '40', 'price' => $p[2], 'discount_price' => $p[3], 'stock' => 10 + $idx],
                    ['color' => 'Beyaz / Lacivert', 'size' => '41', 'price' => $p[2], 'discount_price' => $p[3], 'stock' => 15 + $idx],
                    ['color' => 'Kömür Gri', 'size' => '42', 'price' => $p[2], 'discount_price' => $p[3], 'stock' => 18 + $idx],
                    ['color' => 'Kömür Gri', 'size' => '43', 'price' => $p[2], 'discount_price' => $p[3], 'stock' => 12 + $idx],
                ],
            ];
        }

        // 8. Kamp Malzemeleri (10 ürün: Outdoor Sırt Çantaları)
        $campItems = [
            ['PeakFit Summit 40L Su Geçirmez Trekking Çantası', 'PeakFit', 2699, 2299, '40 Litre', 'Tam Su Geçirmez'],
            ['PeakFit TrailPack 28L Günlük Outdoor Sırt Çantası', 'PeakFit', 1799, null, '28 Litre', 'Suya Dayanıklı'],
            ['UrbanWear Nomad 40L Seyahat & Kamp Çantası', 'UrbanWear', 2499, 2099, '40 Litre', 'Tam Su Geçirmez'],
            ['PeakFit Expedition 55L Dağcılık Sırt Çantası', 'PeakFit', 3699, 3199, '55 Litre', 'Tam Su Geçirmez'],
            ['PeakFit Ranger 28L Laptop Bölmeli Outdoor Çanta', 'PeakFit', 1999, 1699, '28 Litre', 'Suya Dayanıklı'],
            ['UrbanWear ForestPro 40L Kamp Sırt Çantası', 'UrbanWear', 2299, null, '40 Litre', 'Suya Dayanıklı'],
            ['PeakFit Alpine 55L Ergonomik Bel Destekli Çanta', 'PeakFit', 3999, 3499, '55 Litre', 'Tam Su Geçirmez'],
            ['UrbanWear CityTrail 28L Su Geçirmez Sırt Çantası', 'UrbanWear', 1649, 1399, '28 Litre', 'Tam Su Geçirmez'],
            ['PeakFit CampLite 40L Hafif Yürüyüş Çantası', 'PeakFit', 2399, null, '40 Litre', 'Suya Dayanıklı'],
            ['PeakFit Extreme 55L Su Geçirmez Keşif Çantası', 'PeakFit', 4299, 3699, '55 Litre', 'Tam Su Geçirmez'],
        ];

        foreach ($campItems as $idx => $p) {
            $catalog['kamp-malzemeleri'][] = [
                'seller' => 'sport',
                'brand' => $p[1],
                'name' => $p[0],
                'image_key' => 'sirt-cantasi',
                'description' => "{$p[0]}, {$p[4]} geniş iç hacmi ve {$p[5]} ripstop teknik kumaşıyla zorlu doğa koşullarında eşyalarınızı güvenle korur.\n\nNefes alan file sırt paneli, ayarlanabilir göğüs/bel kemerleri ve çoklu fermuarlı bölmeleriyle kamp, trekking ve hafta sonu kaçamakları için mükemmel ergonomi sunar.",
                'attributes' => [
                    'kapasite' => $p[4],
                    'su_gecirmezlik' => $p[5],
                ],
                'variants' => [
                    ['color' => 'Haki Yeşil', 'size' => null, 'price' => $p[2], 'discount_price' => $p[3], 'stock' => 14 + $idx],
                    ['color' => 'Mat Siyah', 'size' => null, 'price' => $p[2], 'discount_price' => $p[3], 'stock' => 19 + $idx],
                ],
            ];
        }

        // 9. Cilt Bakımı (10 ürün: Bakım Serumları)
        $skinItems = [
            ['PureGlow %2 Hyaluronik Asit Yoğun Nem Serumu', 'PureGlow', 649, 529, 'Tüm Cilt Tipleri', '30 ml'],
            ['PureGlow %15 C Vitamini Aydınlatıcı Serum', 'PureGlow', 749, null, 'Tüm Cilt Tipleri', '30 ml'],
            ['ZenLiving Niacinamide %10 Gözenek Sıkılaştırıcı Serum', 'ZenLiving', 589, 479, 'Karma & Yağlı Cilt', '30 ml'],
            ['PureGlow Ceramide Bariyer Onarıcı Bakım Serumu', 'PureGlow', 899, 749, 'Kuru & Hassas Cilt', '50 ml'],
            ['ZenLiving Peptit Kompleks Yaşlanma Karşıtı Serum', 'ZenLiving', 1199, 999, 'Tüm Cilt Tipleri', '50 ml'],
            ['PureGlow BHA Salisilik Asit Arındırıcı Serum', 'PureGlow', 629, null, 'Karma & Yağlı Cilt', '30 ml'],
            ['ZenLiving Centella Yatıştırıcı Hassas Cilt Serumu', 'ZenLiving', 799, 669, 'Kuru & Hassas Cilt', '50 ml'],
            ['PureGlow Squalane & E Vitamini Besleyici Serum', 'PureGlow', 849, null, 'Kuru & Hassas Cilt', '50 ml'],
            ['ZenLiving Botanik Nemlendirici Tonik & Serum', 'ZenLiving', 949, 799, 'Tüm Cilt Tipleri', '100 ml'],
            ['PureGlow Pro-Retinol Gece Yenileyici Serum', 'PureGlow', 1299, 1099, 'Karma & Yağlı Cilt', '30 ml'],
        ];

        foreach ($skinItems as $idx => $p) {
            $catalog['cilt-bakimi'][] = [
                'seller' => 'beauty',
                'brand' => $p[1],
                'name' => $p[0],
                'image_key' => 'cilt-bakim',
                'description' => "{$p[0]}, {$p[4]} için dermatolojik olarak test edilmiş temiz formülüyle cildin nem dengesini destekler ve doğal ışıltısını ortaya çıkarır.\n\nParaben, alkol ve sentetik parfüm içermeyen {$p[5]} UV korumalı amber cam damlalıklı şişesiyle sabah ve akşam bakım rutininizde güvenle kullanabilirsiniz.",
                'attributes' => [
                    'cilt_tipi' => $p[4],
                    'hacim' => $p[5],
                ],
                'variants' => [
                    ['color' => 'Amber Cam', 'size' => null, 'price' => $p[2], 'discount_price' => $p[3], 'stock' => 25 + $idx],
                    ['color' => 'İkili Avantaj Seti', 'size' => null, 'price' => round($p[2] * 1.8, 2), 'discount_price' => round($p[2] * 1.55, 2), 'stock' => 12 + $idx],
                ],
            ];
        }

        // 10. Parfüm (10 ürün: Eau de Parfum)
        $perfumeItems = [
            ['PureGlow Amber Noir Eau de Parfum', 'PureGlow', 2499, 2099, 'Odunsu & Amber', '100 ml'],
            ['VeloStyle Velvet Rose Eau de Parfum', 'VeloStyle', 2199, null, 'Çiçeksi & Pudralı', '50 ml'],
            ['PureGlow Citrus Breeze Eau de Parfum', 'PureGlow', 1899, 1599, 'Fresh & Narenciye', '100 ml'],
            ['VeloStyle Oud Royale Intense Parfüm', 'VeloStyle', 3299, 2799, 'Odunsu & Amber', '100 ml'],
            ['PureGlow Jasmine Bloom Eau de Parfum', 'PureGlow', 1999, null, 'Çiçeksi & Pudralı', '50 ml'],
            ['VeloStyle Bergamot & Cedarwood EDP', 'VeloStyle', 2299, 1949, 'Fresh & Narenciye', '100 ml'],
            ['PureGlow Santal Gold Eau de Parfum', 'PureGlow', 2899, 2499, 'Odunsu & Amber', '100 ml'],
            ['VeloStyle White Musk & Peony EDP', 'VeloStyle', 1799, null, 'Çiçeksi & Pudralı', '50 ml'],
            ['PureGlow Mediterranean Sea Salt EDP', 'PureGlow', 2099, 1749, 'Fresh & Narenciye', '100 ml'],
            ['VeloStyle Midnight Vanilla & Amber EDP', 'VeloStyle', 2999, 2599, 'Odunsu & Amber', '100 ml'],
        ];

        foreach ($perfumeItems as $idx => $p) {
            $catalog['parfum'][] = [
                'seller' => 'beauty',
                'brand' => $p[1],
                'name' => $p[0],
                'image_key' => 'parfum',
                'description' => "{$p[0]}, {$p[4]} koku ailesinin seçkin esanslarıyla hazırlanmış yüksek kalıcılığa sahip Eau de Parfum formülüdür.\n\nÜst notalarda ferah bir açılış sunarken kalp ve dip notalarda teninizle bütünleşen sofistike bir imza bırakır. {$p[5]} şık cam şişesinde sunulmaktadır.",
                'attributes' => [
                    'koku_turu' => $p[4],
                    'hacim' => $p[5],
                ],
                'variants' => [
                    ['color' => '50 ml Şişe', 'size' => null, 'price' => $p[2], 'discount_price' => $p[3], 'stock' => 20 + $idx],
                    ['color' => '100 ml Büyük Boy', 'size' => null, 'price' => round($p[2] * 1.45, 2), 'discount_price' => $p[3] ? round($p[3] * 1.45, 2) : null, 'stock' => 14 + $idx],
                ],
            ];
        }

        return $catalog;
    }
}
