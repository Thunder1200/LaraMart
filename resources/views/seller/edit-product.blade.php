@extends('layouts.app')

@section('title', 'Ürün Düzenle - ' . $product->name)

@section('content')
@php($variant = $product->variants->first())
<div class="max-w-2xl mx-auto bg-white rounded-xl border border-slate-200 p-8 space-y-6">
    <h1 class="text-2xl font-extrabold text-slate-900">Ürünü Düzenle: {{ $product->name }}</h1>

    <form action="{{ route('seller.products.update', $product) }}" method="POST" class="space-y-4 text-sm">
        @csrf
        @method('PUT')

        <div>
            <label class="block font-semibold mb-1">Ürün Adı</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full border rounded-lg px-3 py-2">
        </div>

        <div>
            <label class="block font-semibold mb-1">Kategori</label>
            <select name="category_id" required class="w-full border rounded-lg px-3 py-2">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected($product->category_id === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-3 gap-3">
            <div>
                <label class="block font-semibold mb-1">Fiyat (TL)</label>
                <input type="number" step="0.01" name="price" value="{{ old('price', $variant?->price) }}" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block font-semibold mb-1">İndirimli Fiyat</label>
                <input type="number" step="0.01" name="discount_price" value="{{ old('discount_price', $variant?->discount_price) }}" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block font-semibold mb-1">Stok</label>
                <input type="number" name="stock" value="{{ old('stock', $variant?->stock) }}" class="w-full border rounded-lg px-3 py-2">
            </div>
        </div>

        <div>
            <label class="block font-semibold mb-1">Durum</label>
            <select name="status" class="w-full border rounded-lg px-3 py-2">
                <option value="active" @selected($product->status->value === 'active')>Aktif</option>
                <option value="draft" @selected($product->status->value === 'draft')>Taslak</option>
                <option value="inactive" @selected($product->status->value === 'inactive')>Pasif</option>
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Açıklama</label>
            <textarea name="description" rows="4" required class="w-full border rounded-lg px-3 py-2">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-slate-900 text-white font-bold px-6 py-2.5 rounded-lg">Değişiklikleri Kaydet</button>
            <a href="{{ route('seller.dashboard') }}" class="bg-slate-200 text-slate-800 font-bold px-6 py-2.5 rounded-lg">İptal</a>
        </div>
    </form>
</div>
@endsection

