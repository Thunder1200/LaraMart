<x-layouts.storefront :title="'Alt Sipariş #' . $subOrder->order->order_number . ' — Satıcı Paneli'">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
                <a href="{{ route('seller.orders.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">← Sipariş Listesine Dön</a>
                <h1 class="mt-1 text-2xl font-extrabold text-slate-900">Sipariş #{{ $subOrder->order->order_number }}</h1>
                <p class="text-sm text-slate-600">
                    Müşteri: <strong>{{ $subOrder->order->user?->name ?? 'Müşteri' }}</strong> •
                    Sipariş Tarihi: {{ $subOrder->created_at->format('d.m.Y H:i') }}
                </p>
            </div>
            <span class="rounded-full px-3.5 py-1.5 text-xs font-bold {{ $subOrder->status->badgeClass() }}">
                {{ $subOrder->status->label() }}
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
            {{-- Sol: Ürün Kalemleri ve İade Talepleri --}}
            <div class="lg:col-span-7 space-y-5">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-slate-200 bg-slate-50 px-5 py-3.5 text-sm font-bold text-slate-800">
                        Alt Sipariş Ürünleri ({{ $subOrder->items->sum('quantity') }} adet)
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach ($subOrder->items as $item)
                            <div class="p-5 space-y-3">
                                <div class="flex items-start justify-between gap-4 text-sm">
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $item->product_name }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ $item->quantity }} adet × {{ number_format((float) $item->price, 2, ',', '.') }} TL
                                        </p>
                                    </div>
                                    <p class="font-extrabold text-slate-900 shrink-0">
                                        {{ number_format((float) $item->price * $item->quantity, 2, ',', '.') }} TL
                                    </p>
                                </div>

                                @if ($item->returnRequest)
                                    @php $ret = $item->returnRequest; @endphp
                                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs space-y-2.5">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <span class="font-extrabold text-amber-900">İade Talebi (#{{ $ret->id }})</span>
                                            <span class="rounded-full px-2.5 py-0.5 font-bold {{ $ret->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($ret->status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-200 text-amber-900') }}">
                                                {{ $ret->status === 'approved' ? 'Onaylandı' : ($ret->status === 'rejected' ? 'Reddedildi' : 'Beklemede') }}
                                            </span>
                                        </div>
                                        <p class="text-slate-700"><strong>Müşteri Nedeni:</strong> {{ $ret->reason }}</p>
                                        <p class="text-slate-700"><strong>İade Tutarı:</strong> {{ number_format((float) $ret->refund_amount, 2, ',', '.') }} TL</p>

                                        @if ($ret->status === 'pending')
                                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                                <form method="POST" action="{{ route('seller.returns.decide', $ret) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="decision" value="approved">
                                                    <button type="submit" class="rounded-lg bg-emerald-600 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-emerald-700 transition cursor-pointer">
                                                        İadeyi Onayla
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('seller.returns.decide', $ret) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="decision" value="rejected">
                                                    <button type="submit" class="rounded-lg bg-rose-600 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-rose-700 transition cursor-pointer">
                                                        İadeyi Reddet
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm text-sm space-y-2">
                    <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Teslimat Adresi</h2>
                    <p class="font-bold text-slate-900">{{ $subOrder->order->address['recipient_name'] ?? $subOrder->order->user?->name }}</p>
                    <p class="text-xs text-slate-600">{{ $subOrder->order->address['district'] ?? '' }} / {{ $subOrder->order->address['city'] ?? '' }}</p>
                    <p class="text-xs text-slate-700">{{ $subOrder->order->address['full_address'] ?? '' }}</p>
                </div>
            </div>

            {{-- Sağ: Durum & Kargo Güncelleme Formu --}}
            <aside class="lg:col-span-5 space-y-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                    <h2 class="text-base font-extrabold text-slate-900">Durum &amp; Kargo Yönetimi</h2>

                    @if ($subOrder->cargo_company)
                        <div class="rounded-xl border border-sky-200 bg-sky-50 p-3.5 text-xs space-y-1">
                            <p class="font-bold text-sky-900">Kargo Bilgisi</p>
                            <p class="text-sky-800">Firma: <strong>{{ $subOrder->cargo_company }}</strong></p>
                            <p class="font-mono text-sky-800">Takip No: <strong>{{ $subOrder->tracking_number }}</strong></p>
                        </div>
                    @endif

                    @if ($allowedTransitions !== [])
                        <form
                            method="POST"
                            action="{{ route('seller.orders.update-status', $subOrder) }}"
                            x-data="{ selectedStatus: @js(old('status', $allowedTransitions[0]->value)) }"
                            class="space-y-4"
                        >
                            @csrf
                            @method('PATCH')

                            <div>
                                <label for="status" class="block text-xs font-bold text-slate-700">Yeni Durum (İzin Verilen Geçişler)</label>
                                <select
                                    id="status"
                                    name="status"
                                    x-model="selectedStatus"
                                    class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none"
                                >
                                    @foreach ($allowedTransitions as $next)
                                        <option value="{{ $next->value }}">{{ $next->label() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div x-show="selectedStatus === 'shipped'" x-cloak class="space-y-3 rounded-xl border border-indigo-200 bg-indigo-50/70 p-4">
                                <p class="text-xs font-bold text-indigo-800">Kargo Bilgileri (Zorunlu)</p>
                                <div>
                                    <label for="cargo_company" class="block text-xs font-semibold text-slate-700">Kargo Firması</label>
                                    <select
                                        id="cargo_company"
                                        name="cargo_company"
                                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800"
                                    >
                                        <option value="">Kargo firması seçin</option>
                                        @foreach ($cargoCompanies as $company)
                                            <option value="{{ $company }}" @selected(old('cargo_company', $subOrder->cargo_company) === $company)>
                                                {{ $company }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="tracking_number" class="block text-xs font-semibold text-slate-700">Takip Numarası</label>
                                    <input
                                        id="tracking_number"
                                        type="text"
                                        name="tracking_number"
                                        value="{{ old('tracking_number', $subOrder->tracking_number) }}"
                                        placeholder="Örn: YK20260012345"
                                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono text-slate-800"
                                    >
                                </div>
                            </div>

                            <button
                                type="submit"
                                class="w-full rounded-lg bg-indigo-600 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 transition cursor-pointer"
                            >
                                Durumu Güncelle ve Müşteriye Bildir
                            </button>
                        </form>
                    @else
                        <p class="rounded-xl bg-slate-50 border border-slate-200 p-4 text-xs text-slate-600">
                            Bu alt sipariş <strong>{{ $subOrder->status->label() }}</strong> nihai durumunda olduğu için başka bir duruma geçirilemez.
                        </p>
                    @endif

                    <dl class="border-t border-slate-100 pt-4 space-y-2 text-xs text-slate-600">
                        <div class="flex justify-between">
                            <dt>Alt Sipariş Ara Toplamı</dt>
                            <dd class="font-bold text-slate-900">{{ number_format((float) $subOrder->subtotal, 2, ',', '.') }} TL</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Kargo Bedeli</dt>
                            <dd class="font-bold text-slate-900">{{ number_format((float) $subOrder->shipping_amount, 2, ',', '.') }} TL</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Platform Komisyonu</dt>
                            <dd class="font-bold text-amber-700">-{{ number_format((float) $subOrder->commission_amount, 2, ',', '.') }} TL</dd>
                        </div>
                    </dl>
                </div>
            </aside>
        </div>
    </section>
</x-layouts.storefront>
