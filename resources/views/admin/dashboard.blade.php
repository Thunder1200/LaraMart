@extends('layouts.app')

@section('title', 'Yönetim Paneli (Admin) - LaraMart')

@section('content')
<div class="space-y-10">
    <div class="bg-indigo-950 text-white rounded-2xl p-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-xs uppercase text-amber-400 font-bold">LaraMart Yönetim & Raporlama Merkezi</span>
            <h1 class="text-2xl font-extrabold">Platform Genel Bakış</h1>
        </div>
        <a href="{{ route('admin.orders.export') }}" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs px-4 py-2.5 rounded-lg">
            Siparişleri Dışa Aktar (CSV / Excel)
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <span class="text-xs text-slate-500 font-semibold">Toplam Ödenmiş Ciro</span>
            <p class="text-2xl font-extrabold text-slate-900 mt-1" data-testid="admin-total-revenue">{{ $metrics['total_revenue'] }} TL</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <span class="text-xs text-slate-500 font-semibold">Toplam Platform Komisyonu</span>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $metrics['total_commission'] }} TL</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <span class="text-xs text-slate-500 font-semibold">Ödenmiş Sipariş Sayısı</span>
            <p class="text-2xl font-extrabold text-indigo-600 mt-1">{{ $metrics['total_orders'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <span class="text-xs text-slate-500 font-semibold">Kayıtlı Üye / Ürün</span>
            <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ $usersCount }} Üye / {{ $productsCount }} Ürün</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            <section class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
                <h2 class="text-xl font-extrabold text-slate-900 border-b pb-3">Satıcı Başvuruları ve Mağazalar</h2>
                <div class="divide-y divide-slate-100 text-sm">
                    @foreach($sellers as $seller)
                        <div class="py-3 flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <span class="font-bold text-slate-900">{{ $seller->store_name }}</span>
                                <span class="text-xs text-slate-500 ml-1">({{ $seller->user?->email }})</span>
                                <p class="text-xs text-slate-500">Vergi No: {{ $seller->tax_number }} | IBAN: {{ $seller->iban }} | Durum: <strong>{{ $seller->status->label() }}</strong></p>
                            </div>
                            <div class="flex gap-2">
                                @if($seller->status->value !== 'onayli')
                                    <form action="{{ route('admin.sellers.approve', $seller) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-emerald-600 text-white text-xs font-bold px-3 py-1.5 rounded">Onayla</button>
                                    </form>
                                @endif
                                @if($seller->status->value !== 'reddedildi')
                                    <form action="{{ route('admin.sellers.reject', $seller) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-rose-600 text-white text-xs font-bold px-3 py-1.5 rounded">Reddet</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
                <h2 class="text-xl font-extrabold text-slate-900 border-b pb-3">En Çok Satan 10 Ürün</h2>
                <div class="divide-y divide-slate-100 text-sm">
                    @foreach($metrics['top_products'] as $top)
                        <div class="py-2.5 flex justify-between">
                            <span class="font-semibold">{{ $top->product_name }}</span>
                            <span class="text-slate-600">{{ $top->total_sold }} Adet ({{ number_format((float)$top->total_amount, 2) }} TL)</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
                <h2 class="text-xl font-extrabold text-slate-900 border-b pb-3">Yorum Onay Merkezi</h2>
                <div class="divide-y divide-slate-100 text-sm">
                    @foreach($reviews as $rev)
                        <div class="py-3 flex items-center justify-between gap-4">
                            <div>
                                <span class="font-bold">{{ $rev->product?->name }}</span> (★ {{ $rev->rating }})
                                <p class="text-xs text-slate-600">"{{ $rev->comment }}" — {{ $rev->user?->name }}</p>
                            </div>
                            @if(!$rev->is_approved)
                                <form action="{{ route('admin.reviews.approve', $rev) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-indigo-600 text-white text-xs font-bold px-3 py-1.5 rounded">Onayla</button>
                                </form>
                            @else
                                <span class="text-xs text-emerald-700 font-bold">Yayında</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="space-y-8">
            <section class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
                <h2 class="text-lg font-extrabold text-slate-900 border-b pb-3">Kategori ve Komisyon Yönetimi</h2>
                <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-3 text-sm">
                    @csrf
                    <div>
                        <label class="block font-semibold mb-1">Kategori Adı</label>
                        <input type="text" name="name" required class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Üst Kategori</label>
                        <select name="parent_id" class="w-full border rounded-lg px-3 py-2">
                            <option value="">Ana Kategori</option>
                            @foreach($categories->whereNull('parent_id') as $rootCat)
                                <option value="{{ $rootCat->id }}">{{ $rootCat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold mb-1">Komisyon (%)</label>
                            <input type="number" step="0.01" name="commission_rate" value="10.00" required class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Sıra</label>
                            <input type="number" name="sort" value="10" class="w-full border rounded-lg px-3 py-2">
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-slate-900 text-white font-bold py-2 rounded-lg">Kategori Ekle</button>
                </form>

                <div class="divide-y divide-slate-100 text-xs pt-3 max-h-80 overflow-y-auto">
                    @foreach($categories as $cat)
                        <div class="py-2 flex items-center justify-between">
                            <span>{{ $cat->parent ? $cat->parent->name . ' > ' : '' }}<strong>{{ $cat->name }}</strong> (%{{ $cat->commission_rate }})</span>
                            <form action="{{ route('admin.categories.delete', $cat) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 font-bold">Sil</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

