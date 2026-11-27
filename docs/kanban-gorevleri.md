# GitHub Projects Kanban Panosu Görev Listesi

GitHub deposunda **Projects → New Project → Board** seçerek aşağıdaki sütunları ve kartları oluşturabilirsiniz:
- **Sütunlar:** `Backlog` | `Yapılacak (To Do)` | `Devam Ediyor (In Progress)` | `İncelemede (Review)` | `Tamamlandı (Done)`

## Hafta 1 — Kurulum, Analiz ve Veritabanı Tasarımı (`Done`)
- [x] Amazon, Hepsiburada ve Trendyol rakip analizi
- [x] Laravel 13 + Livewire + Pest + Pint + Larastan kurulumu
- [x] GitHub reposu, `main` ve `develop` dallarının oluşturulması
- [x] 20 zorunlu tablo için ER diyagramı (`dbdiagram.io` + Mermaid)
- [x] Tüm migration dosyaları ve Eloquent model ilişkileri
- [x] Factory ve seeder (1 admin, 5 satıcı, 20 müşteri, 15 kategori, 100 ürün)
- [x] Ana sayfa wireframe tasarımı ve kurulum README dokümanı

## Hafta 2 — Kimlik Doğrulama, Roller ve Kategoriler
- [ ] Kayıt, giriş, şifre sıfırlama, e-posta doğrulama (Mailpit)
- [ ] `spatie/laravel-permission` rolleri (`admin`, `seller`, `customer`) ve middleware
- [ ] Admin Kategori CRUD (alt kategori, slug, sıralama)
- [ ] Ortak layout (header, arama, sepet, hesabım, footer, DB mega menü) + Türkçe doğrulama (`lang/tr`)

## Hafta 3 — Ürün Kataloğu ve Vitrin
- [ ] Ürün/varyant yönetimi ve MediaLibrary çoklu görsel/thumbnail
- [ ] Ana sayfa (banner slider, öne çıkan kategoriler, çok satanlar, yeni gelenler)
- [ ] Kategori sayfası (24 ürün sayfalama, sıralama) ve SEO URL'ler
- [ ] Ürün detay (galeri, Livewire varyant seçimi, N+1 kontrolü)

## Hafta 4 — Arama, Filtreleme ve Satıcı Paneli
- [ ] Arama kutusu (ürün + marka) ve yazarken öneri (autocomplete)
- [ ] URL'de saklanan filtreler (kategori, marka, fiyat, renk, beden, puan)
- [ ] Satıcı başvuru formu ve `/satici` paneli ürün CRUD + Policy
- [ ] Herkese açık mağaza sayfası (`/magaza/{slug}`)

## Hafta 5 — Sepet, Favoriler, Adres ve Kupon
- [ ] Misafir/üye sepeti, girişte birleştirme, satıcı bazlı kargo gruplama, stok limiti
- [ ] Favoriler ve il/ilçe seçimli adres defteri
- [ ] Kupon sistemi ve sepet hesabı için en az 5 birim test

## Hafta 6 — Ödeme ve Sipariş Oluşturma
- [ ] Checkout sayfası ve iyzico sandbox (3D Secure) entegrasyonu
- [ ] Tek DB transaction'da sipariş + alt sipariş + stok kilidi (`lockForUpdate`) + idempotency
- [ ] Kuyruklu sipariş onay e-postası, Siparişlerim sayfası ve 2 kritik feature test

## Hafta 7 — Sipariş Yönetimi, Kargo, İade ve Yorumlar
- [ ] Enum ile alt sipariş durum makinesi ve geçersiz geçiş testi
- [ ] Satıcı kargo/takip girişi, e-posta + site içi bildirimler
- [ ] 14 günlük iade talebi akışı ve satın alana özel yorum/puan sistemi

## Hafta 8 — Admin Paneli ve Raporlar
- [ ] Filament admin paneli kaynakları ve satıcı onay ekranı
- [ ] Kategori bazlı komisyon hesabı + birim test
- [ ] Admin & satıcı dashboard grafikleri, hakediş listesi ve Excel/CSV dışa aktarım

## Hafta 9 — REST API, Performans ve Gelişmiş Arama
- [ ] Laravel Sanctum `/api/v1` uç noktaları, Resource, rate limit, Scribe/Postman dokümanı
- [ ] Önbellekleme + invalidation, DB indeksleri, Laravel Scout arama, ağır işlerin kuyruğa alınması

## Hafta 10 — Test, Güvenlik, Canlıya Alma ve Final Sunumu
- [ ] En az 40 test, güvenlik kontrol listesi, GitHub Actions CI
- [ ] Canlı yayın (HTTPS, queue worker, cron), demo verisi ve final sunumu
