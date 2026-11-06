@extends('layouts.app')

@section('title', 'Üye Ol - LaraMart')

@section('content')
<div class="max-w-md mx-auto bg-white rounded-xl shadow-sm border border-slate-200 p-8">
    <h1 class="text-2xl font-extrabold text-slate-900 mb-6">LaraMart Yeni Üyelik</h1>

    <form action="{{ route('register') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Ad Soyad</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">E-Posta Adresi</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Telefon Numarası</label>
            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="0555..." class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Şifre</label>
            <input type="password" name="password" required class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Şifre Tekrarı</label>
            <input type="password" name="password_confirmation" required class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm">
        </div>

        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-3 rounded-lg">
            Hesap Oluştur
        </button>
    </form>
</div>
@endsection

