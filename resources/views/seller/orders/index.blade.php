<x-layouts.storefront title="Satıcı Siparişleri — LaraMart">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
                <a href="{{ route('seller.dashboard') }}" class="text-xs font-semibold text-indigo-600 hover:underline">← Satıcı Paneline Dön</a>
                <h1 class="mt-1 text-2xl font-extrabold text-slate-900">{{ $seller->store_name }} — Siparişler &amp; Kargo</h1>
                <p class="text-sm text-slate-600">Yalnızca mağazanıza ait alt siparişleri görüntüler, kargolar ve durumlarını güncellersiniz.</p>
            </div>
            <a href="{{ route('seller.returns.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                İade Taleplerini Gör →
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

        {{-- Durum Filtre Sekmeleri --}}
        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('seller.orders.index') }}"
                class="rounded-full px-3.5 py-1.5 text-xs font-bold transition {{ $statusFilter === '' ? 'bg-indigo-600 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:border-indigo-300' }}"
            >
                Tümü
            </a>
            @foreach ($statuses as $status)
                <a
                    href="{{ route('seller.orders.index', ['durum' => $status->value]) }}"
                    class="rounded-full px-3.5 py-1.5 text-xs font-bold transition {{ $statusFilter === $status->value ? 'bg-indigo-600 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:border-indigo-300' }}"
                >
                    {{ $status->label() }}
                </a>
            @endforeach
        </div>

        @if ($subOrders->isNotEmpty())
            <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5">Sipariş No</th>
                            <th class="px-5 py-3.5">Müşteri</th>
                            <th class="px-5 py-3.5">Ürünler</th>
                            <th class="px-5 py-3.5">Tutar</th>
                            <th class="px-5 py-3.5">Durum</th>
                            <th class="px-5 py-3.5">Kargo</th>
                            <th class="px-5 py-3.5 text-right">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($subOrders as $subOrder)
                            @php
                                $pendingReturns = $subOrder->items->filter(fn ($i) => $i->returnRequest?->status === 'pending')->count();
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <p class="font-mono font-bold text-slate-900">{{ $subOrder->order->order_number }}</p>
                                    <p class="text-xs text-slate-500">{{ $subOrder->created_at->format('d.m.Y H:i') }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $subOrder->order->user?->name ?? 'Müşteri' }}</p>
                                    <p class="text-xs text-slate-500">{{ $subOrder->order->address['city'] ?? '' }}</p>
                                </td>
                                <td class="px-5 py-4 text-xs text-slate-700">
                                    {{ $subOrder->items->sum('quantity') }} adet
                                    @if ($pendingReturns > 0)
                                        <span class="ml-1 inline-block rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-700">
                                            {{ $pendingReturns }} İade Talebi
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 font-bold text-slate-900">
                                    {{ number_format((float) $subOrder->subtotal, 2, ',', '.') }} TL
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-block rounded-full px-2.5 py-1 text-xs font-bold {{ $subOrder->status->badgeClass() }}">
                                        {{ $subOrder->status->label() }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-xs text-slate-600">
                                    @if ($subOrder->cargo_company)
                                        <p class="font-semibold text-slate-800">{{ $subOrder->cargo_company }}</p>
                                        <p class="font-mono text-[11px]">{{ $subOrder->tracking_number }}</p>
                                    @else
                                        <span class="text-slate-400">Henüz girilmedi</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a
                                        href="{{ route('seller.orders.show', $subOrder) }}"
                                        class="inline-flex items-center rounded-lg bg-indigo-600 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-indigo-700 transition"
                                    >
                                        Yönet →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>
                {{ $subOrders->links() }}
            </div>
        @else
            <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center">
                <p class="text-base font-bold text-slate-700">Bu filtrede henüz alt sipariş bulunmuyor.</p>
            </div>
        @endif
    </section>
</x-layouts.storefront>
