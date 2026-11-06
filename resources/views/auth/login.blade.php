@extends('layouts.app')

@section('title', 'Giriş Yap - LaraMart')

@section('content')
<div class="max-w-md mx-auto bg-white rounded-xl shadow-sm border border-slate-200 p-8">
    <h1 class="text-2xl font-extrabold text-slate-900 mb-6">LaraMart Giriş</h1>

    <form action="{{ route('login') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">E-Posta Adresi</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Şifre</label>
            <input type="password" name="password" required class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm">
        </div>

        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="remember" value="1">
                <span>Beni Hatırla</span>
            </label>
        </div>

        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-3 rounded-lg">
            Giriş Yap
        </button>
    </form>

    <div class="mt-6 pt-6 border-t border-slate-100 text-xs text-slate-500 space-y-1">
        <p class="font-bold text-slate-700">Hızlı Demo Hesapları (Şifre: password):</p>
        <p>• Müşteri: <code>musteri@laramart.test</code></p>
        <p>• Satıcı 1: <code>satici1@laramart.test</code></p>
        <p>• Admin: <code>admin@laramart.test</code></p>
    </div>
</div>
@endsection

