@extends('layouts.app')

@section('title', 'Sipariş #' . $order->order_number . ' - LaraMart')

@section('content')
<div class="space-y-8">
    <div class="bg-white rounded-xl border border-slate-200 p-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-xs uppercase font-bold text-slate-500">Ana Sipariş Numarası</span>
            <h1 class="text-2xl font-extrabold text-slate-900">#{{ $order->order_number }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                Teslimat Adresi (Snapshot): {{ $order->address['title'] ?? '' }} - {{ $order->address['full_address'] ?? '' }} ({{ $order->address['district'] ?? '' }} / {{ $order->address['city'] ?? '' }})
            </p>
        </div>
        <div class="text-right">
            <span class="text-xs text-slate-500 block">Ödenen Genel Toplam</span>
            <span class="text-2xl font-extrabold text-emerald-700">{{ $order->total }} TL</span>
        </div>
    </div>

    <div class="space-y-6">
        <h2 class="text-xl font-extrabold text-slate-900">Mağaza Bazlı Alt Siparişler ({{ $order->subOrders->count() }} Adet)</h2>

        @foreach($order->subOrders as $subOrder)
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="bg-slate-100 px-6 py-4 flex flex-wrap items-center justify-between gap-4 border-b border-slate-200">
                    <div>
                        <span class="text-xs text-slate-500">Alt Sipariş #{{ $subOrder->id }} — Satıcı:</span>
                        <strong class="text-slate-900 ml-1">{{ $subOrder->seller?->store_name }}</strong>
                    </div>
                    <div class="flex items-center gap-4 text-sm">
                        @if($subOrder->tracking_number)
                            <span class="bg-amber-100 text-amber-900 px-3 py-1 rounded font-semibold">
                                Kargo: {{ $subOrder->cargo_company }} ({{ $subOrder->tracking_number }})
                            </span>
                        @endif
                        <span class="bg-indigo-100 text-indigo-900 px-3 py-1 rounded font-bold">
                            Durum: {{ $subOrder->status->label() }}
                        </span>
                    </div>
                </div>

                <div class="divide-y divide-slate-100 p-6 space-y-6">
                    @foreach($subOrder->items as $item)
                        <div class="pt-4 first:pt-0 space-y-4">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div>
                                    <p class="font-bold text-slate-900">{{ $item->product_name }}</p>
                                    <p class="text-xs text-slate-500">Sipariş Anındaki Birim Fiyat (Snapshot): {{ $item->price }} TL x {{ $item->quantity }} Adet</p>
                                </div>
                                <span class="font-bold text-slate-900">{{ number_format((float)$item->price * $item->quantity, 2) }} TL</span>
                            </div>

                            @if($subOrder->status->value === 'delivered')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200 text-sm">
                                    <div>
                                        <h4 class="font-bold text-slate-800 mb-2">Ürünü Değerlendir & Puan Ver</h4>
                                        @if($item->review)
                                            <p class="text-xs text-emerald-700 font-semibold">
                                                Değerlendirmeniz alındı (★ {{ $item->review->rating }}): "{{ $item->review->comment }}"
                                            </p>
                                        @else
                                            <form action="{{ route('account.reviews.store', $item) }}" method="POST" class="space-y-2">
                                                @csrf
                                                <select name="rating" class="w-full border rounded px-3 py-1.5">
                                                    <option value="5">5 Yıldız - Çok İyi</option>
                                                    <option value="4">4 Yıldız - İyi</option>
                                                    <option value="3">3 Yıldız - Orta</option>
                                                    <option value="2">2 Yıldız - Kötü</option>
                                                    <option value="1">1 Yıldız - Çok Kötü</option>
                                                </select>
                                                <input type="text" name="comment" placeholder="Yorumunuzu yazın..." required class="w-full border rounded px-3 py-1.5">
                                                <button type="submit" class="bg-slate-900 text-white text-xs font-bold px-4 py-2 rounded">Yorum Gönder</button>
                                            </form>
                                        @endif
                                    </div>

                                    <div>
                                        <h4 class="font-bold text-slate-800 mb-2">14 Gün İçinde İade Talebi</h4>
                                        @if($item->returnRequest)
                                            <p class="text-xs text-amber-800 font-semibold">
                                                İade Talebi Durumu: {{ $item->returnRequest->status->label() }} ({{ $item->returnRequest->refund_amount }} TL)
                                            </p>
                                        @else
                                            <form action="{{ route('account.returns.store', $item) }}" method="POST" class="space-y-2">
                                                @csrf
                                                <input type="text" name="reason" placeholder="İade nedeninizi belirtin..." required class="w-full border rounded px-3 py-1.5">
                                                <button type="submit" class="bg-rose-600 text-white text-xs font-bold px-4 py-2 rounded">İade Talebi Aç</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

