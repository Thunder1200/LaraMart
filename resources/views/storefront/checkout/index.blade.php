<x-layouts.storefront title="Ödeme ve Sipariş Onayı — LaraMart">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="border-b border-slate-200 pb-4 flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('cart.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">← Sepete Dön</a>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-900">Güvenli Ödeme (iyzico Sandbox 3D Secure)</h1>
            </div>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                256-Bit SSL • 3D Secure Korumalı
            </span>
        </div>

        @if ($addresses->isEmpty())
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 space-y-3">
                <h2 class="text-base font-bold text-amber-900">Kayıtlı Teslimat Adresiniz Bulunmuyor</h2>
                <p class="text-sm text-amber-800">Siparişinizi tamamlayabilmek için önce en az bir teslimat adresi eklemelisiniz.</p>
                <a href="{{ route('customer.addresses.index') }}" class="inline-block rounded-lg bg-amber-600 px-4 py-2 text-sm font-bold text-white hover:bg-amber-700">
                    Adres Ekle →
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('checkout.start') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

                {{-- Sol: Adres, Kart Bilgileri ve Mesafeli Satış Sözleşmesi --}}
                <div class="lg:col-span-7 space-y-6">
                    {{-- 1. Teslimat Adresi Seçimi --}}
                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-base font-extrabold text-slate-900">1. Teslimat Adresi Seçin</h2>
                            <a href="{{ route('customer.addresses.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">+ Yeni Adres</a>
                        </div>

                        <div class="space-y-3">
                            @foreach ($addresses as $address)
                                <label class="flex items-start gap-3 rounded-xl border p-4 cursor-pointer hover:border-indigo-300 transition {{ (string) old('address_id', $addresses->first()->id) === (string) $address->id ? 'border-indigo-500 bg-indigo-50/40' : 'border-slate-200' }}">
                                    <input
                                        type="radio"
                                        name="address_id"
                                        value="{{ $address->id }}"
                                        @checked((string) old('address_id', $addresses->first()->id) === (string) $address->id)
                                        class="mt-1 text-indigo-600"
                                    >
                                    <div class="text-sm">
                                        <p class="font-bold text-slate-900">
                                            {{ $address->title }}
                                            @if ($address->is_default)
                                                <span class="ml-1 rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-bold text-indigo-700">Varsayılan</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-slate-600">{{ $address->district }} / {{ $address->city }}</p>
                                        <p class="mt-0.5 text-xs text-slate-700">{{ $address->full_address }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('address_id')
                            <p class="text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </section>

                    {{-- 2. Kart Bilgileri (iyzico Sandbox Test Kartları) --}}
                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                        <h2 class="text-base font-extrabold text-slate-900">2. Kart Bilgileri (iyzico Sandbox)</h2>

                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-3.5 text-xs text-slate-600 space-y-1">
                            <p class="font-bold text-slate-800">Test Kartları:</p>
                            <p>• Başarılı 3D Secure Test Kartı: <code class="font-mono font-bold text-emerald-700">5528790000000008</code> (SKT: 12/2030, CVC: 123, SMS: 123456)</p>
                            <p>• Başarısız / Yetersiz Bakiye Test Kartı: <code class="font-mono font-bold text-rose-700">4546710000000000</code> (sonu 0000)</p>
                        </div>

                        <div>
                            <label for="card_holder" class="block text-xs font-bold text-slate-700">Kart Üzerindeki İsim *</label>
                            <input
                                type="text"
                                id="card_holder"
                                name="card_holder"
                                value="{{ old('card_holder', auth()->user()->name) }}"
                                required
                                class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900"
                            >
                            @error('card_holder')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="card_number" class="block text-xs font-bold text-slate-700">Kart Numarası (16 Hane) *</label>
                            <input
                                type="text"
                                id="card_number"
                                name="card_number"
                                value="{{ old('card_number', '5528790000000008') }}"
                                required
                                maxlength="19"
                                class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm font-mono text-slate-900"
                            >
                            @error('card_number')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label for="expire_month" class="block text-xs font-bold text-slate-700">Ay (AA) *</label>
                                <input
                                    type="text"
                                    id="expire_month"
                                    name="expire_month"
                                    value="{{ old('expire_month', '12') }}"
                                    required
                                    maxlength="2"
                                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono text-slate-900"
                                >
                                @error('expire_month')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="expire_year" class="block text-xs font-bold text-slate-700">Yıl (YYYY) *</label>
                                <input
                                    type="text"
                                    id="expire_year"
                                    name="expire_year"
                                    value="{{ old('expire_year', '2030') }}"
                                    required
                                    maxlength="4"
                                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono text-slate-900"
                                >
                                @error('expire_year')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="cvc" class="block text-xs font-bold text-slate-700">CVC *</label>
                                <input
                                    type="text"
                                    id="cvc"
                                    name="cvc"
                                    value="{{ old('cvc', '123') }}"
                                    required
                                    maxlength="4"
                                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono text-slate-900"
                                >
                                @error('cvc')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    {{-- 3. Mesafeli Satış Sözleşmesi --}}
                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-2">
                        <label class="flex items-start gap-3 text-sm text-slate-700 cursor-pointer">
                            <input
                                type="checkbox"
                                name="distance_sales_agreement"
                                value="1"
                                @checked(old('distance_sales_agreement'))
                                class="mt-1 rounded border-slate-300 text-indigo-600"
                            >
                            <span>
                                <strong>Ön Bilgilendirme Formu</strong> ve <strong>Mesafeli Satış Sözleşmesi</strong>'ni okudum, onaylıyorum. Siparişimin satıcı bazında alt siparişlere bölünerek işleneceğini biliyorum.
                            </span>
                        </label>
                        @error('distance_sales_agreement')
                            <p class="text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </section>
                </div>

                {{-- Sağ: Sipariş Özeti --}}
                <aside class="lg:col-span-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-5">
                    <h2 class="text-base font-extrabold text-slate-900">Sipariş Özeti ({{ count($summary['groups']) }} Satıcı)</h2>

                    <div class="space-y-4">
                        @foreach ($summary['groups'] as $group)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-indigo-700">{{ $group['seller_name'] }}</span>
                                    <span class="text-slate-500">
                                        Kargo: {{ $group['shipping'] === 0.0 ? 'Ücretsiz' : number_format($group['shipping'], 2, ',', '.') . ' TL' }}
                                    </span>
                                </div>
                                <ul class="space-y-1 text-xs text-slate-700">
                                    @foreach ($group['items'] as $item)
                                        <li class="flex justify-between gap-2">
                                            <span class="truncate">{{ $item->quantity }}x {{ $item->variant->product->name }} ({{ $item->variant->label() }})</span>
                                            <span class="font-semibold shrink-0">{{ number_format($item->variant->effectivePrice() * $item->quantity, 2, ',', '.') }} TL</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>

                    <dl class="space-y-2 text-sm border-t border-slate-200 pt-4">
                        <div class="flex justify-between text-slate-600">
                            <dt>Ara Toplam</dt>
                            <dd class="font-semibold text-slate-900">{{ number_format($summary['subtotal'], 2, ',', '.') }} TL</dd>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <dt>Toplam Kargo</dt>
                            <dd class="font-semibold text-slate-900">{{ $summary['shipping'] === 0.0 ? 'Ücretsiz' : number_format($summary['shipping'], 2, ',', '.') . ' TL' }}</dd>
                        </div>
                        @if ($summary['discount'] > 0)
                            <div class="flex justify-between text-emerald-700">
                                <dt>Kupon İndirimi ({{ $appliedCoupon?->code }})</dt>
                                <dd class="font-bold">-{{ number_format($summary['discount'], 2, ',', '.') }} TL</dd>
                            </div>
                        @endif
                        <div class="flex justify-between border-t border-slate-200 pt-3 text-base">
                            <dt class="font-extrabold text-slate-900">Ödenecek Tutar</dt>
                            <dd class="text-xl font-extrabold text-indigo-700">{{ number_format($summary['total'], 2, ',', '.') }} TL</dd>
                        </div>
                    </dl>

                    <button type="submit" class="w-full rounded-xl bg-indigo-600 py-3.5 text-sm font-extrabold text-white hover:bg-indigo-700 transition shadow">
                        3D Secure ile Ödemeyi Tamamla →
                    </button>
                </aside>
            </form>
        @endif
    </div>
</x-layouts.storefront>
