@extends('layouts.app')

@section('title', ($currentCategory ? $currentCategory->name : 'Ürün Kataloğu') . ' - LaraMart')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
    <aside class="bg-white rounded-xl border border-slate-200 p-6 h-fit space-y-5">
        <h2 class="text-lg font-extrabold border-b pb-3">Gelişmiş Filtreleme</h2>

        <form action="{{ $currentCategory ? route('catalog.category', $currentCategory->slug) : route('catalog.search') }}" method="GET" class="space-y-4 text-sm">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Arama Kelimesi</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="w-full border border-slate-300 rounded-lg px-3 py-2">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Marka</label>
                <select name="marka" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                    <option value="">Tüm Markalar</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->slug }}" @selected(($filters['marka'] ?? '') === $brand->slug)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Min TL</label>
                    <input type="number" name="min" value="{{ $filters['min'] ?? '' }}" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Max TL</label>
                    <input type="number" name="max" value="{{ $filters['max'] ?? '' }}" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Renk</label>
                    <select name="renk" class="w-full border border-slate-300 rounded-lg px-2 py-2">
                        <option value="">Tümü</option>
                        @foreach(['Siyah', 'Beyaz', 'Lacivert', 'Gri', 'Kırmızı', 'Yeşil'] as $renk)
                            <option value="{{ $renk }}" @selected(($filters['renk'] ?? '') === $renk)>{{ $renk }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Beden</label>
                    <select name="beden" class="w-full border border-slate-300 rounded-lg px-2 py-2">
                        <option value="">Tümü</option>
                        @foreach(['S', 'M', 'L', 'XL'] as $beden)
                            <option value="{{ $beden }}" @selected(($filters['beden'] ?? '') === $beden)>{{ $beden }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Sıralama</label>
                <select name="sirala" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                    <option value="yeni" @selected(($filters['sirala'] ?? '') === 'yeni')>En Yeniler</option>
                    <option value="fiyat_artan" @selected(($filters['sirala'] ?? '') === 'fiyat_artan')>Fiyat: Artan</option>
                    <option value="fiyat_azalan" @selected(($filters['sirala'] ?? '') === 'fiyat_azalan')>Fiyat: Azalan</option>
                    <option value="puan" @selected(($filters['sirala'] ?? '') === 'puan')>En Çok Değerlendirilen</option>
                </select>
            </div>

            <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-lg">
                Filtreleri Uygula
            </button>
        </form>
    </aside>

    <section class="lg:col-span-3 space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 px-6 py-4 flex items-center justify-between">
            <h1 class="text-xl font-extrabold text-slate-900">
                {{ $currentCategory ? $currentCategory->name : 'Tüm Ürünler' }}
            </h1>
            <span class="text-sm text-slate-500">Toplam {{ $products->total() }} ürün bulundu</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($products as $product)
                @php($firstVariant = $product->variants->first())
                <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                            <span>{{ $product->brand?->name }}</span>
                            <span>★ {{ $product->average_rating }}</span>
                        </div>
                        <a href="{{ route('catalog.product', $product->slug) }}" class="font-bold text-slate-900 hover:text-amber-600">
                            {{ $product->name }}
                        </a>
                        <p class="text-xs text-slate-500 mt-1">Mağaza: {{ $product->seller?->store_name }}</p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-lg font-extrabold text-slate-900">
                            {{ $firstVariant ? $firstVariant->effectivePrice() : '0.00' }} TL
                        </span>
                        <a href="{{ route('catalog.product', $product->slug) }}" class="bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold px-3 py-2 rounded-lg">
                            Ürüne Git
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-xl border border-slate-200 p-10 text-center text-slate-500">
                    Aradığınız kriterlere uygun ürün bulunamadı.
                </div>
            @endforelse
        </div>

        <div>
            {{ $products->links() }}
        </div>
    </section>
</div>
@endsection

