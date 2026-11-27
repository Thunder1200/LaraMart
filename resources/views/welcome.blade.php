<x-layouts.storefront title="LaraMart — Çok Satıcılı E-Ticaret Pazaryeri">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
        {{-- Hero Banner --}}
        <div class="rounded-2xl bg-gradient-to-r from-indigo-700 via-indigo-600 to-violet-600 p-8 sm:p-12 text-white shadow-lg">
            <div class="max-w-2xl space-y-4">
                <span class="inline-block rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider">
                    Çok Satıcılı Pazaryeri • LaraMart
                </span>
                <h1 class="text-3xl sm:text-4xl font-extrabold leading-tight">
                    Aradığınız Tüm Ürünler, Onaylı Satıcılar ve Güvenli Alışveriş Tek Çatı Altında
                </h1>
                <p class="text-indigo-100 text-sm sm:text-base">
                    Kategorileri mega menüden keşfedin, üye olarak rolünüze özel panelinizden işlemlerinizi kolayca yönetin.
                </p>
                <div class="flex flex-wrap gap-3 pt-2">
                    @auth
                        <a href="{{ route('customer.account') }}" class="rounded-lg bg-white px-5 py-2.5 text-sm font-bold text-indigo-700 hover:bg-indigo-50 transition">
                            Hesabıma Git
                        </a>
                        @if (auth()->user()->hasRole('admin'))
                            <a href="{{ route('admin.categories.index') }}" class="rounded-lg border border-white/40 px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/10 transition">
                                Kategorileri Yönet
                            </a>
                        @endif
                    @else
                        <a href="{{ route('register') }}" class="rounded-lg bg-white px-5 py-2.5 text-sm font-bold text-indigo-700 hover:bg-indigo-50 transition">
                            Hemen Üye Ol
                        </a>
                        <a href="{{ route('login') }}" class="rounded-lg border border-white/40 px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/10 transition">
                            Giriş Yap
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        {{-- Rol Panelleri Özeti --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <span class="inline-block rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">Müşteri • /hesabim</span>
                <h2 class="mt-3 text-lg font-bold text-slate-900">Müşteri Paneli</h2>
                <p class="mt-1 text-sm text-slate-600">Siparişlerinizi, adres defterinizi, favorilerinizi ve hesap bilgilerinizi yönetin.</p>
                <a href="{{ route('customer.account') }}" class="mt-4 inline-block text-sm font-semibold text-indigo-600 hover:text-indigo-800">Panele git →</a>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <span class="inline-block rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Onaylı Satıcı • /satici</span>
                <h2 class="mt-3 text-lg font-bold text-slate-900">Satıcı Paneli</h2>
                <p class="mt-1 text-sm text-slate-600">Yalnızca admin onaylı satıcılara açık mağaza, ürün, stok ve kargo yönetimi.</p>
                <a href="{{ route('seller.dashboard') }}" class="mt-4 inline-block text-sm font-semibold text-emerald-600 hover:text-emerald-800">Satıcı paneline git →</a>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <span class="inline-block rounded-md bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Yönetici • /admin</span>
                <h2 class="mt-3 text-lg font-bold text-slate-900">Admin Paneli</h2>
                <p class="mt-1 text-sm text-slate-600">Yalnızca yöneticilere açık kategori ağacı, komisyon oranları ve platform yönetimi.</p>
                <a href="{{ route('admin.dashboard') }}" class="mt-4 inline-block text-sm font-semibold text-amber-600 hover:text-amber-800">Admin paneline git →</a>
            </div>
        </div>
    </section>
</x-layouts.storefront>
