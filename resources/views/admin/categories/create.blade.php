<x-layouts.storefront title="Yeni Kategori Ekle — Admin">
    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div>
            <a href="{{ route('admin.categories.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">← Kategori Listesine Dön</a>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Yeni Kategori Ekle</h1>
        </div>

        <form method="POST" action="{{ route('admin.categories.store') }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-6">
            @csrf

            @include('admin.categories._form')

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                <a href="{{ route('admin.categories.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Vazgeç
                </a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                    Kategoriyi Kaydet
                </button>
            </div>
        </form>
    </section>
</x-layouts.storefront>
