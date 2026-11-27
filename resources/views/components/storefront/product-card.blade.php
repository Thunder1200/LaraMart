@props(['product'])

@php
    $variant = $product->displayVariant();
    $totalStock = $product->totalStock();
    $discountPercent = ($variant && $variant->hasDiscount() && (float) $variant->price > 0)
        ? (int) round((1 - ((float) $variant->discount_price / (float) $variant->price)) * 100)
        : null;
    $isFavorited = in_array($product->id, $favoriteProductIds ?? [], true);
@endphp

<article class="group flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm hover:border-indigo-300 hover:shadow-md transition">
    <div class="relative aspect-square overflow-hidden bg-slate-100">
        <a href="{{ route('products.show', $product->slug) }}" class="block h-full w-full">
            <img
                src="{{ $product->thumbnailUrl() }}"
                alt="{{ $product->name }}"
                loading="lazy"
                class="h-full w-full object-cover group-hover:scale-105 transition duration-300"
            >
        </a>

        @if ($discountPercent)
            <span class="absolute top-2.5 left-2.5 rounded-full bg-rose-600 px-2.5 py-0.5 text-xs font-bold text-white shadow">
                -%{{ $discountPercent }}
            </span>
        @endif

        @auth
            <form method="POST" action="{{ route('customer.favorites.toggle', $product) }}" class="absolute top-2.5 right-2.5">
                @csrf
                <button
                    type="submit"
                    aria-label="{{ $isFavorited ? 'Favorilerden çıkar' : 'Favorilere ekle' }}"
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-white/95 shadow hover:scale-105 transition {{ $isFavorited ? 'text-rose-600' : 'text-slate-400 hover:text-rose-500' }}"
                >
                    <span class="text-base leading-none">{{ $isFavorited ? '♥' : '♡' }}</span>
                </button>
            </form>
        @else
            <a
                href="{{ route('login') }}"
                aria-label="Favorilere eklemek için giriş yapın"
                class="absolute top-2.5 right-2.5 flex h-8 w-8 items-center justify-center rounded-full bg-white/95 text-slate-400 shadow hover:text-rose-500 transition"
            >
                <span class="text-base leading-none">♡</span>
            </a>
        @endauth

        @if ($totalStock === 0)
            <span class="absolute bottom-2.5 right-2.5 rounded-full bg-slate-900/80 px-2.5 py-0.5 text-xs font-semibold text-white">
                Tükendi
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-4">
        <div class="flex items-center justify-between gap-2 text-xs text-slate-500">
            <span class="font-semibold text-indigo-600 truncate">{{ $product->brand?->name ?? 'LaraMart' }}</span>
            @if ($product->seller)
                <a href="{{ route('stores.show', $product->seller->slug) }}" class="truncate hover:text-indigo-700 hover:underline">
                    {{ $product->seller->store_name }}
                </a>
            @endif
        </div>

        <a href="{{ route('products.show', $product->slug) }}" class="mt-1.5 line-clamp-2 text-sm font-bold text-slate-900 group-hover:text-indigo-700">
            {{ $product->name }}
        </a>

        <div class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
            <span class="text-amber-500">★</span>
            <span>{{ number_format((float) ($product->approved_reviews_avg_rating ?? 0), 1) }}</span>
            <span>({{ $product->approved_reviews_count ?? 0 }})</span>
            <span class="text-slate-300">•</span>
            <span>{{ $product->variants->count() }} varyant</span>
        </div>

        <div class="mt-auto pt-4 flex items-end justify-between gap-2">
            <div>
                @if ($variant)
                    @if ($variant->hasDiscount())
                        <p class="text-xs text-slate-400 line-through">
                            {{ number_format((float) $variant->price, 2, ',', '.') }} TL
                        </p>
                        <p class="text-base font-extrabold text-indigo-700">
                            {{ number_format((float) $variant->discount_price, 2, ',', '.') }} TL
                        </p>
                    @else
                        <p class="text-base font-extrabold text-slate-900">
                            {{ number_format((float) $variant->price, 2, ',', '.') }} TL
                        </p>
                    @endif
                @else
                    <p class="text-sm font-semibold text-slate-500">Fiyat bilgisi yok</p>
                @endif
            </div>

            <a
                href="{{ route('products.show', $product->slug) }}"
                class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700 hover:bg-indigo-600 hover:text-white transition"
            >
                İncele
            </a>
        </div>
    </div>
</article>
