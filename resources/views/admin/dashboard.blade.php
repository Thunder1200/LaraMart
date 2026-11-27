<x-layouts.storefront title="Admin Paneli — LaraMart">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <span class="inline-block rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-800">Yönetici Paneli</span>
                <h1 class="mt-2 text-2xl font-bold text-slate-900">LaraMart Admin Paneli</h1>
                <p class="text-sm text-slate-600">Platform kategorilerini, kullanıcıları ve satıcıları buradan yönetebilirsiniz.</p>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                Kategori Yönetimi →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase text-slate-500">Toplam Kullanıcı</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stats['users'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase text-slate-500">Satıcı Mağaza</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stats['sellers'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase text-slate-500">Kategori Sayısı</p>
                <p class="mt-2 text-3xl font-extrabold text-indigo-700">{{ $stats['categories'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase text-slate-500">Toplam Ürün</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $stats['products'] }}</p>
            </div>
        </div>
    </section>
</x-layouts.storefront>
