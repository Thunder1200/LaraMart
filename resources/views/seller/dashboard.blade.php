<x-layouts.storefront title="Satıcı Paneli — LaraMart">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <span class="inline-block rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">Onaylı Satıcı</span>
                <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $seller->store_name }} — Satıcı Paneli</h1>
                <p class="text-sm text-slate-600">Vergi No: {{ $seller->tax_number }} • Mağaza Slug: {{ $seller->slug }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase text-slate-500">Toplam Ürün Sayısı</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $seller->products_count }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase text-slate-500">Mağaza Durumu</p>
                <p class="mt-2 text-xl font-bold text-emerald-700">Onaylı</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase text-slate-500">IBAN</p>
                <p class="mt-2 text-sm font-mono font-semibold text-slate-800 break-all">{{ $seller->iban }}</p>
            </div>
        </div>
    </section>
</x-layouts.storefront>
