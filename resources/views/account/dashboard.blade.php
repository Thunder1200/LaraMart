@extends('layouts.app')

@section('title', 'Hesabım - LaraMart')

@section('content')
<div class="space-y-10">
    <div class="bg-white rounded-xl border border-slate-200 p-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Merhaba, {{ $user->name }}</h1>
            <p class="text-sm text-slate-500">{{ $user->email }} | Roller: {{ $user->getRoleNames()->implode(', ') }}</p>
        </div>
        @if($user->seller)
            <span class="px-4 py-2 rounded-lg text-sm font-bold bg-indigo-50 text-indigo-800 border border-indigo-200">
                Satıcı Başvuru Durumu: {{ $user->seller->status->label() }} ({{ $user->seller->store_name }})
            </span>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            <section class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
                <h2 class="text-xl font-extrabold text-slate-900 border-b pb-3">Siparişlerim ({{ $orders->count() }})</h2>
                @forelse($orders as $order)
                    <div class="border border-slate-200 rounded-xl p-4 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <span class="font-extrabold text-slate-900">#{{ $order->order_number }}</span>
                            <p class="text-xs text-slate-500">
                                {{ $order->created_at?->format('d.m.Y H:i') }} | {{ $order->subOrders->count() }} Alt Sipariş (Mağaza)
                            </p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="font-bold text-slate-900">{{ $order->total }} TL</span>
                            <a href="{{ route('account.orders.show', $order) }}" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold px-4 py-2 rounded-lg">
                                Sipariş Detayı
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Henüz siparişiniz bulunmuyor.</p>
                @endforelse
            </section>

            <section class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
                <h2 class="text-xl font-extrabold text-slate-900 border-b pb-3">Favori Ürünlerim ({{ $favorites->count() }})</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @forelse($favorites as $favProduct)
                        <div class="border border-slate-200 rounded-lg p-4 flex items-center justify-between">
                            <div>
                                <a href="{{ route('catalog.product', $favProduct->slug) }}" class="font-bold text-sm text-slate-900 hover:text-amber-600">
                                    {{ $favProduct->name }}
                                </a>
                                <p class="text-xs text-slate-500">{{ $favProduct->seller?->store_name }}</p>
                            </div>
                            <form action="{{ route('account.favorites.toggle', $favProduct) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs text-rose-600 font-bold">Kaldır</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Favori listeniz boş.</p>
                    @endforelse
                </div>
            </section>

            <section class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
                <h2 class="text-xl font-extrabold text-slate-900 border-b pb-3">Site İçi Bildirimler ({{ $notifications->count() }})</h2>
                @forelse($notifications as $notification)
                    <div class="text-sm border-b border-slate-100 pb-2">
                        <span class="font-semibold">{{ $notification->data['store_name'] ?? 'LaraMart' }}</span>:
                        Sipariş <strong>#{{ $notification->data['order_number'] ?? '' }}</strong> durumu
                        <span class="text-indigo-700 font-bold">{{ $notification->data['status_label'] ?? '' }}</span> olarak güncellendi.
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Henüz bildiriminiz yok.</p>
                @endforelse
            </section>
        </div>

        <div class="space-y-8">
            <section class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
                <h2 class="text-lg font-extrabold text-slate-900 border-b pb-3">Adres Defterim</h2>
                @foreach($addresses as $addr)
                    <div class="border border-slate-200 rounded-lg p-3 text-sm flex justify-between items-start">
                        <div>
                            <span class="font-bold">{{ $addr->title }}</span> ({{ $addr->city }}/{{ $addr->district }})
                            <p class="text-xs text-slate-600 mt-1">{{ $addr->full_address }}</p>
                        </div>
                        <form action="{{ route('account.addresses.delete', $addr) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-rose-600 font-bold">Sil</button>
                        </form>
                    </div>
                @endforeach

                <form action="{{ route('account.addresses.store') }}" method="POST" class="space-y-3 pt-3 border-t text-sm">
                    @csrf
                    <h3 class="font-bold text-slate-800">Yeni Adres Ekle</h3>
                    <input type="text" name="title" placeholder="Adres Başlığı (Ev, İş)" required class="w-full border rounded-lg px-3 py-2">
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="city" placeholder="İl (İstanbul)" required class="w-full border rounded-lg px-3 py-2">
                        <input type="text" name="district" placeholder="İlçe (Kadıköy)" required class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <textarea name="full_address" rows="2" placeholder="Açık adres..." required class="w-full border rounded-lg px-3 py-2"></textarea>
                    <label class="flex items-center gap-2 text-xs">
                        <input type="checkbox" name="is_default" value="1"> Varsayılan adres yap
                    </label>
                    <button type="submit" class="w-full bg-slate-900 text-white font-bold py-2 rounded-lg">Adresi Kaydet</button>
                </form>
            </section>

            @if(!$user->seller)
                <section class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
                    <h2 class="text-lg font-extrabold text-slate-900 border-b pb-3">LaraMart'ta Satıcı Olun</h2>
                    <form action="{{ route('account.seller.apply') }}" method="POST" class="space-y-3 text-sm">
                        @csrf
                        <div>
                            <label class="block font-semibold mb-1">Mağaza Adı</label>
                            <input type="text" name="store_name" required class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Vergi Numarası</label>
                            <input type="text" name="tax_number" required class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">IBAN</label>
                            <input type="text" name="iban" placeholder="TR..." required class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <button type="submit" class="w-full bg-amber-500 text-slate-950 font-bold py-2.5 rounded-lg">
                            Satıcı Başvurusu Gönder
                        </button>
                    </form>
                </section>
            @endif
        </div>
    </div>
</div>
@endsection

