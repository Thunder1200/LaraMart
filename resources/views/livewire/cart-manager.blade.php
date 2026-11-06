<div class="space-y-6">
    @if($statusMessage)
        <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-lg text-sm">
            {{ $statusMessage }}
        </div>
    @endif

    @if($errorMessage)
        <div class="bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-lg text-sm">
            {{ $errorMessage }}
        </div>
    @endif

    @if(empty($summary['seller_groups']))
        <div class="bg-white rounded-xl p-10 text-center border border-slate-200">
            <h2 class="text-xl font-bold text-slate-800 mb-2">Sepetinizde henüz ürün bulunmuyor.</h2>
            <p class="text-slate-500 mb-6">LaraMart mağazalarındaki binlerce ürünü keşfetmeye hemen başlayın.</p>
            <a href="{{ route('catalog.search') }}" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-6 py-3 rounded-lg inline-block">
                Alışverişe Başla
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                @foreach($summary['seller_groups'] as $group)
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-slate-100 px-6 py-3 flex items-center justify-between border-b border-slate-200">
                            <div>
                                <span class="text-xs text-slate-500 uppercase font-bold">Satıcı Mağaza:</span>
                                <a href="{{ route('catalog.seller', $group['slug']) }}" class="font-bold text-slate-900 hover:underline ml-1">
                                    {{ $group['store_name'] }}
                                </a>
                            </div>
                            <div class="text-xs font-semibold {{ $group['shipping'] == 0 ? 'text-emerald-700' : 'text-slate-600' }}">
                                {{ $group['shipping'] == 0 ? 'Kargo Bedava (300 TL Üstü)' : 'Kargo: ' . number_format($group['shipping'], 2) . ' TL' }}
                            </div>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @foreach($group['items'] as $item)
                                <div class="p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                    <div>
                                        <a href="{{ route('catalog.product', $item['product_slug']) }}" class="font-bold text-slate-900 hover:text-amber-600">
                                            {{ $item['product_name'] }}
                                        </a>
                                        <p class="text-xs text-slate-500 mt-1">
                                            Varyant: {{ $item['color'] }} / {{ $item['size'] }} | Birim: {{ $item['unit_price'] }} TL
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <button
                                            type="button"
                                            wire:click="updateQuantity({{ $item['cart_item_id'] }}, {{ $item['quantity'] - 1 }})"
                                            class="w-8 h-8 rounded bg-slate-200 hover:bg-slate-300 font-bold"
                                        >-</button>
                                        <span class="font-semibold w-8 text-center">{{ $item['quantity'] }}</span>
                                        <button
                                            type="button"
                                            wire:click="updateQuantity({{ $item['cart_item_id'] }}, {{ $item['quantity'] + 1 }})"
                                            class="w-8 h-8 rounded bg-slate-200 hover:bg-slate-300 font-bold"
                                        >+</button>

                                        <span class="font-bold text-slate-900 min-w-24 text-right">{{ $item['line_total'] }} TL</span>

                                        <button
                                            type="button"
                                            wire:click="removeItem({{ $item['cart_item_id'] }})"
                                            class="text-rose-600 hover:text-rose-800 text-xs font-semibold ml-2"
                                        >Sil</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
                    <h3 class="text-lg font-bold border-b pb-3">Sipariş Özeti</h3>

                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Ara Toplam</span>
                        <span class="font-semibold">{{ $summary['subtotal'] }} TL</span>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Kargo Toplamı</span>
                        <span class="font-semibold">{{ $summary['shipping'] }} TL</span>
                    </div>

                    <div class="flex justify-between text-sm text-emerald-700">
                        <span>Kupon İndirimi</span>
                        <span class="font-semibold">-{{ $summary['discount'] }} TL</span>
                    </div>

                    <div class="border-t pt-3 flex justify-between text-lg font-extrabold text-slate-900">
                        <span>Genel Toplam</span>
                        <span>{{ $summary['total'] }} TL</span>
                    </div>

                    <div class="pt-3 border-t space-y-2">
                        <label class="block text-xs font-semibold text-slate-600">İndirim Kuponu</label>
                        <div class="flex gap-2">
                            <input
                                type="text"
                                wire:model="couponCode"
                                placeholder="Örn: MERHABA10"
                                class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm"
                            >
                            <button
                                type="button"
                                wire:click="applyCouponCode"
                                class="bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold px-4 py-2 rounded-lg"
                            >Uygula</button>
                        </div>
                        @if($summary['coupon_code'])
                            <div class="flex items-center justify-between text-xs text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded">
                                <span>Aktif Kupon: <strong>{{ $summary['coupon_code'] }}</strong></span>
                                <button type="button" wire:click="removeCoupon" class="text-rose-600 font-bold">Kaldır</button>
                            </div>
                        @endif
                    </div>

                    <a href="{{ route('checkout.index') }}" class="block w-full text-center bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-3 rounded-lg mt-4">
                        Alışverişi Tamamla (Checkout)
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

