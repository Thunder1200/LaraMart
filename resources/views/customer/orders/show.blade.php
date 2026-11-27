<x-layouts.storefront :title="'Sipariş Detayı ' . $order->order_number . ' — LaraMart'">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
                <a href="{{ route('customer.orders.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">← Siparişlerime Dön</a>
                <h1 class="mt-1 text-2xl font-extrabold text-slate-900">Sipariş #{{ $order->order_number }}</h1>
                <p class="text-sm text-slate-600">Sipariş Tarihi: {{ $order->created_at->format('d.m.Y H:i') }} • Ödeme Durumu: <strong class="text-emerald-700">Ödendi</strong></p>
            </div>
            <span class="rounded-full bg-indigo-100 px-3.5 py-1.5 text-xs font-bold text-indigo-800">
                {{ $order->subOrders->count() }} Alt Sipariş (Satıcı Paketi)
            </span>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Sol: Satıcı Bazlı Alt Siparişler --}}
            <div class="lg:col-span-8 space-y-5">
                @foreach ($order->subOrders as $subOrder)
                    <article class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="bg-slate-50 border-b border-slate-200 px-5 py-3.5 flex flex-wrap items-center justify-between gap-2 text-sm">
                            <div>
                                <span class="text-slate-500">Satıcı Paketi:</span>
                                <strong class="text-indigo-700">{{ $subOrder->seller->store_name }}</strong>
                            </div>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">
                                {{ $subOrder->status === 'pending_approval' ? 'Onay Bekliyor' : ucfirst($subOrder->status) }}
                            </span>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @foreach ($subOrder->items as $item)
                                <div class="px-5 py-4 flex items-center justify-between gap-4 text-sm">
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $item->product_name }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ $item->quantity }} adet × {{ number_format((float) $item->price, 2, ',', '.') }} TL
                                        </p>
                                    </div>
                                    <p class="font-extrabold text-slate-900">
                                        {{ number_format((float) $item->price * $item->quantity, 2, ',', '.') }} TL
                                    </p>
                                </div>
                            @endforeach
                        </div>

                        <div class="bg-slate-50 border-t border-slate-100 px-5 py-3 flex flex-wrap items-center justify-between text-xs text-slate-600">
                            <span>Kargo: <strong>{{ $subOrder->cargo_company ?? 'Henüz kargolanmadı' }}</strong> {{ $subOrder->tracking_number ? '(' . $subOrder->tracking_number . ')' : '' }}</span>
                            <span>Alt Sipariş Ara Toplam: <strong class="text-slate-900">{{ number_format((float) $subOrder->subtotal, 2, ',', '.') }} TL</strong></span>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Sağ: Teslimat Adresi (Snapshot) ve Ödeme Özeti --}}
            <aside class="lg:col-span-4 space-y-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-2 text-sm">
                    <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Teslimat Adresi (Sipariş Anı Kopyası)</h2>
                    <p class="font-bold text-slate-900">{{ $order->address['title'] ?? 'Teslimat Adresi' }}</p>
                    <p class="text-xs text-slate-600">{{ $order->address['district'] ?? '' }} / {{ $order->address['city'] ?? '' }}</p>
                    <p class="text-xs text-slate-700">{{ $order->address['full_address'] ?? '' }}</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-3 text-sm">
                    <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Ödeme Özeti</h2>
                    <dl class="space-y-2">
                        <div class="flex justify-between text-slate-600">
                            <dt>Ara Toplam</dt>
                            <dd class="font-semibold text-slate-900">{{ number_format((float) $order->subtotal, 2, ',', '.') }} TL</dd>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <dt>Kargo</dt>
                            <dd class="font-semibold text-slate-900">{{ (float) $order->shipping === 0.0 ? 'Ücretsiz' : number_format((float) $order->shipping, 2, ',', '.') . ' TL' }}</dd>
                        </div>
                        @if ((float) $order->discount > 0)
                            <div class="flex justify-between text-emerald-700">
                                <dt>İndirim {{ $order->coupon ? '(' . $order->coupon->code . ')' : '' }}</dt>
                                <dd class="font-bold">-{{ number_format((float) $order->discount, 2, ',', '.') }} TL</dd>
                            </div>
                        @endif
                        <div class="flex justify-between border-t border-slate-200 pt-2.5 text-base">
                            <dt class="font-extrabold text-slate-900">Toplam Ödenen</dt>
                            <dd class="font-extrabold text-indigo-700">{{ number_format((float) $order->total, 2, ',', '.') }} TL</dd>
                        </div>
                    </dl>
                    @if ($order->payments->isNotEmpty())
                        <p class="text-[11px] font-mono text-slate-500 pt-2 border-t border-slate-100">
                            iyzico İşlem No: {{ $order->payments->first()->transaction_id }}
                        </p>
                    @endif
                </div>
            </aside>
        </div>
    </section>
</x-layouts.storefront>
