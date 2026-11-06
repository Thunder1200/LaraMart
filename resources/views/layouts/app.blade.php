<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LaraMart - Çok Satıcılı E-Ticaret Pazaryeri')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col">
    <header class="bg-slate-900 text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-wrap items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="text-2xl font-extrabold tracking-tight text-amber-400">
                Lara<span class="text-white">Mart</span>
            </a>

            <div class="flex-1 max-w-xl mx-4">
                <livewire:search-autocomplete />
            </div>

            <div class="flex items-center gap-4 text-sm font-medium">
                <a href="{{ route('cart.index') }}" class="bg-slate-800 hover:bg-slate-700 px-4 py-2 rounded-lg flex items-center gap-2">
                    <span>Sepetim</span>
                </a>

                @auth
                    <a href="{{ route('account.index') }}" class="hover:text-amber-400">Hesabım ({{ auth()->user()->name }})</a>

                    @if(auth()->user()->hasRole('seller') && auth()->user()->seller?->isApproved())
                        <a href="{{ route('seller.dashboard') }}" class="bg-emerald-600 hover:bg-emerald-500 px-3 py-1.5 rounded-lg">Satıcı Paneli</a>
                    @endif

                    @if(auth()->user()->hasRole('admin'))
                        <a href="{{ route('admin.dashboard') }}" class="bg-indigo-600 hover:bg-indigo-500 px-3 py-1.5 rounded-lg">Admin Paneli</a>
                    @endif

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-rose-400 hover:text-rose-300">Çıkış</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:text-amber-400">Giriş Yap</a>
                    <a href="{{ route('register') }}" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-4 py-2 rounded-lg">Üye Ol</a>
                @endauth
            </div>
        </div>

        <nav class="bg-slate-800 border-t border-slate-700">
            <div class="max-w-7xl mx-auto px-4 py-2.5 flex flex-wrap items-center gap-6 text-sm">
                <span class="text-amber-400 font-semibold">Kategoriler:</span>
                @foreach($megaMenuCategories ?? [] as $rootCategory)
                    <div class="group relative">
                        <a href="{{ route('catalog.category', $rootCategory->slug) }}" class="hover:text-amber-300 py-1 inline-block font-medium">
                            {{ $rootCategory->name }}
                        </a>
                        @if($rootCategory->children->isNotEmpty())
                            <div class="hidden group-hover:block absolute left-0 top-full bg-white text-slate-900 shadow-xl rounded-lg p-3 min-w-48 z-50 border border-slate-200">
                                @foreach($rootCategory->children as $childCategory)
                                    <a href="{{ route('catalog.category', $childCategory->slug) }}" class="block px-3 py-1.5 rounded hover:bg-slate-100 text-sm">
                                        {{ $childCategory->name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </nav>
    </header>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 py-8">
        @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-slate-900 text-slate-400 border-t border-slate-800 mt-12">
        <div class="max-w-7xl mx-auto px-4 py-8 flex flex-col md:flex-row items-center justify-between text-sm gap-4">
            <div>
                <span class="font-bold text-white">LaraMart Pazaryeri</span> &copy; {{ date('Y') }} — Çok Satıcılı E-Ticaret Platformu
            </div>
            <div class="flex gap-6">
                <a href="{{ route('home') }}" class="hover:text-white">Ana Sayfa</a>
                <a href="{{ route('catalog.search') }}" class="hover:text-white">Tüm Ürünler</a>
                <a href="{{ route('cart.index') }}" class="hover:text-white">Sepet</a>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>

