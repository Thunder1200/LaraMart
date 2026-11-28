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

        @if (session('error'))
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Sol: Satıcı Bazlı Alt Siparişler --}}
            <div class="lg:col-span-8 space-y-5">
                @foreach ($order->subOrders as $subOrder)
                    @php
                        $step = $subOrder->status->progressStep();
                        $returnOpen = $subOrder->isReturnWindowOpen();
                    @endphp
                    <article class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="bg-slate-50 border-b border-slate-200 px-5 py-3.5 flex flex-wrap items-center justify-between gap-2 text-sm">
                            <div>
                                <span class="text-slate-500">Satıcı Paketi:</span>
                                <strong class="text-indigo-700">{{ $subOrder->seller->store_name }}</strong>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $subOrder->status->badgeClass() }}">
                                {{ $subOrder->status->label() }}
                            </span>
                        </div>

                        {{-- Kargo & Sipariş Yaşam Döngüsü Çubuğu --}}
                        @if ($step > 0)
                            <div class="px-5 pt-4 pb-2 border-b border-slate-100">
                                <div class="grid grid-cols-4 gap-2 text-center text-[11px]">
                                    @foreach ([1 => 'Onay Bekliyor', 2 => 'Hazırlanıyor', 3 => 'Kargoda', 4 => 'Teslim Edildi'] as $idx => $label)
                                        <div class="space-y-1.5">
                                            <div class="h-1.5 rounded-full {{ $step >= $idx ? 'bg-indigo-600' : 'bg-slate-200' }}"></div>
                                            <span class="{{ $step >= $idx ? 'font-bold text-indigo-700' : 'text-slate-400' }}">{{ $label }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="divide-y divide-slate-100">
                            @foreach ($subOrder->items as $item)
                                <div class="px-5 py-4 space-y-3" x-data="{ showReturnForm: false }">
                                    <div class="flex flex-wrap items-start justify-between gap-4 text-sm">
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

                                    {{-- Teslim Edilen Ürün İşlemleri: Yorum Yaz & 14 Gün İade Talebi --}}
                                    @if ($subOrder->status === \App\Enums\SubOrderStatus::Delivered || $subOrder->status === \App\Enums\SubOrderStatus::Returned)
                                        <div class="flex flex-wrap items-center gap-2.5 text-xs">
                                            @if ($item->variant?->product)
                                                @if ($item->review)
                                                    <span class="rounded-lg bg-emerald-50 border border-emerald-200 px-2.5 py-1 font-semibold text-emerald-700">
                                                        ★ Değerlendirildi ({{ $item->review->is_approved ? 'Yayında' : 'Onay Bekliyor' }})
                                                    </span>
                                                @else
                                                    <a
                                                        href="{{ route('products.show', $item->variant->product->slug) }}#yorum-yaz"
                                                        class="rounded-lg bg-amber-50 border border-amber-200 px-3 py-1 font-bold text-amber-800 hover:bg-amber-100 transition"
                                                    >
                                                        ★ Ürünü Değerlendir
                                                    </a>
                                                @endif
                                            @endif

                                            @if ($item->returnRequest)
                                                @php $ret = $item->returnRequest; @endphp
                                                <span class="rounded-lg px-2.5 py-1 font-bold {{ $ret->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($ret->status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                                    İade Talebi: {{ $ret->status === 'approved' ? 'Onaylandı' : ($ret->status === 'rejected' ? 'Reddedildi' : 'Satıcı İnceliyor') }}
                                                </span>
                                            @elseif ($returnOpen)
                                                <button
                                                    type="button"
                                                    @click="showReturnForm = !showReturnForm"
                                                    class="rounded-lg border border-rose-300 bg-rose-50 px-3 py-1 font-bold text-rose-700 hover:bg-rose-100 transition cursor-pointer"
                                                >
                                                    İade Talebi Aç ({{ $subOrder->returnDaysRemaining() }} gün kaldı)
                                                </button>
                                            @endif
                                        </div>

                                        @if (! $item->returnRequest && $returnOpen)
                                            <form
                                                x-show="showReturnForm"
                                                x-cloak
                                                method="POST"
                                                action="{{ route('customer.returns.store', $item) }}"
                                                class="rounded-xl border border-rose-200 bg-rose-50/50 p-4 space-y-3"
                                            >
                                                @csrf
                                                <label for="reason-{{ $item->id }}" class="block text-xs font-bold text-slate-800">
                                                    İade Nedeniniz (En az 10 karakter)
                                                </label>
                                                <textarea
                                                    id="reason-{{ $item->id }}"
                                                    name="reason"
                                                    rows="2"
                                                    required
                                                    minlength="10"
                                                    placeholder="Örn: Ürün bedeni uymadı / hasarlı teslim edildi..."
                                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-800"
                                                ></textarea>
                                                <div class="flex items-center justify-end gap-2">
                                                    <button type="button" @click="showReturnForm = false" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 cursor-pointer">
                                                        Vazgeç
                                                    </button>
                                                    <button type="submit" class="rounded-lg bg-rose-600 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-rose-700 transition cursor-pointer">
                                                        İade Talebini Gönder
                                                    </button>
                                                </div>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="bg-slate-50 border-t border-slate-100 px-5 py-3 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                            <span>
                                Kargo: <strong>{{ $subOrder->cargo_company ?? 'Henüz kargolanmadı' }}</strong>
                                @if ($subOrder->tracking_number)
                                    • Takip No: <span class="font-mono font-bold text-indigo-700">{{ $subOrder->tracking_number }}</span>
                                @endif
                            </span>
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
