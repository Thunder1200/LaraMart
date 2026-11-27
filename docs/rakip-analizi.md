# Rakip Analizi: Amazon, Hepsiburada ve Trendyol

Hafta 1 kapsamında Türkiye ve dünyadaki önde gelen çok satıcılı (multi-vendor) pazaryerleri incelenmiş ve **LaraMart** için mimari çıkarımlar yapılmıştır.

## Platform Karşılaştırması

| Özellik / Süreç | Amazon | Hepsiburada | Trendyol | LaraMart Tasarım Kararı |
| :--- | :--- | :--- | :--- | :--- |
| **Çoklu Satıcı Sipariş Yapısı** | Tek sepette farklı satıcılar tek ödemede birleşir; sevkiyatlar satıcı bazında bölünür | Tek ana sipariş altında satıcı bazlı paketler ve ayrı kargo takibi | Satıcıya göre ayrı teslimat grupları ve ayrı kargo baremleri | **1 `orders` + satıcı başına 1 `sub_orders`** yapısı |
| **Sepet ve Kargo Gruplaması** | Satıcıya göre gönderim bilgisi ve ücretsiz kargo eşiği | Sepette satıcı başlıkları altında gruplama, kargo bedava barajı | Satıcı bazlı kargo ücreti ve ücretsiz kargo ilerleme çubuğu | Sepette satıcıya göre gruplama ve satıcı başı kargo hesabı (ör. 300 TL üstü ücretsiz) |
| **Ürün Varyantları** | Renk/beden seçilince fiyat, stok ve görsel anlık değişir | Varyant bazlı stok ve indirimli fiyat gösterimi | Beden seçimi, tükenen beden uyarısı | `product_variants` tablosu + Livewire ile sayfa yenilenmeden güncelleme |
| **Arama ve Filtreleme** | Otomatik tamamlama + kategori, marka, fiyat, puan filtreleri | Sol panel filtreleri ve URL paylaşılabilir sorgu parametreleri | Zengin renk/beden/marka/fiyat filtreleri | URL query string tabanlı paylaşılabilir filtreler + autocomplete + Scout |
| **Satıcı Onay ve Komisyon** | Belge/vergi doğrulaması ve kategoriye göre değişen komisyon oranları | Vergi no / IBAN ile mağaza başvurusu, kategori komisyonu | Kategori bazlı komisyon ve dönemsel hakediş | `sellers.status` onay akışı, kategori komisyonu (`categories.commission_rate`), `seller_payouts` |
| **Yorum ve İade Güvenliği** | "Doğrulanmış Satın Alma" etiketi, kolay iade | Yalnızca teslim edilmiş ürüne değerlendirme, 14 gün iade | Sipariş kalemi bazlı yorum ve 14 gün içinde iade talebi | `reviews.order_item_id` ile sadece satın alan ve teslim alan kullanıcı yorum yapabilir; 14 gün iade |

## LaraMart İçin Temel Çıkarımlar

1. **Veri Tutarlılığı:** Ürün fiyatı veya müşteri adresi sonradan değiştiğinde geçmiş fatura/sipariş kayıtlarının bozulmaması için snapshot (kopya) yaklaşımı uygulanır.
2. **Stok Güvenliği:** Yüksek talep gören son 1 adetlik ürünlerde çifte satışı önlemek için ödeme anında satır kilidi (`lockForUpdate()`) kullanılır.
3. **Rol İzolasyonu:** Müşteri (`/hesabim`), onaylı satıcı (`/satici`) ve yönetici (`/admin`) ekranları kesin middleware ve Policy sınırlarıyla ayrılır.
