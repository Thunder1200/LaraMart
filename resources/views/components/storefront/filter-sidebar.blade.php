@props([
    'action',
    'filters' => [],
    'filterOptions' => [],
    'showCategoryFilter' => true,
])

@php
    $supportsSize = (bool) ($filterOptions['supports_size'] ?? true);
    $attributeFilters = $filterOptions['attribute_filters'] ?? [];
    $selectedAttributes = is_array($filters['ozellik'] ?? null) ? $filters['ozellik'] : [];
    $cleanAction = parse_url($action, PHP_URL_PATH) ?: $action;
@endphp

<form
    method="GET"
    action="{{ $cleanAction }}"
    autocomplete="off"
    x-data="{
        cleanUrl: @js($cleanAction),
        clearFilters() {
            const form = this.$el;
            form.querySelectorAll('select').forEach((select) => {
                select.selectedIndex = 0;
                select.value = '';
            });
            form.querySelectorAll('input').forEach((input) => {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false;
                } else {
                    input.value = '';
                }
            });
            const target = new URL(this.cleanUrl, window.location.origin);
            if (window.location.pathname !== target.pathname || window.location.search !== '' || window.location.hash !== '') {
                window.location.assign(target.pathname);
            }
        }
    }"
    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm space-y-5"
>
    @if (! empty($filters['q']))
        <input type="hidden" name="q" value="{{ $filters['q'] }}">
    @endif
    @if (! empty($filters['sirala']))
        <input type="hidden" name="sirala" value="{{ $filters['sirala'] }}">
    @endif

    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-800">Filtrele</h2>
        <a
            href="{{ $cleanAction }}"
            @click.prevent="clearFilters()"
            data-test="clear-filters-top"
            class="text-xs font-semibold text-rose-600 hover:text-rose-700 hover:underline cursor-pointer"
        >
            Temizle
        </a>
    </div>

    @if ($showCategoryFilter)
        <div>
            <label for="filter-kategori" class="block text-xs font-bold text-slate-700">Kategori</label>
            <select
                id="filter-kategori"
                name="kategori"
                onchange="this.form.submit()"
                class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none"
            >
                <option value="">Tüm Kategoriler</option>
                @foreach ($filterOptions['categories'] ?? [] as $cat)
                    <option value="{{ $cat->slug }}" @selected(($filters['kategori'] ?? '') === $cat->slug)>
                        {{ $cat->parent_id ? '↳ ' : '' }}{{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    {{-- Ortak Filtre: Marka --}}
    <div>
        <label for="filter-marka" class="block text-xs font-bold text-slate-700">Marka</label>
        <select
            id="filter-marka"
            name="marka"
            class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none"
        >
            <option value="">Tüm Markalar</option>
            @foreach ($filterOptions['brands'] ?? [] as $brand)
                <option value="{{ $brand->slug }}" @selected(($filters['marka'] ?? '') === $brand->slug)>
                    {{ $brand->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Ortak Filtre: Fiyat Aralığı --}}
    <div>
        <span class="block text-xs font-bold text-slate-700">Fiyat Aralığı (TL)</span>
        <div class="mt-1.5 grid grid-cols-2 gap-2">
            <input
                type="number"
                name="min"
                min="0"
                step="1"
                value="{{ $filters['min'] ?? '' }}"
                placeholder="Min"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none"
            >
            <input
                type="number"
                name="max"
                min="0"
                step="1"
                value="{{ $filters['max'] ?? '' }}"
                placeholder="Max"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none"
            >
        </div>
    </div>

    {{-- Ortak Filtre: Renk --}}
    @if (! empty($filterOptions['colors']))
        <div>
            <label for="filter-renk" class="block text-xs font-bold text-slate-700">Renk</label>
            <select
                id="filter-renk"
                name="renk"
                class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none"
            >
                <option value="">Tüm Renkler</option>
                @foreach ($filterOptions['colors'] as $color)
                    <option value="{{ $color }}" @selected(($filters['renk'] ?? '') === $color)>{{ $color }}</option>
                @endforeach
            </select>
        </div>
    @endif

    {{-- Yalnızca Giyim / Ayakkabı / Fitness kategorilerinde Beden filtresi --}}
    @if ($supportsSize && ! empty($filterOptions['sizes']))
        <div>
            <label for="filter-beden" class="block text-xs font-bold text-slate-700">Beden / Numara</label>
            <select
                id="filter-beden"
                name="beden"
                class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none"
            >
                <option value="">Tüm Bedenler</option>
                @foreach ($filterOptions['sizes'] as $size)
                    <option value="{{ $size }}" @selected(($filters['beden'] ?? '') === $size)>{{ $size }}</option>
                @endforeach
            </select>
        </div>
    @endif

    {{-- Kategoriye Özel Dinamik Teknik Özellik Filtreleri --}}
    @if (! empty($attributeFilters))
        <div class="border-t border-slate-100 pt-4 space-y-4">
            <p class="text-[11px] font-extrabold uppercase tracking-wider text-indigo-600">Kategoriye Özel Özellikler</p>
            @foreach ($attributeFilters as $attrKey => $config)
                <div>
                    <label for="filter-attr-{{ $attrKey }}" class="block text-xs font-bold text-slate-700">
                        {{ $config['label'] }}
                    </label>
                    <select
                        id="filter-attr-{{ $attrKey }}"
                        name="ozellik[{{ $attrKey }}]"
                        class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none"
                    >
                        <option value="">Tümü</option>
                        @foreach ($config['options'] as $option)
                            <option value="{{ $option }}" @selected(($selectedAttributes[$attrKey] ?? '') === $option)>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Ortak Filtre: Minimum Puan --}}
    <div class="border-t border-slate-100 pt-4">
        <label for="filter-puan" class="block text-xs font-bold text-slate-700">Minimum Puan</label>
        <select
            id="filter-puan"
            name="puan"
            class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none"
        >
            <option value="">Tümü</option>
            <option value="4.5" @selected(($filters['puan'] ?? '') === '4.5')>4.5 ★ ve üzeri</option>
            <option value="4" @selected(($filters['puan'] ?? '') === '4')>4.0 ★ ve üzeri</option>
            <option value="3.5" @selected(($filters['puan'] ?? '') === '3.5')>3.5 ★ ve üzeri</option>
            <option value="3" @selected(($filters['puan'] ?? '') === '3')>3.0 ★ ve üzeri</option>
        </select>
    </div>

    <div class="space-y-2">
        <button
            type="submit"
            class="w-full rounded-lg bg-indigo-600 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 transition cursor-pointer"
        >
            Filtreleri Uygula
        </button>

        <a
            href="{{ $cleanAction }}"
            @click.prevent="clearFilters()"
            data-test="clear-filters-bottom"
            class="block w-full rounded-lg border border-slate-300 bg-white py-2 text-center text-sm font-semibold text-slate-700 hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700 transition cursor-pointer"
        >
            Filtreleri Temizle
        </a>
    </div>
</form>
