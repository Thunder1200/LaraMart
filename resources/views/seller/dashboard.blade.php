@extends('layouts.app')

@section('title', 'Satıcı Paneli - ' . $seller->store_name)

@section('content')
<div class="space-y-10">
    <div class="bg-slate-900 text-white rounded-2xl p-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-xs uppercase text-emerald-400 font-bold">LaraMart Satıcı Yönetim Merkezi</span>
            <h1 class="text-2xl font-extrabold">{{ $seller->store_name }}</h1>
        </div>
        <a href="{{ route('catalog.seller', $seller->slug) }}" class="bg-amber-500 text-slate-950 font-bold text-xs px-4 py-2 rounded-lg">
            Mağaza Vitrinini Gör &rarr;
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <span class="text-xs text-slate-500 font-semibold">Brüt Ciro</span>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $metrics['gross_revenue'] }} TL</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <span class="text-xs text-slate-500 font-semibold">Platform Komisyon Kesintisi</span>
            <p class="text-2xl font-extrabold text-rose-600 mt-1">-{{ $metrics['commission_total'] }} TL</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <span class="text-xs text-slate-500 font-semibold">Net Satıcı Kazancı</span>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $metrics['net_earnings'] }} TL</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <span class="text-xs text-slate-500 font-semibold">Tamamlanan Alt Sipariş</span>
            <p class="text-2xl font-extrabold text-indigo-600 mt-1">{{ $metrics['sub_order_count'] }} Adet</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            <section class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
                <h2 class="text-xl font-extrabold text-slate-900 border-b pb-3">Mağaza Alt Siparişlerim ({{ $subOrders->count() }})</h2>
                @forelse($subOrders as $subOrder)
                    <div class="border border-slate-200 rounded-xl p-4 space-y-3 text-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <span class="font-extrabold">Alt Sipariş #{{ $subOrder->id }}</span>
                                <span class="text-slate-500">(Ana Sipariş: #{{ $subOrder->order?->order_number }})</span>
                            </div>
                            <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded font-bold text-xs">
                                {{ $subOrder->status->label() }}
                            </span>
                        </div>

                        <div class="text-xs text-slate-600">
                            Tutar: <strong>{{ $subOrder->subtotal }} TL</strong> | Komisyon: <strong>{{ $subOrder->commission_amount }} TL</strong>
                        </div>

                        <form action="{{ route('seller.suborders.status', $subOrder) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-2 pt-2 border-t">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="border rounded px-2 py-1.5 text-xs">
                                @foreach(\App\Enums\SubOrderStatus::cases() as $statusCase)
                                    <option value="{{ $statusCase->value }}" @selected($subOrder->status === $statusCase)>
                                        {{ $statusCase->label() }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="text" name="cargo_company" value="{{ $subOrder->cargo_company }}" placeholder="Kargo Firması" class="border rounded px-2 py-1.5 text-xs">
                            <input type="text" name="tracking_number" value="{{ $subOrder->tracking_number }}" placeholder="Takip No" class="border rounded px-2 py-1.5 text-xs">
                            <button type="submit" class="bg-slate-900 text-white font-bold text-xs rounded px-3 py-1.5">Güncelle</button>
                        </form>

                        @foreach($subOrder->items as $item)
                            @if($item->returnRequest && $item->returnRequest->status->value === 'pending')
                                <div class="bg-rose-50 border border-rose-200 rounded p-3 flex items-center justify-between text-xs">
                                    <span>İade Talebi ({{ $item->product_name }}): "{{ $item->returnRequest->reason }}"</span>
                                    <div class="flex gap-2">
                                        <form action="{{ route('seller.returns.handle', $item->returnRequest) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="decision" value="approved">
                                            <button type="submit" class="bg-emerald-600 text-white px-2.5 py-1 rounded font-bold">Onayla</button>
                                        </form>
                                        <form action="{{ route('seller.returns.handle', $item->returnRequest) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="decision" value="rejected">
                                            <button type="submit" class="bg-rose-600 text-white px-2.5 py-1 rounded font-bold">Reddet</button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Henüz mağazanıza ait sipariş bulunmuyor.</p>
                @endforelse
            </section>

            <section class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
                <h2 class="text-xl font-extrabold text-slate-900 border-b pb-3">Ürünlerim ({{ $products->count() }})</h2>
                <div class="divide-y divide-slate-100 text-sm">
                    @foreach($products as $product)
                        @php($v = $product->variants->first())
                        <div class="py-3 flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <a href="{{ route('catalog.product', $product->slug) }}" class="font-bold text-slate-900 hover:text-amber-600">
                                    {{ $product->name }}
                                </a>
                                <p class="text-xs text-slate-500">
                                    Durum: {{ $product->status->label() }} | Fiyat: {{ $v?->price }} TL | Stok: {{ $v?->stock }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('seller.products.edit', $product) }}" class="bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold px-3 py-1.5 rounded">
                                    Düzenle
                                </a>
                                <form action="{{ route('seller.products.delete', $product) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-rose-100 hover:bg-rose-200 text-rose-700 text-xs font-bold px-3 py-1.5 rounded">
                                        Sil
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="space-y-8">
            <section class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
                <h2 class="text-lg font-extrabold text-slate-900 border-b pb-3">Yeni Ürün Ekle</h2>
                <form action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3 text-sm">
                    @csrf
                    <div>
                        <label class="block font-semibold mb-1">Ürün Adı</label>
                        <input type="text" name="name" required class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Kategori</label>
                        <select name="category_id" required class="w-full border rounded-lg px-3 py-2">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Marka</label>
                        <select name="brand_id" class="w-full border rounded-lg px-3 py-2">
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold mb-1">SKU</label>
                            <input type="text" name="sku" value="SKU-{{ rand(1000,9999) }}" required class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Stok</label>
                            <input type="number" name="stock" value="25" required class="w-full border rounded-lg px-3 py-2">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold mb-1">Fiyat (TL)</label>
                            <input type="number" step="0.01" name="price" required class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">İndirimli (TL)</label>
                            <input type="number" step="0.01" name="discount_price" class="w-full border rounded-lg px-3 py-2">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="color" value="Siyah" placeholder="Renk" class="w-full border rounded-lg px-3 py-2">
                        <input type="text" name="size" value="M" placeholder="Beden" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Durum</label>
                        <select name="status" class="w-full border rounded-lg px-3 py-2">
                            <option value="active">Aktif</option>
                            <option value="draft">Taslak</option>
                            <option value="inactive">Pasif</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Açıklama</label>
                        <textarea name="description" rows="3" required class="w-full border rounded-lg px-3 py-2"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 rounded-lg">
                        Ürünü Kaydet
                    </button>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection

