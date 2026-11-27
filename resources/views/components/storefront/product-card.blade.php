@props(['product'])

@php
    $variant = $product->displayVariant();
    $totalStock = $product->totalStock();
    $discountPercent = ($variant && $variant->hasDiscount() && (float) $variant->price > 0)
        ? (int) round((1 - ((float) $variant->discount_price / (float) $variant->price)) * 100)
        : null;
    $isFavorited = in_array((int) $product->id, $favoriteProductIds ?? [], true);
    $swatches = $product->colorSwatches();
    $initialImage = $product->thumbnailUrl();
@endphp

<article
    x-data="{
        productId: {{ (int) $product->id }},
        activeImage: @js($initialImage),
        activeColor: @js($swatches[0]['color'] ?? ''),
        favorited: {{ $isFavorited ? 'true' : 'false' }},
        favoriteLoading: false,
        favoriteUrl: @js(route('customer.favorites.toggle', $product, false)),
        loginUrl: @js(route('login', [], false)),
        async toggleFavorite() {
            if (this.favoriteLoading) {
                return;
            }
            const previous = this.favorited;
            this.favorited = !previous;
            this.favoriteLoading = true;
            try {
                const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') ?? '';
                const response = await fetch(this.favoriteUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token,
                    },
                });
                if (response.status === 401 || response.status === 419 || response.redirected) {
                    window.location.assign(this.loginUrl);
                    return;
                }
                if (!response.ok) {
                    this.favorited = previous;
                    return;
                }
                const data = await response.json();
                this.favorited = Boolean(data.favorited);
                window.dispatchEvent(new CustomEvent('favorite-toggled', {
                    detail: { productId: this.productId, favorited: this.favorited },
                }));
            } catch (error) {
                this.favorited = previous;
            } finally {
                this.favoriteLoading = false;
            }
        }
    }"
    @favorite-toggled.window="if ($event.detail.productId === productId) favorited = Boolean($event.detail.favorited)"
    class="group flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm hover:border-indigo-300 hover:shadow-md transition"
>
    <div class="relative aspect-square overflow-hidden bg-slate-100">
        <a href="{{ route('products.show', $product->slug) }}" class="block h-full w-full">
            <img
                :src="activeImage"
                src="{{ $initialImage }}"
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
            <button
                type="button"
                @click.prevent.stop="toggleFavorite()"
                :disabled="favoriteLoading"
                :aria-pressed="favorited ? 'true' : 'false'"
                :aria-label="favorited ? 'Favorilerden çıkar' : 'Favorilere ekle'"
                :class="favorited
                    ? 'bg-rose-50 text-rose-600 ring-1 ring-rose-200 dark:bg-rose-950/85 dark:text-rose-400 dark:ring-rose-800'
                    : 'bg-white/95 text-slate-400 hover:text-rose-500 dark:bg-slate-900/90 dark:text-slate-400 dark:hover:text-rose-400'"
                aria-label="{{ $isFavorited ? 'Favorilerden çıkar' : 'Favorilere ekle' }}"
                aria-pressed="{{ $isFavorited ? 'true' : 'false' }}"
                data-test="favorite-toggle-{{ $product->id }}"
                class="absolute top-2.5 right-2.5 z-10 flex h-9 w-9 items-center justify-center rounded-full shadow hover:scale-105 active:scale-95 transition cursor-pointer disabled:opacity-70"
            >
                <svg
                    class="h-5 w-5 transition-transform duration-150"
                    :class="favorited ? 'scale-110' : 'scale-100'"
                    fill="{{ $isFavorited ? 'currentColor' : 'none' }}"
                    :fill="favorited ? 'currentColor' : 'none'"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
            </button>
        @else
            <a
                href="{{ route('login') }}"
                aria-label="Favorilere eklemek için giriş yapın"
                class="absolute top-2.5 right-2.5 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-white/95 dark:bg-slate-900/90 text-slate-400 shadow hover:text-rose-500 dark:hover:text-rose-400 transition"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
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

        @if (count($swatches) > 1)
            <div class="mt-2.5 flex items-center gap-1.5">
                @foreach ($swatches as $swatch)
                    <button
                        type="button"
                        title="{{ $swatch['color'] }}"
                        @click.prevent="activeImage = @js($swatch['image']); activeColor = @js($swatch['color'])"
                        :class="activeColor === @js($swatch['color']) ? 'ring-2 ring-indigo-600 ring-offset-1' : 'ring-1 ring-slate-300'"
                        class="h-4 w-4 rounded-full transition"
                        style="background-color: {{ $swatch['hex'] }};"
                        aria-label="Renk: {{ $swatch['color'] }}"
                    ></button>
                @endforeach
                <span class="ml-1 text-[11px] text-slate-500 truncate" x-text="activeColor"></span>
            </div>
        @endif

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
