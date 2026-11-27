<x-layouts.storefront title="Arama ve Filtreleme — LaraMart">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">
                    @if (! empty($filters['q']))
                        "{{ $filters['q'] }}" için Arama Sonuçları
                    @else
                        Tüm Ürünler ve Filtreleme
                    @endif
                </h1>
                <p class="mt-1 text-sm text-slate-600">
                    Toplam <strong>{{ $products->total() }}</strong> ürün bulundu. Filtreler URL'de saklanır; linki doğrudan paylaşabilirsiniz.
                </p>
            </div>

            <form method="GET" action="{{ route('search') }}" class="flex items-center gap-2">
                @foreach (['q', 'kategori', 'marka', 'min', 'max', 'renk', 'beden', 'puan'] as $param)
                    @if (! empty($filters[$param]))
                        <input type="hidden" name="{{ $param }}" value="{{ $filters[$param] }}">
                    @endif
                @endforeach
                <label for="sirala" class="text-xs font-semibold text-slate-600">Sırala:</label>
                <select
                    id="sirala"
                    name="sirala"
                    onchange="this.form.submit()"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none"
                >
                    <option value="en-yeni" @selected(($filters['sirala'] ?? 'en-yeni') === 'en-yeni')>En Yeni</option>
                    <option value="fiyat-artan" @selected(($filters['sirala'] ?? '') === 'fiyat-artan')>Fiyat: Düşükten Yükseğe</option>
                    <option value="fiyat-azalan" @selected(($filters['sirala'] ?? '') === 'fiyat-azalan')>Fiyat: Yüksekten Düşüğe</option>
                    <option value="en-cok-degerlendirilen" @selected(($filters['sirala'] ?? '') === 'en-cok-degerlendirilen')>En Çok Değerlendirilen</option>
                </select>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <aside class="lg:col-span-3">
                <x-storefront.filter-sidebar
                    :action="route('search')"
                    :filters="$filters"
                    :filter-options="$filterOptions"
                />
            </aside>

            <div class="lg:col-span-9 space-y-6">
                @if ($products->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach ($products as $product)
                            <x-storefront.product-card :product="$product" />
                        @endforeach
                    </div>

                    <div>
                        {{ $products->links() }}
                    </div>
                @else
                    <div class="rounded-xl border border-slate-200 bg-white p-12 text-center">
                        <p class="text-base font-semibold text-slate-700">Seçtiğiniz filtrelere uygun ürün bulunamadı.</p>
                        <a href="{{ route('search') }}" class="mt-4 inline-block rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Filtreleri Sıfırla
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.storefront>
