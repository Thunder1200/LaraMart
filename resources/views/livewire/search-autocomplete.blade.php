<div class="relative">
    <form action="{{ route('catalog.search') }}" method="GET" class="flex">
        <input
            type="text"
            name="q"
            wire:model.live.debounce.250ms="query"
            placeholder="Ürün, kategori veya marka ara..."
            class="w-full rounded-l-lg px-4 py-2 text-slate-900 text-sm focus:outline-none"
        >
        <button type="submit" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold px-4 py-2 rounded-r-lg text-sm">
            Ara
        </button>
    </form>

    @if($suggestions->isNotEmpty())
        <div class="absolute left-0 right-0 mt-1 bg-white text-slate-900 rounded-lg shadow-xl border border-slate-200 z-50 divide-y divide-slate-100">
            @foreach($suggestions as $item)
                <a href="{{ route('catalog.product', $item->slug) }}" class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-50 text-sm">
                    <div>
                        <span class="font-semibold">{{ $item->name }}</span>
                        @if($item->brand)
                            <span class="text-xs text-slate-500 ml-1">({{ $item->brand->name }})</span>
                        @endif
                    </div>
                    <span class="text-amber-600 font-bold">
                        {{ $item->variants->first()?->effectivePrice() ?? '0.00' }} TL
                    </span>
                </a>
            @endforeach
        </div>
    @endif
</div>

