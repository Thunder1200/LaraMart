# LaraMart — Çok Satıcılı E-Ticaret Pazaryeri Projesi

**LaraMart**, Amazon ve Hepsiburada benzeri çok satıcılı (multi-vendor) bir e-ticaret pazaryeri uygulamasıdır. Satıcılar mağaza açıp ürün listeler; müşteriler ürün arar, sepete ekler, ödeme yapar ve siparişini takip eder; yöneticiler (admin) platformu ve komisyon süreçlerini yönetir.

## Teknoloji Yığını

- **Backend:** PHP 8.3+, Laravel 13
- **Veritabanı:** MySQL 8 (geliştirme/test için SQLite desteği)
- **Arayüz:** Blade + Livewire + Tailwind CSS
- **Yetkilendirme:** `spatie/laravel-permission` (`admin`, `seller`, `customer`)
- **Medya Yönetimi:** `spatie/laravel-medialibrary`
- **Test & Kod Kalitesi:** Pest, Laravel Pint, Larastan

## Kurulum Adımları

### 1. Gereksinimler
- PHP 8.3 veya üzeri
- Composer 2.x
- Node.js LTS ve npm
- MySQL 8 (veya lokal hızlı başlangıç için SQLite)

### 2. Projeyi Klonlama ve Bağımlılıkları Yükleme

```bash
git clone https://github.com/Thunder1200/LaraMart.git
cd LaraMart
composer install
npm install
```

### 3. Ortam Değişkenleri (`.env`) Ayarı

```bash
cp .env.example .env
php artisan key:generate
```

`.env` dosyasında MySQL veritabanı bilgilerinizi düzenleyin:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laramart
DB_USERNAME=root
DB_PASSWORD=
```

*(MySQL henüz kurulu değilse varsayılan `DB_CONNECTION=sqlite` ayarı ile de hemen çalıştırabilirsiniz.)*

### 4. Veritabanı Tablolarını Oluşturma ve Örnek Verileri Yükleme

```bash
php artisan migrate:fresh --seed
```

Bu komut **20 zorunlu pazaryeri tablosunu** (artı Laravel ve Spatie sistem tablolarını) oluşturur ve aşağıdaki örnek verilerle doldurur:
- **1** Admin (`admin@laramart.test` / `password`)
- **5** Onaylı Satıcı (`satici1@laramart.test` – `satici5@laramart.test` / `password`)
- **20** Müşteri (`musteri1@laramart.test` – `musteri20@laramart.test` / `password`)
- **15** Kategori (5 ana kategori + 10 alt kategori)
- **100** Ürün (varyantları, markaları, kuponları ve bannerları ile birlikte)

### 5. Uygulamayı Çalıştırma

İki ayrı terminalde çalıştırın:

```bash
npm run dev
```

```bash
php artisan serve
```

Uygulama `http://127.0.0.1:8000` adresinde açılacaktır.

### 6. Test ve Kod Kalitesi Komutları

```bash
php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse
```

## Proje Dokümantasyonu (Hafta 1)

- [ER Diyagramı (Mermaid & Tablo Özeti)](docs/er-diagram.md)
- [dbdiagram.io DBML Dosyası](docs/er-diagram.dbml)
- [Ana Sayfa Wireframe (HTML)](docs/wireframe.html)
- [Rakip Analizi (Amazon, Hepsiburada, Trendyol)](docs/rakip-analizi.md)
- [GitHub Projects Kanban Görev Listesi](docs/kanban-gorevleri.md)

## Veritabanı Tasarım Kuralları

1. Tüm fiyat ve parasal tutarlar `DECIMAL(10,2)` olarak saklanır (`FLOAT` kullanılmaz).
2. Sipariş anındaki adres (JSON), ürün adı ve fiyat bilgisi `orders` / `order_items` tablolarına kopyalanır.
3. Stok bilgisi `product_variants` tablosunda tutulur ve ödeme onayında `DB::transaction` + `lockForUpdate()` ile düşülür.
4. Çok satıcılı sepetlerde **1 ana sipariş (`orders`)** ve satıcı başına **1 alt sipariş (`sub_orders`)** oluşturulur.
