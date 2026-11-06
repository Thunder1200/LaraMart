<div class="bg-slate-50 border border-slate-200 rounded-xl p-6 space-y-5">
    <div class="flex items-baseline gap-3">
        <span class="text-3xl font-extrabold text-slate-900" data-testid="livewire-price">{{ $currentPrice }} TL</span>
        @if($originalPrice && (float)$originalPrice > (float)$currentPrice)
            <span class="text-lg text-slate-400 line-through">{{ $originalPrice }} TL</span>
        @endif
    </div>

    <div class="flex items-center gap-4 text-sm">
        <span class="px-3 py-1 rounded-full font-semibold {{ $currentStock > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}" data-testid="livewire-stock">
            {{ $currentStock > 0 ? "Stokta ({$currentStock} Adet)" : 'Tükendi' }}
        </span>
        <span class="text-slate-500">SKU: <strong>{{ $sku }}</strong></span>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">Varyant Seçimi (Renk / Beden):</label>
        <div class="flex flex-wrap gap-2">
            @foreach($variants as $variant)
                <button
                    type="button"
                    wire:click="selectVariant({{ $variant->id }})"
                    class="px-4 py-2 rounded-lg border text-sm font-medium transition {{ $selectedVariantId === $variant->id ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-700 border-slate-300 hover:border-slate-500' }}"
                >
                    {{ $variant->color }} - {{ $variant->size }} ({{ $variant->effectivePrice() }} TL)
                </button>
            @endforeach
        </div>
    </div>

    <div class="flex items-center gap-3 pt-2">
        <input
            type="number"
            min="1"
            max="{{ max(1, $currentStock) }}"
            wire:model="quantity"
            class="w-20 border border-slate-300 rounded-lg px-3 py-2 text-center font-semibold"
        >
        <button
            type="button"
            wire:click="addToCart"
            @disabled($currentStock <= 0)
            class="flex-1 bg-amber-500 hover:bg-amber-400 disabled:bg-slate-300 text-slate-950 font-bold py-3 px-6 rounded-lg transition"
        >
            Sepete Ekle
        </button>
    </div>

    @if($feedbackMessage)
        <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-2.5 rounded-lg text-sm font-medium">
            {{ $feedbackMessage }}
        </div>
    @endif

    @if($errorMessage)
        <div class="bg-rose-50 border border-rose-300 text-rose-800 px-4 py-2.5 rounded-lg text-sm font-medium">
            {{ $errorMessage }}
        </div>
    @endif
</div>

