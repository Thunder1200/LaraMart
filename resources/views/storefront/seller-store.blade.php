@extends('layouts.app')

@section('title', $seller->store_name . ' Mağazası - LaraMart')

@section('content')
<div class="space-y-8">
    <div class="bg-slate-900 text-white rounded-2xl p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs uppercase text-amber-400 font-bold">Onaylı LaraMart Satıcısı</span>
            <h1 class="text-3xl font-extrabold mt-1">{{ $seller->store_name }}</h1>
        </div>
        <div class="text-sm text-slate-300">
            Toplam {{ $products->total() }} aktif ürün listeleniyor
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($products as $product)
            @php($firstVariant = $product->variants->first())
            <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col justify-between">
                <div>
                    <a href="{{ route('catalog.product', $product->slug) }}" class="font-bold text-slate-900 hover:text-amber-600">
                        {{ $product->name }}
                    </a>
                    <p class="text-xs text-slate-500 mt-1">{{ $product->category?->name }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-lg font-extrabold">{{ $firstVariant ? $firstVariant->effectivePrice() : '0.00' }} TL</span>
                    <a href="{{ route('catalog.product', $product->slug) }}" class="bg-amber-500 text-slate-950 text-xs font-bold px-3 py-2 rounded-lg">İncele</a>
                </div>
            </div>
        @endforeach
    </div>

    <div>
        {{ $products->links() }}
    </div>
</div>
@endsection

