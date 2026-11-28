<x-layouts.storefront title="İade Talepleri — Satıcı Paneli">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
                <a href="{{ route('seller.dashboard') }}" class="text-xs font-semibold text-indigo-600 hover:underline">← Satıcı Paneline Dön</a>
                <h1 class="mt-1 text-2xl font-extrabold text-slate-900">{{ $seller->store_name }} — İade Talepleri</h1>
                <p class="text-sm text-slate-600">Müşterilerin 14 gün içinde açtığı iade taleplerini onaylayabilir veya reddedebilirsiniz.</p>
            </div>
            <a href="{{ route('seller.orders.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                Siparişlere Dön →
            </a>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        @if ($returnRequests->isNotEmpty())
            <div class="space-y-4">
                @foreach ($returnRequests as $ret)
                    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-mono text-slate-500">
                                    Sipariş #{{ $ret->orderItem->subOrder->order->order_number }} • {{ $ret->created_at->format('d.m.Y H:i') }}
                                </p>
                                <h2 class="mt-1 text-base font-bold text-slate-900">{{ $ret->orderItem->product_name }}</h2>
                                <p class="text-xs text-slate-600">
                                    Müşteri: <strong>{{ $ret->orderItem->subOrder->order->user?->name }}</strong> •
                                    İade Tutarı: <strong class="text-indigo-700">{{ number_format((float) $ret->refund_amount, 2, ',', '.') }} TL</strong>
                                </p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $ret->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($ret->status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ $ret->status === 'approved' ? 'Onaylandı' : ($ret->status === 'rejected' ? 'Reddedildi' : 'Beklemede') }}
                            </span>
                        </div>

                        <p class="rounded-xl bg-slate-50 border border-slate-200 p-3.5 text-sm text-slate-700">
                            <strong class="text-xs uppercase tracking-wider text-slate-500 block mb-1">İade Nedeni</strong>
                            {{ $ret->reason }}
                        </p>

                        @if ($ret->status === 'pending')
                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                <form method="POST" action="{{ route('seller.returns.decide', $ret) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="decision" value="approved">
                                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 transition cursor-pointer">
                                        Onayla &amp; Stoğa İade Et
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('seller.returns.decide', $ret) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="decision" value="rejected">
                                    <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-xs font-bold text-white hover:bg-rose-700 transition cursor-pointer">
                                        Reddet
                                    </button>
                                </form>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>

            <div>
                {{ $returnRequests->links() }}
            </div>
        @else
            <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center">
                <p class="text-base font-bold text-slate-700">Henüz iade talebi bulunmuyor.</p>
            </div>
        @endif
    </section>
</x-layouts.storefront>
