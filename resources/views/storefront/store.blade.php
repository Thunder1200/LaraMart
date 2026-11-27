<x-layouts.storefront :title="$seller->store_name . ' — Mağaza — LaraMart'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        {{-- Mağaza Başlık Kartı --}}
        <section class="rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-900 to-indigo-700 p-8 text-white shadow-lg flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/15 text-2xl font-extrabold text-white">
                    {{ mb_substr($seller->store_name, 0, 1) }}
                </div>
                <div>
                    <span class="inline-block rounded-full bg-emerald-400/20 px-2.5 py-0.5 text-xs font-bold text-emerald-200">Onaylı Mağaza</span>
                    <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold">{{ $seller->store_name }}</h1>
                    <p class="text-sm text-indigo-100">{{ $products->total() }} aktif ürün listeleniyor</p>
                </div>
            </div>

            <form method="GET" action="{{ route('stores.show', $seller->slug) }}" class="flex items-center gap-2">
                <label for="sirala" class="text-xs font-semibold text-indigo-100">Sırala:</label>
                <select
                    id="sirala"
                    name="sirala"
                    onchange="this.form.submit()"
                    class="rounded-lg border border-white/20 bg-white/10 px-3 py-2 text-sm text-white focus:outline-none"
                >
                    <option value="en-yeni" class="text-slate-900" @selected($sort === 'en-yeni')>En Yeni</option>
                    <option value="fiyat-artan" class="text-slate-900" @selected($sort === 'fiyat-artan')>Fiyat: Düşükten Yükseğe</option>
                    <option value="fiyat-azalan" class="text-slate-900" @selected($sort === 'fiyat-azalan')>Fiyat: Yüksekten Düşüğe</option>
                    <option value="en-cok-degerlendirilen" class="text-slate-900" @selected($sort === 'en-cok-degerlendirilen')>En Çok Değerlendirilen</option>
                </select>
            </form>
        </section>

        {{-- Mağaza Ürünleri --}}
        @if ($products->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($products as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>

            <div>
                {{ $products->links() }}
            </div>
        @else
            <div class="rounded-xl border border-slate-200 bg-white p-12 text-center">
                <p class="text-base font-semibold text-slate-700">Bu mağazada henüz aktif ürün bulunmuyor.</p>
            </div>
        @endif
    </div>
</x-layouts.storefront>
