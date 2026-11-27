<x-layouts.storefront title="Hesabım — LaraMart">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Hesabım</h1>
                <p class="text-sm text-slate-600">Hoş geldiniz, {{ $user->name }} ({{ $user->email }})</p>
            </div>
            <a href="{{ route('settings.profile') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Profil &amp; Şifre Ayarları
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase text-slate-500">Kayıtlı Adreslerim</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $user->addresses_count }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase text-slate-500">Favori Ürünlerim</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $user->favorites_count }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase text-slate-500">Siparişlerim</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $user->orders_count }}</p>
            </div>
        </div>
    </section>
</x-layouts.storefront>
