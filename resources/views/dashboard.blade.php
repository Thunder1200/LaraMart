<x-layouts.storefront title="Kontrol Paneli — LaraMart">
    @php
        $user = auth()->user();
    @endphp
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div class="rounded-2xl bg-gradient-to-r from-indigo-700 to-violet-600 p-8 text-white shadow-lg flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold">Hoş geldiniz, {{ $user->name }}!</h1>
                <p class="mt-1 text-sm text-indigo-100">LaraMart hesabınızdan alışverişe devam edebilir veya rolünüze ait panelinize geçebilirsiniz.</p>
            </div>
            <a href="{{ route('home') }}" class="rounded-lg bg-white px-5 py-2.5 text-sm font-bold text-indigo-700 hover:bg-indigo-50 transition">
                Alışverişe Başla →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <a href="{{ route('customer.account') }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:border-indigo-400 transition">
                <p class="text-xs font-bold uppercase text-indigo-600">Müşteri</p>
                <h2 class="mt-1 text-lg font-bold text-slate-900">Hesabım</h2>
                <p class="mt-1 text-xs text-slate-600">Siparişlerim, favorilerim ve adreslerim</p>
            </a>

            <a href="{{ route('cart.index') }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:border-indigo-400 transition">
                <p class="text-xs font-bold uppercase text-indigo-600">Alışveriş</p>
                <h2 class="mt-1 text-lg font-bold text-slate-900">Sepetim</h2>
                <p class="mt-1 text-xs text-slate-600">Sepetteki ürünler ve kuponlar</p>
            </a>

            @if ($user->hasRole('seller') && $user->seller?->status === 'approved')
                <a href="{{ route('seller.dashboard') }}" class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-5 shadow-sm hover:border-emerald-400 transition">
                    <p class="text-xs font-bold uppercase text-emerald-700">Onaylı Satıcı</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Satıcı Paneli</h2>
                    <p class="mt-1 text-xs text-slate-600">Ürün, stok ve mağaza yönetimi</p>
                </a>
            @endif

            @if ($user->hasRole('admin'))
                <a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-amber-200 bg-amber-50/50 p-5 shadow-sm hover:border-amber-400 transition">
                    <p class="text-xs font-bold uppercase text-amber-700">Yönetici</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Admin Paneli</h2>
                    <p class="mt-1 text-xs text-slate-600">Kategori, kullanıcı ve platform yönetimi</p>
                </a>
            @endif
        </div>
    </section>
</x-layouts.storefront>
