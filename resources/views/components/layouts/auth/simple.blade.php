<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white text-zinc-900 antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900 dark:text-zinc-100 transition-colors duration-200">
        <div class="fixed top-4 right-4 z-50">
            <button
                type="button"
                x-data="{ dark: document.documentElement.classList.contains('dark') }"
                @theme-changed.window="dark = $event.detail.theme === 'dark'"
                @click="dark = window.laramartTheme.toggle() === 'dark'"
                :aria-label="dark ? 'Açık temaya geç' : 'Karanlık temaya geç'"
                data-test="theme-toggle"
                class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white/90 dark:bg-zinc-900/90 px-3 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-200 shadow-sm hover:border-indigo-400 active:scale-95 transition cursor-pointer"
            >
                <svg x-show="dark" x-cloak class="h-4 w-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                </svg>
                <svg x-show="!dark" class="h-4 w-4 text-zinc-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                </svg>
                <span x-text="dark ? 'Açık Tema' : 'Karanlık Tema'">Tema</span>
            </button>
        </div>
        <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-2">
                <a href="{{ route('home') }}" class="group flex flex-col items-center gap-2.5 font-medium">
                    <img
                        src="{{ asset('images/logo-icon.png') }}"
                        alt="LaraMart Logo"
                        class="h-14 w-14 rounded-2xl shadow-md ring-1 ring-zinc-200 dark:ring-zinc-700 group-hover:scale-105 transition-transform duration-150"
                    />
                    <span class="text-2xl font-extrabold tracking-tight leading-none">
                        <span class="text-zinc-900 dark:text-white">Lara</span><span class="bg-gradient-to-r from-indigo-600 to-violet-600 dark:from-indigo-400 dark:to-violet-400 bg-clip-text text-transparent">Mart</span>
                    </span>
                </a>
                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
