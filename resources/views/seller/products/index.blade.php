<x-layouts.storefront title="Ürünlerim — Satıcı Paneli">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('seller.dashboard') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">← Satıcı Paneline Dön</a>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $seller->store_name }} — Ürün Yönetimi</h1>
                <p class="text-sm text-slate-600">Yalnızca kendi mağazanıza ait ürünleri ekleyebilir, düzenleyebilir, stok güncelleyebilir ve aktif/pasif yapabilirsiniz.</p>
            </div>
            <a href="{{ route('seller.products.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                + Yeni Ürün Ekle
            </a>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Ürün</th>
                        <th class="px-4 py-3">Kategori / Marka</th>
                        <th class="px-4 py-3">Fiyat</th>
                        <th class="px-4 py-3">Hızlı Stok</th>
                        <th class="px-4 py-3">Durum</th>
                        <th class="px-4 py-3 text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $product)
                        @php $variant = $product->variants->first(); @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img
                                        src="{{ $product->thumbnailUrl() }}"
                                        alt="{{ $product->name }}"
                                        class="h-11 w-11 rounded-lg object-cover border border-slate-200 shrink-0"
                                    >
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $product->name }}</p>
                                        <p class="text-xs font-mono text-slate-500">{{ $variant?->sku }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600">
                                <p class="font-semibold text-slate-800">{{ $product->category?->name }}</p>
                                <p>{{ $product->brand?->name ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                @if ($variant)
                                    {{ number_format($variant->effectivePrice(), 2, ',', '.') }} TL
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($variant)
                                    <form method="POST" action="{{ route('seller.products.update-stock', $product) }}" class="inline-flex items-center gap-1.5">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="variant_id" value="{{ $variant->id }}">
                                        <input
                                            type="number"
                                            name="stock"
                                            min="0"
                                            max="100000"
                                            value="{{ $variant->stock }}"
                                            class="w-20 rounded-md border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-800"
                                        >
                                        <button type="submit" class="rounded-md bg-slate-800 px-2.5 py-1 text-xs font-semibold text-white hover:bg-slate-700">
                                            Kaydet
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('seller.products.toggle-status', $product) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="submit"
                                        class="rounded-full px-3 py-1 text-xs font-bold transition {{ $product->status === 'active' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}"
                                    >
                                        {{ $product->status === 'active' ? 'Aktif' : 'Pasif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('seller.products.edit', $product) }}" class="rounded-md border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                        Düzenle
                                    </a>
                                    <form method="POST" action="{{ route('seller.products.destroy', $product) }}" onsubmit="return confirm('Bu ürünü silmek istediğinize emin misiniz?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                            Sil
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">Henüz eklenmiş ürününüz bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $products->links() }}
        </div>
    </section>
</x-layouts.storefront>
