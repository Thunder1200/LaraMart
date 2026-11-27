<x-layouts.storefront title="LaraMart — Çok Satıcılı E-Ticaret Pazaryeri">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">
        {{-- 1. Banner Slider --}}
        <section
            aria-label="Kampanya Bannerları"
            x-data="{
                active: 0,
                total: {{ max($banners->count(), 1) }},
                next() { this.active = (this.active + 1) % this.total; },
                prev() { this.active = (this.active - 1 + this.total) % this.total; }
            }"
            x-init="if (total > 1) setInterval(() => next(), 6000)"
            class="relative overflow-hidden rounded-2xl shadow-lg"
        >
            @php
                $gradients = [
                    'from-indigo-700 via-indigo-600 to-violet-600',
                    'from-emerald-700 via-teal-600 to-cyan-600',
                    'from-slate-900 via-indigo-900 to-indigo-700',
                ];
            @endphp

            @forelse ($banners as $index => $banner)
                <div
                    x-show="active === {{ $index }}"
                    x-transition.opacity.duration.300ms
                    class="bg-gradient-to-r {{ $gradients[$index % count($gradients)] }} px-8 py-12 sm:px-14 sm:py-16 text-white"
                >
                    <div class="max-w-2xl space-y-4">
                        <span class="inline-block rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider">
                            LaraMart Kampanya #{{ $index + 1 }}
                        </span>
                        <h1 class="text-3xl sm:text-4xl font-extrabold leading-tight">
                            {{ $banner->title }}
                        </h1>
                        <p class="text-white/85 text-sm sm:text-base">
                            Onaylı satıcılardan binlerce varyantlı ürün, güvenli ödeme ve hızlı teslimat avantajıyla LaraMart'ta.
                        </p>
                        @if ($banner->url)
                            <div class="pt-2">
                                <a href="{{ $banner->url }}" class="inline-flex items-center gap-2 rounded-lg bg-white dark:bg-white px-5 py-2.5 text-sm font-bold text-slate-900 dark:text-slate-900 hover:bg-slate-100 dark:hover:bg-slate-100 transition">
                                    Kampanyayı Keşfet →
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-gradient-to-r from-indigo-700 to-violet-600 px-8 py-14 text-white">
                    <h1 class="text-3xl font-extrabold">LaraMart Çok Satıcılı Pazaryeri</h1>
                    <p class="mt-2 text-indigo-100">Binlerce ürün ve onaylı satıcı tek platformda.</p>
                </div>
            @endforelse

            @if ($banners->count() > 1)
                <button
                    type="button"
                    @click="prev()"
                    aria-label="Önceki banner"
                    class="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-black/25 p-2 text-white hover:bg-black/45 transition"
                >
                    ‹
                </button>
                <button
                    type="button"
                    @click="next()"
                    aria-label="Sonraki banner"
                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-black/25 p-2 text-white hover:bg-black/45 transition"
                >
                    ›
                </button>

                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-2">
                    @foreach ($banners as $index => $banner)
                        <button
                            type="button"
                            @click="active = {{ $index }}"
                            :class="active === {{ $index }} ? 'w-7 bg-white' : 'w-2.5 bg-white/50'"
                            class="h-2.5 rounded-full transition-all"
                            aria-label="Banner {{ $index + 1 }}"
                        ></button>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- 2. Öne Çıkan Kategoriler --}}
        <section aria-label="Öne Çıkan Kategoriler" class="space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Öne Çıkan Kategoriler</h2>
                    <p class="text-sm text-slate-600">İlgilendiğiniz kategoriye tıklayarak ürünleri inceleyin.</p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach ($featuredCategories as $category)
                    <a
                        href="{{ route('categories.show', $category->slug) }}"
                        class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:border-indigo-400 hover:shadow-md transition flex flex-col justify-between"
                    >
                        <div>
                            <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 font-extrabold text-lg group-hover:bg-indigo-600 group-hover:text-white transition">
                                {{ mb_substr($category->name, 0, 1) }}
                            </div>
                            <h3 class="mt-3 font-bold text-slate-900 group-hover:text-indigo-700">{{ $category->name }}</h3>
                            @if ($category->children->isNotEmpty())
                                <p class="mt-1 text-xs text-slate-500 line-clamp-2">
                                    {{ $category->children->pluck('name')->implode(', ') }}
                                </p>
                            @endif
                        </div>
                        <span class="mt-4 inline-block text-xs font-semibold text-indigo-600">Ürünleri Gör →</span>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- 3. Çok Satanlar --}}
        <section aria-label="Çok Satanlar" class="space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Çok Satanlar</h2>
                    <p class="text-sm text-slate-600">Müşterilerin en çok tercih ettiği ürünler</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($bestSellers as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>
        </section>

        {{-- 4. Yeni Gelenler --}}
        <section aria-label="Yeni Gelenler" class="space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Yeni Gelenler</h2>
                    <p class="text-sm text-slate-600">Mağazalarımıza yeni eklenen en güncel ürünler</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($newArrivals as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>
        </section>
    </div>
</x-layouts.storefront>
