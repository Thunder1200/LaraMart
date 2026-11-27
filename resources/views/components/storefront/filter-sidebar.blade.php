@props([
    'action',
    'filters' => [],
    'filterOptions' => [],
    'showCategoryFilter' => true,
])

<form method="GET" action="{{ $action }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm space-y-5">
    @if (! empty($filters['q']))
        <input type="hidden" name="q" value="{{ $filters['q'] }}">
    @endif
    @if (! empty($filters['sirala']))
        <input type="hidden" name="sirala" value="{{ $filters['sirala'] }}">
    @endif

    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-800">Filtrele</h2>
        <a href="{{ $action }}" class="text-xs font-semibold text-rose-600 hover:underline">Temizle</a>
    </div>

    @if ($showCategoryFilter)
        <div>
            <label for="filter-kategori" class="block text-xs font-bold text-slate-700">Kategori</label>
            <select
                id="filter-kategori"
                name="kategori"
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

    <div>
        <label for="filter-renk" class="block text-xs font-bold text-slate-700">Renk</label>
        <select
            id="filter-renk"
            name="renk"
            class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none"
        >
            <option value="">Tüm Renkler</option>
            @foreach ($filterOptions['colors'] ?? [] as $color)
                <option value="{{ $color }}" @selected(($filters['renk'] ?? '') === $color)>{{ $color }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="filter-beden" class="block text-xs font-bold text-slate-700">Beden</label>
        <select
            id="filter-beden"
            name="beden"
            class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none"
        >
            <option value="">Tüm Bedenler</option>
            @foreach ($filterOptions['sizes'] ?? [] as $size)
                <option value="{{ $size }}" @selected(($filters['beden'] ?? '') === $size)>{{ $size }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="filter-puan" class="block text-xs font-bold text-slate-700">Minimum Puan</label>
        <select
            id="filter-puan"
            name="puan"
            class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none"
        >
            <option value="">Tümü</option>
            <option value="4" @selected(($filters['puan'] ?? '') === '4')>4 ★ ve üzeri</option>
            <option value="3" @selected(($filters['puan'] ?? '') === '3')>3 ★ ve üzeri</option>
            <option value="2" @selected(($filters['puan'] ?? '') === '2')>2 ★ ve üzeri</option>
        </select>
    </div>

    <button
        type="submit"
        class="w-full rounded-lg bg-indigo-600 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 transition"
    >
        Filtreleri Uygula
    </button>
</form>
