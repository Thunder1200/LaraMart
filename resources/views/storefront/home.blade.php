@extends('layouts.app')

@section('title', 'LaraMart - Çok Satıcılı E-Ticaret Pazaryeri')

@section('content')
<div class="space-y-12">
    @if($banners->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($banners as $banner)
                <a href="{{ $banner->url ?? '#' }}" class="block bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-2xl p-8 shadow-md hover:shadow-xl transition">
                    <span class="inline-block bg-amber-500 text-slate-950 text-xs font-extrabold px-3 py-1 rounded-full mb-3">KAMPANYA</span>
                    <h2 class="text-2xl font-extrabold mb-2">{{ $banner->title }}</h2>
                    <p class="text-slate-300 text-sm">Hemen fırsatları inceleyin &rarr;</p>
                </a>
            @endforeach
        </div>
    @endif

    <section>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-extrabold text-slate-900">Öne Çıkan Kategoriler</h2>
            <a href="{{ route('catalog.search') }}" class="text-sm font-semibold text-amber-600 hover:underline">Tümünü Gör &rarr;</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            @foreach($featuredCategories as $category)
                <a href="{{ route('catalog.category', $category->slug) }}" class="bg-white border border-slate-200 rounded-xl p-5 hover:border-amber-500 hover:shadow transition">
                    <h3 class="font-bold text-slate-900">{{ $category->name }}</h3>
                    <p class="text-xs text-slate-500 mt-1">{{ $category->children->count() }} Alt Kategori</p>
                </a>
            @endforeach
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-extrabold text-slate-900 mb-6">Çok Satan Ürünler</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($bestSellers as $product)
                @php($firstVariant = $product->variants->first())
                <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                            <span>{{ $product->brand?->name }}</span>
                            <span>★ {{ $product->average_rating }}</span>
                        </div>
                        <a href="{{ route('catalog.product', $product->slug) }}" class="font-bold text-slate-900 hover:text-amber-600 line-clamp-2">
                            {{ $product->name }}
                        </a>
                        <p class="text-xs text-slate-500 mt-1">Satıcı: {{ $product->seller?->store_name }}</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-lg font-extrabold text-slate-900">
                            {{ $firstVariant ? $firstVariant->effectivePrice() : '0.00' }} TL
                        </span>
                        <a href="{{ route('catalog.product', $product->slug) }}" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold px-3 py-2 rounded-lg">
                            İncele
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-extrabold text-slate-900 mb-6">Yeni Gelenler</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($newArrivals as $product)
                @php($firstVariant = $product->variants->first())
                <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                            <span>{{ $product->category?->name }}</span>
                            <span>★ {{ $product->average_rating }}</span>
                        </div>
                        <a href="{{ route('catalog.product', $product->slug) }}" class="font-bold text-slate-900 hover:text-amber-600 line-clamp-2">
                            {{ $product->name }}
                        </a>
                        <p class="text-xs text-slate-500 mt-1">Satıcı: {{ $product->seller?->store_name }}</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-lg font-extrabold text-slate-900">
                            {{ $firstVariant ? $firstVariant->effectivePrice() : '0.00' }} TL
                        </span>
                        <a href="{{ route('catalog.product', $product->slug) }}" class="bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold px-3 py-2 rounded-lg">
                            Detay
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection

