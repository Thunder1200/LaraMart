@props(['title' => 'LaraMart — Çok Satıcılı E-Ticaret Pazaryeri'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>{{ $title }}</title>
    <script>
        (function () {
            function resolveTheme() {
                try {
                    const stored = localStorage.getItem('laramart-theme');
                    if (stored === 'dark' || stored === 'light') {
                        return stored;
                    }
                    const fluxStored = localStorage.getItem('flux.appearance');
                    if (fluxStored === 'dark' || fluxStored === 'light') {
                        return fluxStored;
                    }
                } catch (e) {}
                return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            function applyTheme(theme) {
                const normalized = theme === 'dark' ? 'dark' : 'light';
                document.documentElement.classList.toggle('dark', normalized === 'dark');
                document.documentElement.style.colorScheme = normalized;
                return normalized;
            }

            window.laramartTheme = {
                get() {
                    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                },
                set(theme) {
                    const normalized = applyTheme(theme);
                    try {
                        localStorage.setItem('laramart-theme', normalized);
                        localStorage.setItem('flux.appearance', normalized);
                    } catch (e) {}
                    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: normalized } }));
                    return normalized;
                },
                toggle() {
                    return this.set(this.get() === 'dark' ? 'light' : 'dark');
                }
            };

            applyTheme(resolveTheme());

            window.laramartProductCard = function (config) {
                return {
                    productId: config.productId,
                    activeImage: config.activeImage,
                    activeColor: config.activeColor,
                    favorited: Boolean(config.favorited),
                    favoriteLoading: false,
                    favoriteUrl: config.favoriteUrl,
                    loginUrl: config.loginUrl,
                    async toggleFavorite() {
                        if (this.favoriteLoading) {
                            return;
                        }
                        const previous = this.favorited;
                        this.favorited = !previous;
                        this.favoriteLoading = true;
                        try {
                            const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') ?? '';
                            const response = await fetch(this.favoriteUrl, {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': token,
                                },
                            });
                            if (response.status === 401 || response.status === 419 || response.redirected) {
                                window.location.assign(this.loginUrl);
                                return;
                            }
                            if (!response.ok) {
                                this.favorited = previous;
                                return;
                            }
                            const data = await response.json();
                            this.favorited = Boolean(data.favorited);
                            window.dispatchEvent(new CustomEvent('favorite-toggled', {
                                detail: { productId: this.productId, favorited: this.favorited },
                            }));
                        } catch (error) {
                            this.favorited = previous;
                        } finally {
                            this.favoriteLoading = false;
                        }
                    },
                };
            };
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="laramart-storefront min-h-screen flex flex-col bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100 antialiased transition-colors duration-200">
    {{-- Üst Bilgi Şeridi --}}
    <div class="bg-slate-900 dark:bg-slate-950 border-b border-transparent dark:border-slate-800/80 text-slate-200 text-xs">
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

    {{-- Header: Logo, Arama Kutusu, Tema Geçişi, Sepet İkonu, Hesabım Menüsü --}}
    <header class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-40 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                {{-- Logo --}}
                <div class="flex items-center justify-between">
                    <a href="{{ route('home') }}" class="group flex items-center gap-2.5 text-2xl font-extrabold tracking-tight">
                        <img
                            src="{{ asset('images/logo-icon.png') }}"
                            alt="LaraMart Logo"
                            class="h-10 w-10 rounded-xl shadow-sm ring-1 ring-slate-200/70 dark:ring-slate-700/80 group-hover:scale-105 transition-transform duration-150 shrink-0"
                        >
                        <span class="leading-none">
                            <span class="text-slate-900 dark:text-white">Lara</span><span class="bg-gradient-to-r from-indigo-600 to-violet-600 dark:from-indigo-400 dark:to-violet-400 bg-clip-text text-transparent">Mart</span>
                        </span>
                    </a>
                </div>

                {{-- Arama Kutusu (Livewire Autocomplete) --}}
                <livewire:storefront.search-bar />

                {{-- Tema Geçişi, Sepet ve Hesabım Menüsü --}}
                <div class="flex items-center justify-end gap-2.5 sm:gap-3">
                    {{-- Light / Dark Mode Toggle --}}
                    <button
                        type="button"
                        x-data="{ dark: document.documentElement.classList.contains('dark') }"
                        @theme-changed.window="dark = $event.detail.theme === 'dark'"
                        @click="dark = window.laramartTheme.toggle() === 'dark'"
                        :aria-pressed="dark ? 'true' : 'false'"
                        :aria-label="dark ? 'Açık temaya geç' : 'Karanlık temaya geç'"
                        :title="dark ? 'Açık Tema' : 'Karanlık Tema'"
                        data-test="theme-toggle"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:border-indigo-300 dark:hover:border-indigo-500 hover:text-indigo-700 dark:hover:text-indigo-300 active:scale-95 transition cursor-pointer"
                    >
                        <svg x-show="dark" x-cloak class="h-4.5 w-4.5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        </svg>
                        <svg x-show="!dark" class="h-4.5 w-4.5 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                        </svg>
                        <span class="hidden sm:inline text-xs font-semibold" x-text="dark ? 'Açık' : 'Koyu'">Koyu</span>
                    </button>

                    <a
                        href="{{ route('cart.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3.5 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:border-indigo-300 dark:hover:border-indigo-500 hover:text-indigo-700 dark:hover:text-indigo-300 transition"
                        aria-label="Sepetim"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                        </svg>
                        <span>Sepet</span>
                        <span class="rounded-full bg-indigo-100 dark:bg-indigo-900/70 px-2 py-0.5 text-xs font-bold text-indigo-700 dark:text-indigo-300">{{ $cartItemCount ?? 0 }}</span>
                    </a>

                    @auth
                        <div class="relative group">
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-lg bg-slate-900 dark:bg-indigo-600 px-3.5 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:hover:bg-indigo-500 transition cursor-pointer"
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
        <nav aria-label="Kategori Mega Menü" class="bg-slate-100 dark:bg-slate-900/95 border-t border-slate-200 dark:border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <ul class="flex flex-wrap items-center gap-1 py-2 text-sm">
                    @forelse ($megaMenuCategories as $category)
                        <li class="relative group">
                            <a
                                href="{{ route('categories.show', $category->slug) }}"
                                class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 font-semibold text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-800 hover:text-indigo-700 dark:hover:text-indigo-300 transition"
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
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 mb-3">
                    <img
                        src="{{ asset('images/logo-icon.png') }}"
                        alt="LaraMart Logo"
                        class="h-8 w-8 rounded-lg shadow-sm ring-1 ring-slate-700/80"
                    >
                    <span class="text-lg font-extrabold tracking-tight">
                        <span class="text-white">Lara</span><span class="bg-gradient-to-r from-indigo-400 to-violet-400 bg-clip-text text-transparent">Mart</span>
                    </span>
                </a>
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
