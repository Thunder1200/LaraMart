@props(['title' => 'LaraMart — Çok Satıcılı E-Ticaret Pazaryeri'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-900 antialiased">
    {{-- Üst Bilgi Şeridi --}}
    <div class="bg-slate-900 text-slate-200 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex flex-wrap items-center justify-between gap-2">
            <span>Çok Satıcılı Güvenli Pazaryeri • 300 TL Üzeri Siparişlerde Ücretsiz Kargo</span>
            <div class="flex items-center gap-4">
                @auth
                    @if (auth()->user()->hasRole('admin'))
                        <a href="{{ route('admin.dashboard') }}" class="text-amber-300 hover:text-amber-200 font-medium">Admin Paneli (/admin)</a>
                    @endif
                    @if (auth()->user()->hasRole('seller') && auth()->user()->seller?->status === 'approved')
                        <a href="{{ route('seller.dashboard') }}" class="text-emerald-300 hover:text-emerald-200 font-medium">Satıcı Paneli (/satici)</a>
                    @else
                        <a href="{{ route('seller.apply.create') }}" class="text-emerald-300 hover:text-emerald-200 font-medium">Satıcı Ol</a>
                    @endif
                    <a href="{{ route('customer.account') }}" class="hover:text-white">Hesabım</a>
                @else
                    <a href="{{ route('login') }}" class="hover:text-white">Giriş Yap</a>
                    <a href="{{ route('register') }}" class="hover:text-white">Üye Ol</a>
                @endauth
            </div>
        </div>
    </div>

    {{-- Header: Logo, Arama Kutusu, Sepet İkonu, Hesabım Menüsü --}}
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                {{-- Logo --}}
                <div class="flex items-center justify-between">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 text-2xl font-extrabold tracking-tight text-indigo-700">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-white text-lg">L</span>
                        <span>LaraMart</span>
                    </a>
                </div>

                {{-- Arama Kutusu (Livewire Autocomplete) --}}
                <livewire:storefront.search-bar />

                {{-- Sepet ve Hesabım Menüsü --}}
                <div class="flex items-center justify-end gap-3">
                    <a
                        href="{{ route('cart.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 hover:border-indigo-300 hover:text-indigo-700 transition"
                        aria-label="Sepetim"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                        </svg>
                        <span>Sepet</span>
                        <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-bold text-indigo-700">{{ $cartItemCount ?? 0 }}</span>
                    </a>

                    @auth
                        <div class="relative group">
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-3.5 py-2 text-sm font-medium text-white hover:bg-slate-800 transition"
                            >
                                <span>{{ auth()->user()->name }}</span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div class="invisible opacity-0 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100 transition absolute right-0 mt-2 w-56 rounded-xl border border-slate-200 bg-white shadow-lg py-2 z-50">
                                <div class="px-4 py-2 border-b border-slate-100">
                                    <p class="text-xs text-slate-500">Giriş yapılan hesap</p>
                                    <p class="text-sm font-semibold text-slate-900 truncate">{{ auth()->user()->email }}</p>
                                </div>
                                <a href="{{ route('customer.account') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Hesabım (/hesabim)</a>
                                <a href="{{ route('customer.orders.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Siparişlerim</a>
                                <a href="{{ route('customer.favorites.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Favorilerim</a>
                                <a href="{{ route('customer.addresses.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Adres Defterim</a>
                                @if (auth()->user()->hasRole('seller') && auth()->user()->seller?->status === 'approved')
                                    <a href="{{ route('seller.dashboard') }}" class="block px-4 py-2 text-sm text-emerald-700 font-medium hover:bg-emerald-50">Satıcı Paneli (/satici)</a>
                                @endif
                                @if (auth()->user()->hasRole('admin'))
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-indigo-700 font-medium hover:bg-indigo-50">Admin Paneli (/admin)</a>
                                    <a href="{{ route('admin.categories.index') }}" class="block px-4 py-2 text-sm text-indigo-700 hover:bg-indigo-50">Kategori Yönetimi</a>
                                @endif
                                <a href="{{ route('settings.profile') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Profil Ayarları</a>
                                <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 mt-1 pt-1">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-rose-600 hover:bg-rose-50">Çıkış Yap</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 transition">Giriş Yap</a>
                        <a href="{{ route('register') }}" class="rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition">Üye Ol</a>
                    @endauth
                </div>
            </div>
        </div>

        {{-- Veritabanından Çekilen Mega Menü (Kategoriler) --}}
        <nav aria-label="Kategori Mega Menü" class="bg-slate-100 border-t border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <ul class="flex flex-wrap items-center gap-1 py-2 text-sm">
                    @forelse ($megaMenuCategories as $category)
                        <li class="relative group">
                            <a
                                href="{{ route('categories.show', $category->slug) }}"
                                class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 font-semibold text-slate-700 hover:bg-white hover:text-indigo-700 transition"
                            >
                                <span>{{ $category->name }}</span>
                                @if ($category->children->isNotEmpty())
                                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                @endif
                            </a>

                            @if ($category->children->isNotEmpty())
                                <div class="invisible opacity-0 group-hover:visible group-hover:opacity-100 transition absolute left-0 top-full mt-1 w-64 rounded-xl border border-slate-200 bg-white p-3 shadow-xl z-50">
                                    <p class="px-2 pb-1.5 text-xs font-bold uppercase tracking-wider text-slate-400">{{ $category->name }}</p>
                                    <ul class="space-y-1">
                                        @foreach ($category->children as $child)
                                            <li>
                                                <a
                                                    href="{{ route('categories.show', $child->slug) }}"
                                                    class="block rounded-md px-2.5 py-1.5 text-sm text-slate-700 hover:bg-indigo-50 hover:text-indigo-700"
                                                >
                                                    {{ $child->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </li>
                    @empty
                        <li class="px-3 py-1.5 text-slate-500">Henüz kategori eklenmedi.</li>
                    @endforelse
                </ul>
            </div>
        </nav>
    </header>

    {{-- İçerik --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-slate-900 text-slate-300 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 text-sm">
            <div>
                <h3 class="text-white font-bold text-base mb-3">LaraMart</h3>
                <p class="text-slate-400 leading-relaxed">
                    Laravel 13 ile geliştirilen çok satıcılı e-ticaret pazaryeri platformu.
                </p>
            </div>
            <div>
                <h3 class="text-white font-semibold mb-3">Müşteri Hizmetleri</h3>
                <ul class="space-y-2 text-slate-400">
                    <li><a href="{{ route('customer.account') }}" class="hover:text-white">Hesabım (/hesabim)</a></li>
                    <li>14 Gün İade Güvencesi</li>
                    <li>Sipariş ve Kargo Takibi</li>
                </ul>
            </div>
            <div>
                <h3 class="text-white font-semibold mb-3">Satıcılar İçin</h3>
                <ul class="space-y-2 text-slate-400">
                    <li><a href="{{ route('seller.dashboard') }}" class="hover:text-white">Satıcı Paneli (/satici)</a></li>
                    <li>Kategori Bazlı Komisyon</li>
                    <li>Mağaza ve Stok Yönetimi</li>
                </ul>
            </div>
            <div>
                <h3 class="text-white font-semibold mb-3">Yönetim ve Güvenlik</h3>
                <ul class="space-y-2 text-slate-400">
                    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-white">Admin Paneli (/admin)</a></li>
                    <li>E-posta Doğrulama &amp; Rol Bazlı Yetki</li>
                    <li>KVKK ve Güvenli Alışveriş</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-800 py-4 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} LaraMart — Tüm hakları saklıdır.
        </div>
    </footer>

    @livewireScripts
</body>
</html>
