@extends('layouts.app')

@section('title', 'Ödeme Hatası - LaraMart')

@section('content')
<div class="max-w-lg mx-auto bg-white rounded-xl border border-rose-200 p-8 text-center space-y-4">
    <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center mx-auto font-extrabold text-2xl">!</div>
    <h1 class="text-2xl font-extrabold text-slate-900">Ödeme İşlemi Tamamlanamadı</h1>
    <p class="text-slate-600 text-sm">{{ $errorMessage }}</p>
    <p class="text-xs text-slate-500">Sepetiniz ve ürün stokları korunmuştur, hiçbir kayıt değiştirilmemiştir.</p>
    <div class="pt-4 flex justify-center gap-4">
        <a href="{{ route('checkout.index') }}" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-5 py-2.5 rounded-lg text-sm">
            Tekrar Dene
        </a>
        <a href="{{ route('cart.index') }}" class="bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold px-5 py-2.5 rounded-lg text-sm">
            Sepete Dön
        </a>
    </div>
</div>
@endsection

