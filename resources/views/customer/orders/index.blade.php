<x-layouts.storefront title="Siparişlerim — LaraMart">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div class="border-b border-slate-200 pb-4">
            <a href="{{ route('customer.account') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">← Hesabıma Dön</a>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Siparişlerim</h1>
            <p class="text-sm text-slate-600">Tüm siparişlerinizi ve satıcı bazlı alt sipariş durumlarını buradan takip edebilirsiniz.</p>
        </div>

        @if ($orders->isNotEmpty())
            <div class="space-y-4">
                @foreach ($orders as $order)
                    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-3">
                            <div class="flex flex-wrap items-center gap-4 text-sm">
                                <div>
                                    <span class="block text-xs text-slate-500">Sipariş No</span>
                                    <strong class="font-mono text-slate-900">{{ $order->order_number }}</strong>
                                </div>
                                <div>
                                    <span class="block text-xs text-slate-500">Tarih</span>
                                    <strong class="text-slate-800">{{ $order->created_at->format('d.m.Y H:i') }}</strong>
                                </div>
                                <div>
                                    <span class="block text-xs text-slate-500">Alt Sipariş</span>
                                    <strong class="text-indigo-700">{{ $order->subOrders->count() }} satıcı paketi</strong>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <span class="block text-xs text-slate-500">Toplam</span>
                                    <strong class="text-lg font-extrabold text-indigo-700">{{ number_format((float) $order->total, 2, ',', '.') }} TL</strong>
                                </div>
                                <a href="{{ route('customer.orders.show', $order) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-bold text-white hover:bg-indigo-700 transition">
                                    Detay Gör →
                                </a>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach ($order->subOrders as $subOrder)
                                <div class="rounded-xl bg-slate-50 border border-slate-200 p-3.5 text-xs flex items-center justify-between">
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $subOrder->seller->store_name }}</p>
                                        <p class="text-slate-500">{{ $subOrder->items->sum('quantity') }} ürün</p>
                                    </div>
                                    <span class="rounded-full px-2.5 py-1 font-bold {{ $subOrder->status->badgeClass() }}">
                                        {{ $subOrder->status->label() }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>

            <div>
                {{ $orders->links() }}
            </div>
        @else
            <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center space-y-3">
                <p class="text-base font-bold text-slate-800">Henüz siparişiniz bulunmuyor.</p>
                <a href="{{ route('home') }}" class="inline-block rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    Alışverişe Başla
                </a>
            </div>
        @endif
    </section>
</x-layouts.storefront>
