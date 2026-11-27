<x-layouts.storefront :title="$product->name . ' — LaraMart'">
    @php
        $specs = $product->formattedAttributes();
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">
        {{-- Breadcrumb --}}
        <nav aria-label="Sayfa Yolu" class="text-xs text-slate-500 flex flex-wrap items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-indigo-700">Ana Sayfa</a>
            @if ($product->category?->parent)
                <span>/</span>
                <a href="{{ route('categories.show', $product->category->parent->slug) }}" class="hover:text-indigo-700">
                    {{ $product->category->parent->name }}
                </a>
            @endif
            @if ($product->category)
                <span>/</span>
                <a href="{{ route('categories.show', $product->category->slug) }}" class="hover:text-indigo-700">
                    {{ $product->category->name }}
                </a>
            @endif
            <span>/</span>
            <span class="font-semibold text-slate-800">{{ $product->name }}</span>
        </nav>

        {{-- Livewire Ürün Detay & Varyant Seçimi --}}
        <livewire:storefront.product-detail :product="$product" />

        {{-- Ürün Açıklaması & Teknik Özellikler --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <section class="{{ $specs !== [] ? 'lg:col-span-7' : 'lg:col-span-12' }} rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Ürün Açıklaması</h2>
                <div class="mt-3 text-sm leading-relaxed text-slate-700 whitespace-pre-line">
                    {{ $product->description ?: 'Bu ürün için detaylı açıklama yakında eklenecektir.' }}
                </div>
            </section>

            @if ($specs !== [])
                <section class="lg:col-span-5 rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900">Teknik Özellikler</h2>
                    <dl class="mt-4 divide-y divide-slate-100 text-sm">
                        @if ($product->brand)
                            <div class="py-2.5 flex items-center justify-between gap-4">
                                <dt class="text-slate-500">Marka</dt>
                                <dd class="font-semibold text-slate-900 text-right">{{ $product->brand->name }}</dd>
                            </div>
                        @endif
                        @foreach ($specs as $label => $value)
                            <div class="py-2.5 flex items-center justify-between gap-4">
                                <dt class="text-slate-500">{{ $label }}</dt>
                                <dd class="font-semibold text-slate-900 text-right">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endif
        </div>

        {{-- Onaylı Müşteri Değerlendirmeleri --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Müşteri Değerlendirmeleri</h2>
                    <p class="text-xs text-slate-500">Yalnızca doğrulanmış satın alma yapan müşterilerin onaylı yorumları</p>
                </div>
                <div class="flex items-center gap-2 rounded-xl bg-amber-50 border border-amber-200 px-4 py-2">
                    <span class="text-xl text-amber-500">★</span>
                    <span class="text-lg font-extrabold text-slate-900">{{ number_format((float) ($product->approved_reviews_avg_rating ?? 0), 1) }}</span>
                    <span class="text-xs text-slate-600">({{ $product->approved_reviews_count ?? 0 }} değerlendirme)</span>
                </div>
            </div>

            @if ($product->approvedReviews->isNotEmpty())
                <div class="divide-y divide-slate-100">
                    @foreach ($product->approvedReviews as $review)
                        <article class="py-4 space-y-1.5">
                            <div class="flex items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900">{{ $review->user?->name ?? 'Müşteri' }}</span>
                                    <span class="text-amber-500 font-bold">
                                        {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                                    </span>
                                </div>
                                <span class="text-slate-400">{{ $review->created_at->format('d.m.Y') }}</span>
                            </div>
                            <p class="text-sm text-slate-700">{{ $review->comment }}</p>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500">Bu ürün için henüz onaylı değerlendirme bulunmuyor.</p>
            @endif
        </section>

        {{-- Benzer Ürünler --}}
        @if ($relatedProducts->isNotEmpty())
            <section class="space-y-5">
                <h2 class="text-xl font-extrabold text-slate-900">Benzer Ürünler</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($relatedProducts as $related)
                        <x-storefront.product-card :product="$related" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.storefront>
