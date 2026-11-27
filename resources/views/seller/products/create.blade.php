<x-layouts.storefront title="Yeni Ürün Ekle — Satıcı Paneli">
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div>
            <a href="{{ route('seller.products.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">← Ürünlerime Dön</a>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Yeni Ürün Ekle</h1>
        </div>

        <form method="POST" action="{{ route('seller.products.store') }}" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-6">
            @csrf

            @include('seller.products._form')

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('seller.products.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Vazgeç
                </a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 transition">
                    Ürünü Kaydet
                </button>
            </div>
        </form>
    </section>
</x-layouts.storefront>
