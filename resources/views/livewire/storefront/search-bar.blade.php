@php
    $suggestions = $this->suggestions;
    $hasResults = $suggestions['products']->isNotEmpty() || $suggestions['brands']->isNotEmpty();
@endphp

<div
    class="relative flex-1 max-w-2xl"
    x-data="{ open: @entangle('showSuggestions') }"
    @click.outside="open = false"
>
    <form wire:submit="search" class="relative flex items-center">
        <input
            type="search"
            name="q"
            wire:model.live.debounce.250ms="query"
            @focus="if ($wire.query.trim().length >= 2) open = true"
            autocomplete="off"
            placeholder="Ürün veya marka ara... (yazarken öneri)"
            class="w-full rounded-lg border border-slate-300 bg-slate-50 py-2.5 pl-4 pr-24 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-200"
        >
        <button
            type="submit"
            class="absolute right-1.5 rounded-md bg-indigo-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-indigo-700 transition"
        >
            Ara
        </button>
    </form>

    @if ($showSuggestions && mb_strlen(trim($query)) >= 2)
        <div
            x-show="open"
            x-transition
            class="absolute left-0 right-0 top-full mt-1.5 rounded-xl border border-slate-200 bg-white shadow-xl z-50 overflow-hidden"
        >
            @if ($hasResults)
                @if ($suggestions['brands']->isNotEmpty())
                    <div class="border-b border-slate-100 px-4 py-2.5">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Markalar</p>
                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                            @foreach ($suggestions['brands'] as $brand)
                                <a
                                    href="{{ route('search', ['marka' => $brand->slug]) }}"
                                    class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100"
                                >
                                    {{ $brand->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($suggestions['products']->isNotEmpty())
                    <div class="divide-y divide-slate-100">
                        @foreach ($suggestions['products'] as $product)
                            @php $variant = $product->displayVariant(); @endphp
                            <a
                                href="{{ route('products.show', $product->slug) }}"
                                class="flex items-center justify-between gap-3 px-4 py-2.5 hover:bg-slate-50 transition"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <img
                                        src="{{ $product->thumbnailUrl() }}"
                                        alt="{{ $product->name }}"
                                        class="h-10 w-10 rounded-lg object-cover border border-slate-200 shrink-0"
                                    >
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-slate-900 truncate">{{ $product->name }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ $product->brand?->name ?? 'LaraMart' }}</p>
                                    </div>
                                </div>
                                @if ($variant)
                                    <span class="text-xs font-bold text-indigo-700 shrink-0">
                                        {{ number_format($variant->effectivePrice(), 2, ',', '.') }} TL
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif

                <a
                    href="{{ route('search', ['q' => trim($query)]) }}"
                    class="block bg-slate-50 px-4 py-2.5 text-center text-xs font-bold text-indigo-700 hover:bg-indigo-50 border-t border-slate-100"
                >
                    "{{ trim($query) }}" için tüm sonuçları gör →
                </a>
            @else
                <div class="px-4 py-4 text-center text-xs text-slate-500">
                    "{{ trim($query) }}" ile eşleşen ürün veya marka bulunamadı.
                </div>
            @endif
        </div>
    @endif
</div>
