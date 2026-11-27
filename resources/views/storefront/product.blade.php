<x-layouts.storefront :title="$product->name . ' — LaraMart'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">
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

        {{-- Ürün Açıklaması --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">Ürün Açıklaması</h2>
            <div class="mt-3 text-sm leading-relaxed text-slate-700 whitespace-pre-line">
                {{ $product->description ?: 'Bu ürün için detaylı açıklama yakında eklenecektir.' }}
            </div>
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
