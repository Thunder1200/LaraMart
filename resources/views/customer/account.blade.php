<x-layouts.storefront title="Hesabım — LaraMart">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Hesabım</h1>
                <p class="text-sm text-slate-600">Hoş geldiniz, {{ $user->name }} ({{ $user->email }})</p>
            </div>
            <div class="flex flex-wrap gap-2.5">
                <a href="{{ route('cart.index') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    Sepetime Git
                </a>
                <a href="{{ route('settings.profile') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Profil &amp; Şifre Ayarları
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <a href="{{ route('customer.addresses.index') }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm hover:border-indigo-400 transition block">
                <p class="text-xs font-semibold uppercase text-slate-500">Kayıtlı Adreslerim</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $user->addresses_count }}</p>
                <span class="mt-3 inline-block text-xs font-bold text-indigo-600">Adres Defterini Yönet →</span>
            </a>

            <a href="{{ route('customer.favorites.index') }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm hover:border-rose-400 transition block">
                <p class="text-xs font-semibold uppercase text-slate-500">Favori Ürünlerim</p>
                <p class="mt-2 text-3xl font-extrabold text-rose-600">{{ $user->favorites_count }}</p>
                <span class="mt-3 inline-block text-xs font-bold text-rose-600">Favorilerimi Gör →</span>
            </a>

            <a href="{{ route('customer.orders.index') }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm hover:border-indigo-400 transition block">
                <p class="text-xs font-semibold uppercase text-slate-500">Siparişlerim</p>
                <p class="mt-2 text-3xl font-extrabold text-indigo-700">{{ $user->orders_count }}</p>
                <span class="mt-3 inline-block text-xs font-bold text-indigo-600">Siparişlerimi Gör →</span>
            </a>
        </div>
    </section>
</x-layouts.storefront>
