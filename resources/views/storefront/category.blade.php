<x-layouts.storefront :title="$category->name . ' — LaraMart'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        {{-- Breadcrumb --}}
        <nav aria-label="Sayfa Yolu" class="text-xs text-slate-500 flex flex-wrap items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-indigo-700">Ana Sayfa</a>
            <span>/</span>
            @if ($category->parent)
                <a href="{{ route('categories.show', $category->parent->slug) }}" class="hover:text-indigo-700">{{ $category->parent->name }}</a>
                <span>/</span>
            @endif
            <span class="font-semibold text-slate-800">{{ $category->name }}</span>
        </nav>

        {{-- Başlık ve Sıralama --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $category->name }}</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Toplam <strong>{{ $products->total() }}</strong> ürün listeleniyor
                </p>
            </div>

            <form method="GET" action="{{ route('categories.show', $category->slug) }}" class="flex items-center gap-2">
                @foreach (['marka', 'min', 'max', 'renk', 'beden', 'puan'] as $param)
                    @if (! empty($filters[$param]))
                        <input type="hidden" name="{{ $param }}" value="{{ $filters[$param] }}">
                    @endif
                @endforeach
                @foreach (is_array($filters['ozellik'] ?? null) ? $filters['ozellik'] : [] as $attrKey => $attrVal)
                    @if ($attrVal !== null && $attrVal !== '')
                        <input type="hidden" name="ozellik[{{ $attrKey }}]" value="{{ $attrVal }}">
                    @endif
                @endforeach
                <label for="sirala" class="text-xs font-semibold text-slate-600">Sırala:</label>
                <select
                    id="sirala"
                    name="sirala"
                    onchange="this.form.submit()"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                >
                    <option value="en-yeni" @selected($sort === 'en-yeni')>En Yeni</option>
                    <option value="fiyat-artan" @selected($sort === 'fiyat-artan')>Fiyat: Düşükten Yükseğe</option>
                    <option value="fiyat-azalan" @selected($sort === 'fiyat-azalan')>Fiyat: Yüksekten Düşüğe</option>
                    <option value="en-cok-degerlendirilen" @selected($sort === 'en-cok-degerlendirilen')>En Çok Değerlendirilen</option>
                </select>
            </form>
        </div>

        {{-- Alt Kategoriler --}}
        @if ($category->children->isNotEmpty())
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold text-slate-500 mr-1">Alt Kategoriler:</span>
                @foreach ($category->children as $child)
                    <a
                        href="{{ route('categories.show', $child->slug) }}"
                        class="rounded-full border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 hover:border-indigo-400 hover:text-indigo-700 transition"
                    >
                        {{ $child->name }}
                    </a>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <aside class="lg:col-span-3">
                <x-storefront.filter-sidebar
                    :action="route('categories.show', $category->slug)"
                    :filters="$filters"
                    :filter-options="$filterOptions"
                    :show-category-filter="false"
                />
            </aside>

            <div class="lg:col-span-9 space-y-6">
                @if ($products->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach ($products as $product)
                            <x-storefront.product-card :product="$product" />
                        @endforeach
                    </div>

                    <div class="pt-4">
                        {{ $products->links() }}
                    </div>
                @else
                    <div class="rounded-xl border border-slate-200 bg-white p-12 text-center">
                        <p class="text-base font-semibold text-slate-700">Bu kategoride seçilen filtrelere uygun ürün bulunmuyor.</p>
                        <a href="{{ route('categories.show', $category->slug) }}" class="mt-4 inline-block rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Filtreleri Temizle
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.storefront>
