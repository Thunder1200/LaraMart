@php
    $variant = $this->selectedVariant;
    $images = $this->gallery;
    $activeImage = $images[$activeImageIndex] ?? $images[0];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
    {{-- Sol: Çoklu Görsel Galerisi --}}
    <div class="lg:col-span-6 space-y-4">
        <div class="aspect-square overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
            <img
                src="{{ $activeImage['url'] }}"
                alt="{{ $product->name }}"
                class="h-full w-full object-cover"
            >
        </div>

        @if (count($images) > 1)
            <div class="grid grid-cols-5 gap-3">
                @foreach ($images as $index => $image)
                    <button
                        type="button"
                        @if (! empty($image['variant_id']))
                            wire:click="selectVariant({{ $image['variant_id'] }})"
                        @else
                            wire:click="selectImage({{ $index }})"
                        @endif
                        title="{{ $image['color'] ?? ($product->name . ' önizleme') }}"
                        class="group relative aspect-square overflow-hidden rounded-lg border-2 transition {{ $activeImageIndex === $index ? 'border-indigo-600 ring-2 ring-indigo-200' : 'border-slate-200 hover:border-slate-400' }}"
                    >
                        <img
                            src="{{ $image['thumb'] }}"
                            alt="{{ $product->name }} {{ $image['color'] ?? ('önizleme ' . ($index + 1)) }}"
                            class="h-full w-full object-cover"
                        >
                        @if (! empty($image['color']))
                            <span class="absolute inset-x-0 bottom-0 bg-slate-900/70 px-1 py-0.5 text-[10px] font-semibold text-white truncate">
                                {{ $image['color'] }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Sağ: Ürün Bilgileri, Varyant Seçimi ve Sepete Ekle --}}
    <div class="lg:col-span-6 flex flex-col space-y-5">
        <div>
            <div class="flex flex-wrap items-center gap-2 text-xs">
                @if ($product->brand)
                    <span class="rounded-full bg-indigo-50 px-2.5 py-1 font-bold text-indigo-700">{{ $product->brand->name }}</span>
                @endif
                <span class="text-slate-500">
                    Satıcı:
                    <a href="{{ route('stores.show', $product->seller->slug) }}" class="font-bold text-indigo-700 hover:underline">
                        {{ $product->seller->store_name }}
                    </a>
                </span>
            </div>

            <div class="mt-2 flex items-start justify-between gap-4">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $product->name }}</h1>
                <button
                    type="button"
                    wire:click="toggleFavorite"
                    aria-label="{{ $isFavorited ? 'Favorilerden çıkar' : 'Favorilere ekle' }}"
                    aria-pressed="{{ $isFavorited ? 'true' : 'false' }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-2 text-xs font-bold transition shrink-0 cursor-pointer {{ $isFavorited ? 'border-rose-300 bg-rose-50 text-rose-600' : 'border-slate-200 bg-white text-slate-600 hover:border-rose-300 hover:text-rose-600' }}"
                >
                    <svg
                        class="h-4 w-4 {{ $isFavorited ? 'fill-rose-600 text-rose-600' : 'fill-none text-current' }}"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                    <span>{{ $isFavorited ? 'Favoride' : 'Favorile' }}</span>
                </button>
            </div>

            <div class="mt-2 flex items-center gap-2 text-sm text-slate-600">
                <span class="text-amber-500">★</span>
                <span class="font-semibold">{{ number_format((float) ($product->approved_reviews_avg_rating ?? 0), 1) }}</span>
                <span class="text-slate-400">({{ $product->approved_reviews_count ?? 0 }} değerlendirme)</span>
            </div>
        </div>

        {{-- Dinamik Fiyat ve Stok Alanı (Livewire ile sayfa yenilenmeden güncellenir) --}}
        @if ($variant)
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 space-y-2">
                <div class="flex flex-wrap items-baseline gap-3">
                    @if ($variant->hasDiscount())
                        <span class="text-3xl font-extrabold text-indigo-700" data-test="variant-price">
                            {{ number_format((float) $variant->discount_price, 2, ',', '.') }} TL
                        </span>
                        <span class="text-base text-slate-400 line-through">
                            {{ number_format((float) $variant->price, 2, ',', '.') }} TL
                        </span>
                    @else
                        <span class="text-3xl font-extrabold text-slate-900" data-test="variant-price">
                            {{ number_format((float) $variant->price, 2, ',', '.') }} TL
                        </span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                    <span class="font-mono text-slate-500">SKU: {{ $variant->sku }}</span>

                    @if ($variant->stock > 0)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 font-bold text-emerald-800" data-test="variant-stock">
                            Stokta var ({{ $variant->stock }} adet)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-3 py-1 font-bold text-rose-800" data-test="variant-stock">
                            Tükendi
                        </span>
                    @endif
                </div>
            </div>
        @endif

        {{-- Varyant Seçimi (Renk / Beden) --}}
        <div class="space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-sm font-bold text-slate-800">Varyant Seçimi (Renk / Beden)</span>
                @if ($variant)
                    <span class="text-xs font-semibold text-indigo-700">Seçili: {{ $variant->label() }}</span>
                @endif
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                @foreach ($product->variants as $item)
                    @php $isSelected = $variant && $variant->id === $item->id; @endphp
                    <button
                        type="button"
                        wire:key="variant-{{ $item->id }}"
                        wire:click="selectVariant({{ $item->id }})"
                        class="rounded-xl border px-3 py-2.5 text-left transition {{ $isSelected ? 'border-indigo-600 bg-indigo-50 ring-2 ring-indigo-200' : 'border-slate-200 bg-white hover:border-indigo-300' }}"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="h-3.5 w-3.5 rounded-full border border-slate-300 shrink-0"
                                style="background-color: {{ $item->swatchHex() }};"
                            ></span>
                            <p class="text-xs font-bold truncate {{ $isSelected ? 'text-indigo-800' : 'text-slate-900' }}">
                                {{ $item->label() }}
                            </p>
                        </div>
                        <p class="mt-1 text-xs text-slate-600">
                            {{ number_format($item->effectivePrice(), 2, ',', '.') }} TL
                        </p>
                        <p class="mt-0.5 text-[11px] {{ $item->stock > 0 ? 'text-emerald-700' : 'text-rose-600 font-semibold' }}">
                            {{ $item->stock > 0 ? $item->stock . ' adet' : 'Stokta yok' }}
                        </p>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Adet ve Sepete Ekle --}}
        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="flex items-center rounded-lg border border-slate-300 bg-white">
                <label for="quantity" class="px-3 text-xs font-semibold text-slate-500">Adet</label>
                <input
                    id="quantity"
                    type="number"
                    min="1"
                    max="{{ max($variant?->stock ?? 1, 1) }}"
                    wire:model.live="quantity"
                    @disabled(! $variant || $variant->stock < 1)
                    class="w-20 border-l border-slate-200 py-2.5 text-center text-sm font-bold text-slate-900 focus:outline-none"
                >
            </div>

            <button
                type="button"
                wire:click="addToCart"
                @disabled(! $variant || $variant->stock < 1)
                class="flex-1 rounded-lg bg-indigo-600 px-6 py-3 text-sm font-bold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-slate-300 transition"
            >
                {{ ($variant && $variant->stock > 0) ? 'Sepete Ekle' : 'Stokta Yok' }}
            </button>
        </div>

        @if ($cartNotice)
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-800 flex flex-wrap items-center justify-between gap-2">
                <span>{{ $cartNotice }}</span>
                <a href="{{ route('cart.index') }}" class="font-bold underline hover:text-emerald-950">Sepete Git →</a>
            </div>
        @endif

        @if ($cartError)
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-medium text-rose-800">
                {{ $cartError }}
            </div>
        @endif

        <div class="mt-auto grid grid-cols-2 gap-3 pt-4 border-t border-slate-100 text-xs text-slate-600">
            <div class="rounded-lg bg-slate-50 p-3">
                <strong class="block text-slate-800">300 TL Üzeri Ücretsiz Kargo</strong>
                Satıcı bazlı avantajlı gönderim
            </div>
            <div class="rounded-lg bg-slate-50 p-3">
                <strong class="block text-slate-800">14 Gün Kolay İade</strong>
                Güvenli pazaryeri koruması
            </div>
        </div>
    </div>
</div>
