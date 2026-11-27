<?php

namespace App\Support;

use App\Models\Category;

class CategoryFilterSchema
{
    /**
     * Beden filtresinin anlamlı olduğu kategoriler.
     *
     * @var list<string>
     */
    private const array SIZE_ENABLED_SLUGS = [
        'giyim-moda',
        'kadin-giyim',
        'erkek-giyim',
        'fitness-kosu',
        'ayakkabi',
    ];

    public static function supportsSize(?Category $category): bool
    {
        if ($category === null) {
            return true;
        }

        if (in_array($category->slug, self::SIZE_ENABLED_SLUGS, true)) {
            return true;
        }

        $parentSlug = $category->parent?->slug;

        return $parentSlug !== null && in_array($parentSlug, self::SIZE_ENABLED_SLUGS, true);
    }

    /**
     * Kategoriye özel teknik özellik filtrelerini döndürür.
     *
     * @return array<string, array{label: string, options: list<string>}>
     */
    public static function attributeFiltersFor(?Category $category): array
    {
        if ($category === null) {
            return [];
        }

        $slug = $category->slug;
        $parentSlug = $category->parent?->slug;

        $schemas = [
            'telefon-aksesuar' => [
                'baglanti' => [
                    'label' => 'Bağlantı Teknolojisi',
                    'options' => ['Bluetooth 5.3', 'Bluetooth 5.4', 'USB-C & Bluetooth'],
                ],
                'pil_omru' => [
                    'label' => 'Pil Ömrü',
                    'options' => ['24 Saat', '36 Saat', '48 Saat', '7 Gün', '14 Gün'],
                ],
                'suya_dayaniklilik' => [
                    'label' => 'Suya Dayanıklılık',
                    'options' => ['IPX4', 'IPX7', 'IP68', '5 ATM'],
                ],
            ],
            'bilgisayar-tablet' => [
                'ram' => [
                    'label' => 'Bellek (RAM)',
                    'options' => ['8 GB', '16 GB', '32 GB'],
                ],
                'depolama' => [
                    'label' => 'Depolama Kapasitesi',
                    'options' => ['256 GB SSD', '512 GB SSD', '1 TB SSD'],
                ],
                'baglanti' => [
                    'label' => 'Bağlantı Tipi',
                    'options' => ['Wi-Fi 6E & USB-C', 'Kablosuz 2.4 GHz & Bluetooth', 'USB-C Kablolu'],
                ],
            ],
            'elektronik' => [
                'baglanti' => [
                    'label' => 'Bağlantı Teknolojisi',
                    'options' => ['Bluetooth 5.3', 'Bluetooth 5.4', 'Wi-Fi 6E & USB-C', 'Kablosuz 2.4 GHz & Bluetooth'],
                ],
                'garanti' => [
                    'label' => 'Garanti Süresi',
                    'options' => ['24 Ay', '36 Ay'],
                ],
            ],
            'kadin-giyim' => [
                'kumas' => [
                    'label' => 'Kumaş Türü',
                    'options' => ['Su İtici Gabardin', 'Yün Karışım', '%100 Pamuk', 'Keten Dokuma'],
                ],
                'kalip' => [
                    'label' => 'Kalıp',
                    'options' => ['Regular Fit', 'Oversize', 'Slim Fit'],
                ],
            ],
            'erkek-giyim' => [
                'kumas' => [
                    'label' => 'Kumaş / Üst Yüzey',
                    'options' => ['%100 Organik Pamuk', 'Penye Pamuk', 'Nefes Alan Mesh'],
                ],
                'kalip' => [
                    'label' => 'Kalıp',
                    'options' => ['Regular Fit', 'Oversize', 'Slim Fit', 'Anatomik Kalıp'],
                ],
            ],
            'giyim-moda' => [
                'kumas' => [
                    'label' => 'Kumaş Türü',
                    'options' => ['%100 Organik Pamuk', 'Su İtici Gabardin', 'Yün Karışım', 'Nefes Alan Mesh'],
                ],
                'kalip' => [
                    'label' => 'Kalıp',
                    'options' => ['Regular Fit', 'Oversize', 'Slim Fit'],
                ],
            ],
            'mutfak-gerecleri' => [
                'materyal' => [
                    'label' => 'Gövde Materyali',
                    'options' => ['Paslanmaz Çelik', 'Fırçalanmış Krom', 'Mat Siyah Çelik'],
                ],
                'guc' => [
                    'label' => 'Basınç / Güç',
                    'options' => ['15 Bar / 1450W', '20 Bar / 1600W', '1200W Filtre'],
                ],
            ],
            'aydinlatma-dekor' => [
                'materyal' => [
                    'label' => 'Materyal',
                    'options' => ['Alüminyum & Meşe Ahşap', 'Pirinç & Buzlu Cam', 'Mat Metal'],
                ],
                'guc' => [
                    'label' => 'Işık Teknolojisi',
                    'options' => ['LED 12W Sıcak Işık', 'LED 18W Dimmerli', 'LED 10W USB Şarjlı'],
                ],
            ],
            'ev-yasam' => [
                'materyal' => [
                    'label' => 'Materyal',
                    'options' => ['Paslanmaz Çelik', 'Fırçalanmış Krom', 'Alüminyum & Meşe Ahşap', 'Pirinç & Buzlu Cam'],
                ],
                'garanti' => [
                    'label' => 'Garanti Süresi',
                    'options' => ['24 Ay', '36 Ay'],
                ],
            ],
            'fitness-kosu' => [
                'taban' => [
                    'label' => 'Taban Teknolojisi',
                    'options' => ['Karbon Destekli EVA', 'Yüksek Yastıklamalı Köpük', 'Kaymaz Kauçuk'],
                ],
                'su_gecirmezlik' => [
                    'label' => 'Hava / Su Koruması',
                    'options' => ['Nefes Alan Mesh', 'Suya Dayanıklı'],
                ],
            ],
            'kamp-malzemeleri' => [
                'kapasite' => [
                    'label' => 'Hacim / Kapasite',
                    'options' => ['28 Litre', '40 Litre', '55 Litre'],
                ],
                'su_gecirmezlik' => [
                    'label' => 'Su Koruması',
                    'options' => ['Tam Su Geçirmez', 'Suya Dayanıklı'],
                ],
            ],
            'spor-outdoor' => [
                'su_gecirmezlik' => [
                    'label' => 'Su Koruması',
                    'options' => ['Tam Su Geçirmez', 'Suya Dayanıklı', 'Nefes Alan Mesh'],
                ],
            ],
            'cilt-bakimi' => [
                'cilt_tipi' => [
                    'label' => 'Cilt Tipi',
                    'options' => ['Tüm Cilt Tipleri', 'Kuru & Hassas Cilt', 'Karma & Yağlı Cilt'],
                ],
                'hacim' => [
                    'label' => 'Hacim',
                    'options' => ['30 ml', '50 ml', '100 ml'],
                ],
            ],
            'parfum' => [
                'koku_turu' => [
                    'label' => 'Koku Ailesi',
                    'options' => ['Odunsu & Amber', 'Çiçeksi & Pudralı', 'Fresh & Narenciye'],
                ],
                'hacim' => [
                    'label' => 'Şişe Hacmi',
                    'options' => ['50 ml', '100 ml'],
                ],
            ],
            'kozmetik-bakim' => [
                'hacim' => [
                    'label' => 'Hacim',
                    'options' => ['30 ml', '50 ml', '100 ml'],
                ],
            ],
        ];

        if (isset($schemas[$slug])) {
            return $schemas[$slug];
        }

        if ($parentSlug !== null && isset($schemas[$parentSlug])) {
            return $schemas[$parentSlug];
        }

        return [];
    }

    public static function labelFor(string $key): string
    {
        return match ($key) {
            'baglanti' => 'Bağlantı Teknolojisi',
            'pil_omru' => 'Pil Ömrü',
            'suya_dayaniklilik' => 'Suya Dayanıklılık',
            'ram' => 'Bellek (RAM)',
            'depolama' => 'Depolama Kapasitesi',
            'garanti' => 'Garanti Süresi',
            'kumas' => 'Kumaş / Üst Yüzey',
            'kalip' => 'Kalıp',
            'materyal' => 'Materyal',
            'guc' => 'Güç / Teknoloji',
            'taban' => 'Taban Teknolojisi',
            'su_gecirmezlik' => 'Su Koruması',
            'kapasite' => 'Hacim / Kapasite',
            'cilt_tipi' => 'Cilt Tipi',
            'koku_turu' => 'Koku Ailesi',
            'hacim' => 'Hacim',
            default => ucfirst(str_replace('_', ' ', $key)),
        };
    }
}
