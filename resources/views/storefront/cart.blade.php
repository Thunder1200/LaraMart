<x-layouts.storefront title="Sepetim — LaraMart">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Alışveriş Sepetim</h1>
                <p class="text-sm text-slate-600">
                    Ürünler satıcıya göre gruplanır; her satıcı için <strong>300 TL ve üzeri</strong> alışverişte kargo ücretsizdir.
                </p>
            </div>
            <a href="{{ route('search') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                Alışverişe Devam Et →
            </a>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error') || $couponError)
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                {{ session('error') ?? $couponError }}
            </div>
        @endif

        @if (count($summary['groups']) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                {{-- Sol: Satıcıya Göre Gruplanmış Sepet Kalemleri --}}
                <div class="lg:col-span-8 space-y-6">
                    @foreach ($summary['groups'] as $group)
                        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                            <div class="bg-slate-50 border-b border-slate-200 px-5 py-3.5 flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="text-slate-500">Satıcı:</span>
                                    <a href="{{ route('stores.show', $group['seller_slug']) }}" class="font-bold text-indigo-700 hover:underline">
                                        {{ $group['seller_name'] }}
                                    </a>
                                </div>

                                @if ($group['shipping'] === 0.0)
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                                        Kargo Bedava
                                    </span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                        Kargo: {{ number_format($group['shipping'], 2, ',', '.') }} TL • Ücretsiz kargo için {{ number_format($group['free_shipping_remaining'], 2, ',', '.') }} TL daha ekleyin
                                    </span>
                                @endif
                            </div>

                            <div class="divide-y divide-slate-100">
                                @foreach ($group['items'] as $item)
                                    @php
                                        $product = $item->variant->product;
                                        $unitPrice = $item->variant->effectivePrice();
                                    @endphp
                                    <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div class="flex items-center gap-4 min-w-0">
                                            <a href="{{ route('products.show', $product->slug) }}" class="shrink-0">
                                                <img
                                                    src="{{ $product->thumbnailUrl() }}"
                                                    alt="{{ $product->name }}"
                                                    class="h-20 w-20 rounded-xl object-cover border border-slate-200"
                                                >
                                            </a>
                                            <div class="min-w-0 space-y-1">
                                                <a href="{{ route('products.show', $product->slug) }}" class="text-sm font-bold text-slate-900 hover:text-indigo-700 line-clamp-2">
                                                    {{ $product->name }}
                                                </a>
                                                <p class="text-xs text-slate-500">
                                                    Varyant: <strong class="text-slate-700">{{ $item->variant->label() }}</strong> • Stok: {{ $item->variant->stock }} adet
                                                </p>
                                                <p class="text-sm font-extrabold text-indigo-700">
                                                    {{ number_format($unitPrice, 2, ',', '.') }} TL
                                                    <span class="text-xs font-normal text-slate-500">/ adet</span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between sm:justify-end gap-3">
                                            <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center gap-1.5">
                                                @csrf
                                                @method('PATCH')
                                                <input
                                                    type="number"
                                                    name="quantity"
                                                    min="1"
                                                    max="{{ $item->variant->stock }}"
                                                    value="{{ $item->quantity }}"
                                                    aria-label="Ürün adedi"
                                                    class="w-16 rounded-lg border border-slate-300 px-2 py-1.5 text-center text-sm font-bold text-slate-900"
                                                >
                                                <button type="submit" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                                    Güncelle
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('cart.destroy', $item) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                                    Sil
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="bg-slate-50 border-t border-slate-100 px-5 py-3 flex items-center justify-between text-xs text-slate-600">
                                <span>Mağaza Ara Toplamı: <strong class="text-slate-900">{{ number_format($group['subtotal'], 2, ',', '.') }} TL</strong></span>
                                <span>Mağaza Kargo: <strong class="text-slate-900">{{ $group['shipping'] === 0.0 ? 'Ücretsiz' : number_format($group['shipping'], 2, ',', '.') . ' TL' }}</strong></span>
                            </div>
                        </section>
                    @endforeach
                </div>

                {{-- Sağ: Kupon ve Sipariş Özeti --}}
                <aside class="lg:col-span-4 space-y-5">
                    {{-- Kupon Kodu --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-3">
                        <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-800">İndirim Kuponu</h2>

                        @if ($appliedCoupon)
                            <div class="flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-xs">
                                <div>
                                    <span class="font-bold text-emerald-800">{{ $appliedCoupon->code }}</span>
                                    <span class="block text-emerald-700">
                                        {{ $appliedCoupon->type === 'percentage' ? '%' . number_format((float) $appliedCoupon->value, 0) . ' indirim' : number_format((float) $appliedCoupon->value, 2, ',', '.') . ' TL indirim' }}
                                    </span>
                                </div>
                                <form method="POST" action="{{ route('cart.coupon.remove') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-bold text-rose-600 hover:underline">Kaldır</button>
                                </form>
                            </div>
                        @else
                            <form method="POST" action="{{ route('cart.coupon.apply') }}" class="flex gap-2">
                                @csrf
                                <input
                                    type="text"
                                    name="code"
                                    value="{{ old('code') }}"
                                    placeholder="Örn: HOSGELDIN10"
                                    required
                                    class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm uppercase text-slate-900 focus:border-indigo-500 focus:outline-none"
                                >
                                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800 transition">
                                    Uygula
                                </button>
                            </form>
                            @error('code')
                                <p class="text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    {{-- Sipariş Özeti --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                        <h2 class="text-base font-extrabold text-slate-900">Sipariş Özeti</h2>

                        <dl class="space-y-2.5 text-sm">
                            <div class="flex items-center justify-between text-slate-600">
                                <dt>Ürünler Ara Toplam ({{ $summary['item_count'] }} adet)</dt>
                                <dd class="font-semibold text-slate-900">{{ number_format($summary['subtotal'], 2, ',', '.') }} TL</dd>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <dt>Toplam Kargo ({{ count($summary['groups']) }} satıcı)</dt>
                                <dd class="font-semibold text-slate-900">
                                    {{ $summary['shipping'] === 0.0 ? 'Ücretsiz' : number_format($summary['shipping'], 2, ',', '.') . ' TL' }}
                                </dd>
                            </div>
                            @if ($summary['discount'] > 0)
                                <div class="flex items-center justify-between text-emerald-700">
                                    <dt>Kupon İndirimi</dt>
                                    <dd class="font-bold">-{{ number_format($summary['discount'], 2, ',', '.') }} TL</dd>
                                </div>
                            @endif
                            <div class="border-t border-slate-200 pt-3 flex items-center justify-between text-base">
                                <dt class="font-extrabold text-slate-900">Genel Toplam</dt>
                                <dd class="text-xl font-extrabold text-indigo-700">{{ number_format($summary['total'], 2, ',', '.') }} TL</dd>
                            </div>
                        </dl>

                        @guest
                            <div class="rounded-lg bg-amber-50 border border-amber-200 px-3.5 py-2.5 text-xs text-amber-800">
                                Misafir sepetiniz güvende! Giriş yaptığınızda bu ürünler otomatik olarak hesabınızdaki sepetle birleşecektir.
                            </div>
                            <a href="{{ route('login') }}" class="block w-full rounded-lg bg-indigo-600 py-3 text-center text-sm font-bold text-white hover:bg-indigo-700 transition">
                                Satın Almak İçin Giriş Yap
                            </a>
                        @else
                            <a href="{{ route('checkout.index') }}" class="block w-full rounded-lg bg-indigo-600 py-3 text-center text-sm font-bold text-white hover:bg-indigo-700 transition shadow">
                                Ödemeye Geç →
                            </a>
                        @endguest
                    </div>
                </aside>
            </div>
        @else
            <div class="rounded-2xl border border-slate-200 bg-white p-14 text-center space-y-4">
                <p class="text-lg font-bold text-slate-800">Sepetiniz şu an boş.</p>
                <p class="text-sm text-slate-500">Beğendiğiniz ürünleri keşfedip sepetinize ekleyebilirsiniz.</p>
                <a href="{{ route('home') }}" class="inline-block rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 transition">
                    Alışverişe Başla
                </a>
            </div>
        @endif
    </div>
</x-layouts.storefront>
