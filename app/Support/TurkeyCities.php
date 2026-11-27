<?php

namespace App\Support;

class TurkeyCities
{
    /**
     * @return array<string, list<string>>
     */
    public static function all(): array
    {
        return [
            'İstanbul' => ['Kadıköy', 'Beşiktaş', 'Üsküdar', 'Şişli', 'Bakırköy', 'Ataşehir', 'Maltepe', 'Beyoğlu', 'Sarıyer', 'Fatih'],
            'Ankara' => ['Çankaya', 'Yenimahalle', 'Keçiören', 'Etimesgut', 'Mamak', 'Sincan', 'Gölbaşı'],
            'İzmir' => ['Konak', 'Karşıyaka', 'Bornova', 'Buca', 'Bayraklı', 'Çiğli', 'Narlıdere', 'Urla'],
            'Bursa' => ['Nilüfer', 'Osmangazi', 'Yıldırım', 'Mudanya', 'İnegöl'],
            'Antalya' => ['Muratpaşa', 'Konyaaltı', 'Kepez', 'Alanya', 'Manavgat'],
            'Adana' => ['Seyhan', 'Çukurova', 'Yüreğir', 'Sarıçam'],
            'Konya' => ['Selçuklu', 'Meram', 'Karatay'],
            'Kocaeli' => ['İzmit', 'Gebze', 'Darıca', 'Başiskele', 'Gölcük'],
            'Eskişehir' => ['Odunpazarı', 'Tepebaşı'],
            'Gaziantep' => ['Şahinbey', 'Şehitkamil'],
            'Samsun' => ['Atakum', 'İlkadım', 'Canik'],
            'Trabzon' => ['Ortahisar', 'Akçaabat', 'Yomra'],
        ];
    }
}
