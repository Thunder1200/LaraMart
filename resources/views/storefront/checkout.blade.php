@extends('layouts.app')

@section('title', 'Güvenli Ödeme (iyzico Sandbox) - LaraMart')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-8 space-y-6">
        <h1 class="text-2xl font-extrabold text-slate-900">Güvenli Ödeme ve Sipariş Onayı</h1>

        @if($addresses->isEmpty())
            <div class="bg-amber-50 border border-amber-300 text-amber-800 p-4 rounded-lg text-sm">
                Ödeme yapabilmek için önce Hesabım sayfasından bir teslimat adresi eklemelisiniz.
                <a href="{{ route('account.index') }}" class="font-bold underline ml-2">Adres Ekle &rarr;</a>
            </div>
        @endif

        <form action="{{ route('checkout.process') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Teslimat Adresi Seçin</label>
                <div class="space-y-2">
                    @foreach($addresses as $address)
                        <label class="flex items-start gap-3 p-4 border border-slate-200 rounded-xl cursor-pointer hover:border-amber-500">
                            <input type="radio" name="address_id" value="{{ $address->id }}" @checked($loop->first) class="mt-1">
                            <div class="text-sm">
                                <span class="font-bold text-slate-900">{{ $address->title }} ({{ $address->city }} / {{ $address->district }})</span>
                                <p class="text-slate-600">{{ $address->full_address }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-slate-900">iyzico Sandbox Kart Bilgileri (3D Secure)</h2>
                    <span class="text-xs bg-indigo-100 text-indigo-800 px-2.5 py-1 rounded font-semibold">Test Ortamı</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kart Üzerindeki İsim</label>
                        <input type="text" name="card_holder_name" value="AHMET YILMAZ" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kart Numarası (Sandbox)</label>
                        <input type="text" name="card_number" value="5528 7900 0000 0008" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Ay</label>
                        <input type="text" name="expire_month" value="12" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Yıl</label>
                        <input type="text" name="expire_year" value="2030" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">CVC</label>
                        <input type="text" name="cvc" value="123" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                    </div>
                </div>
            </div>

            <div>
                <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                    <input type="checkbox" name="terms_accepted" value="1" required>
                    <span>Ön bilgilendirme formunu ve Mesafeli Satış Sözleşmesini okudum, onaylıyorum.</span>
                </label>
            </div>

            <button type="submit" @disabled($addresses->isEmpty()) class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:bg-slate-300 text-white font-extrabold py-3.5 rounded-xl text-base">
                {{ $summary['total'] }} TL Öde ve Siparişi Tamamla
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6 h-fit space-y-4">
        <h2 class="text-lg font-extrabold border-b pb-3">Sipariş Özeti ({{ count($summary['seller_groups']) }} Satıcı)</h2>

        @foreach($summary['seller_groups'] as $group)
            <div class="border-b border-slate-100 pb-3 text-sm space-y-1">
                <p class="font-bold text-indigo-700">{{ $group['store_name'] }}</p>
                @foreach($group['items'] as $item)
                    <div class="flex justify-between text-xs text-slate-600">
                        <span>{{ $item['product_name'] }} (x{{ $item['quantity'] }})</span>
                        <span>{{ $item['line_total'] }} TL</span>
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="space-y-2 text-sm pt-2">
            <div class="flex justify-between">
                <span>Ara Toplam</span>
                <span class="font-semibold">{{ $summary['subtotal'] }} TL</span>
            </div>
            <div class="flex justify-between">
                <span>Kargo</span>
                <span class="font-semibold">{{ $summary['shipping'] }} TL</span>
            </div>
            <div class="flex justify-between text-emerald-700">
                <span>İndirim</span>
                <span class="font-semibold">-{{ $summary['discount'] }} TL</span>
            </div>
            <div class="flex justify-between text-lg font-extrabold border-t pt-2">
                <span>Toplam</span>
                <span>{{ $summary['total'] }} TL</span>
            </div>
        </div>
    </div>
</div>
@endsection

