@extends('layouts.app')

@section('title', $product->name . ' - LaraMart')

@section('content')
<div class="space-y-10">
    <div class="bg-white rounded-2xl border border-slate-200 p-8 grid grid-cols-1 lg:grid-cols-2 gap-10">
        <div class="space-y-4">
            <div class="aspect-square bg-slate-100 rounded-xl flex items-center justify-center border border-slate-200 p-8 text-center">
                <div>
                    <span class="text-xs uppercase font-bold text-amber-600 block mb-2">{{ $product->brand?->name }}</span>
                    <h2 class="text-xl font-extrabold text-slate-800">{{ $product->name }}</h2>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="flex items-center justify-between text-sm">
                <a href="{{ route('catalog.category', $product->category->slug) }}" class="text-amber-600 font-semibold hover:underline">
                    {{ $product->category->name }}
                </a>
                <span class="text-slate-600 font-semibold">Ortalama Puan: ★ {{ $product->average_rating }} ({{ $product->approvedReviews->count() }} Yorum)</span>
            </div>

            <h1 class="text-3xl font-extrabold text-slate-900">{{ $product->name }}</h1>

            <div class="text-sm text-slate-600">
                Satıcı Mağaza:
                <a href="{{ route('catalog.seller', $product->seller->slug) }}" class="font-bold text-indigo-600 hover:underline">
                    {{ $product->seller->store_name }}
                </a>
            </div>

            <p class="text-slate-600 text-sm leading-relaxed">{{ $product->description }}</p>

            <livewire:product-variant-selector :product="$product" />

            @auth
                <form action="{{ route('account.favorites.toggle', $product) }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1">
                        ♥ Favorilerime Ekle / Çıkar
                    </button>
                </form>
            @endauth
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-8 space-y-6">
        <h2 class="text-xl font-extrabold text-slate-900">Müşteri Yorumları ({{ $product->approvedReviews->count() }})</h2>

        @forelse($product->approvedReviews as $review)
            <div class="border-b border-slate-100 pb-4">
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="font-bold text-slate-900">{{ $review->user?->name ?? 'Müşteri' }}</span>
                    <span class="text-amber-500 font-bold">★ {{ $review->rating }} / 5</span>
                </div>
                <p class="text-sm text-slate-600">{{ $review->comment }}</p>
            </div>
        @empty
            <p class="text-sm text-slate-500">Bu ürün için henüz onaylanmış yorum bulunmuyor.</p>
        @endforelse
    </div>
</div>
@endsection

