<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public function fillDemoAccount(string $email): void
    {
        $this->email = $email;
        $this->password = 'password';
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $user = Auth::user();
        $defaultRoute = match (true) {
            $user !== null && $user->hasRole('admin') => route('admin.dashboard', absolute: false),
            $user !== null && $user->hasRole('seller') && $user->seller?->status === 'approved' => route('seller.dashboard', absolute: false),
            default => route('home', absolute: false),
        };

        $this->redirectIntended(default: $defaultRoute);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}; ?>

<div
    x-data="{
        filledEmail: null,
        highlightInputs: false,
        filledTimer: null,
        highlightTimer: null,
        triggerDemoFill(email) {
            this.filledEmail = email;
            this.highlightInputs = false;
            requestAnimationFrame(() => {
                this.highlightInputs = true;
            });
            clearTimeout(this.filledTimer);
            clearTimeout(this.highlightTimer);
            this.highlightTimer = setTimeout(() => {
                this.highlightInputs = false;
            }, 950);
            this.filledTimer = setTimeout(() => {
                if (this.filledEmail === email) {
                    this.filledEmail = null;
                }
            }, 1800);
        }
    }"
    class="flex flex-col gap-6"
>
    <x-auth-header title="LaraMart Hesabına Giriş Yap" description="Alışverişe devam etmek veya panelinize geçmek için bilgilerinizi girin" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-6">
        <!-- Email Address -->
        <div
            :class="highlightInputs ? 'ring-2 ring-emerald-500/80 ring-offset-2 ring-offset-white dark:ring-offset-neutral-950 bg-emerald-50/40 dark:bg-emerald-950/25 rounded-lg p-1 -m-1' : ''"
            class="transition-all duration-300"
        >
            <flux:input wire:model="email" label="E-posta adresi" type="email" name="email" required autofocus autocomplete="email" placeholder="ornek@laramart.test" />
        </div>

        <!-- Password -->
        <div
            :class="highlightInputs ? 'ring-2 ring-emerald-500/80 ring-offset-2 ring-offset-white dark:ring-offset-neutral-950 bg-emerald-50/40 dark:bg-emerald-950/25 rounded-lg p-1 -m-1' : ''"
            class="relative transition-all duration-300"
        >
            <flux:input
                wire:model="password"
                label="Şifre"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="Şifreniz"
            />

            @if (Route::has('password.request'))
                <x-text-link class="absolute right-0 top-0" href="{{ route('password.request') }}">
                    Şifremi unuttum?
                </x-text-link>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" label="Beni hatırla" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full cursor-pointer">Giriş Yap</flux:button>
        </div>
    </form>

    <div class="space-x-1 text-center text-sm text-zinc-600 dark:text-zinc-400">
        Hesabınız yok mu?
        <x-text-link href="{{ route('register') }}">Üye Ol</x-text-link>
        •
        <x-text-link href="{{ route('home') }}">Ana Sayfaya Dön</x-text-link>
    </div>

    {{-- Demo Hesaplar Kutusu --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/60 p-4 space-y-3 text-xs">
        <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-700 pb-2">
            <span class="font-bold text-zinc-800 dark:text-zinc-100">Demo Hesaplar</span>
            <span class="rounded bg-indigo-100 dark:bg-indigo-900/60 px-2 py-0.5 font-mono font-bold text-indigo-700 dark:text-indigo-200">
                Şifre: password
            </span>
        </div>

        @php
            $demoAccounts = [
                ['role' => 'Admin', 'badge' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200', 'email' => 'admin@laramart.test', 'note' => 'Yönetici'],
                ['role' => 'Satıcı 1', 'badge' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200', 'email' => 'satici1@laramart.test', 'note' => 'TechZone Elektronik'],
                ['role' => 'Satıcı 2', 'badge' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200', 'email' => 'satici2@laramart.test', 'note' => 'ModaEvim Giyim'],
                ['role' => 'Satıcı 3', 'badge' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200', 'email' => 'satici3@laramart.test', 'note' => 'EvTrend Yaşam'],
                ['role' => 'Satıcı 4', 'badge' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200', 'email' => 'satici4@laramart.test', 'note' => 'SporPlus Outdoor'],
                ['role' => 'Satıcı 5', 'badge' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200', 'email' => 'satici5@laramart.test', 'note' => 'KozmoDünya Bakım'],
                ['role' => 'Müşteri 1', 'badge' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-200', 'email' => 'musteri1@laramart.test', 'note' => 'musteri1..20'],
                ['role' => 'Müşteri 2', 'badge' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-200', 'email' => 'musteri2@laramart.test', 'note' => '2. Müşteri'],
            ];
        @endphp

        <div class="space-y-1.5">
            @foreach ($demoAccounts as $account)
                <button
                    type="button"
                    wire:click="fillDemoAccount('{{ $account['email'] }}')"
                    @click="triggerDemoFill(@js($account['email']))"
                    :class="filledEmail === @js($account['email'])
                        ? 'border-emerald-400 dark:border-emerald-500/80 bg-emerald-50/70 dark:bg-emerald-950/40 ring-2 ring-emerald-400/30'
                        : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:border-indigo-400 dark:hover:border-indigo-500 hover:bg-indigo-50/30 dark:hover:bg-zinc-800/80'"
                    data-test="demo-fill-{{ $account['email'] }}"
                    class="group w-full flex items-center justify-between gap-2 rounded-lg border px-2.5 py-2 text-left transition-all duration-150 active:scale-[0.97] cursor-pointer"
                >
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="rounded px-1.5 py-0.5 text-[10px] font-bold {{ $account['badge'] }}">{{ $account['role'] }}</span>
                            <span class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">{{ $account['note'] }}</span>
                        </div>
                        <p class="mt-0.5 font-mono text-[11px] font-semibold text-zinc-800 dark:text-zinc-100 truncate">
                            {{ $account['email'] }}
                        </p>
                    </div>
                    <span
                        :class="filledEmail === @js($account['email'])
                            ? 'bg-emerald-600 text-white scale-105 shadow-sm'
                            : 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-300 group-hover:bg-indigo-600 group-hover:text-white'"
                        class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-[11px] font-bold shrink-0 transition-all duration-150"
                    >
                        <template x-if="filledEmail === @js($account['email'])">
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                <span>Dolduruldu</span>
                            </span>
                        </template>
                        <template x-if="filledEmail !== @js($account['email'])">
                            <span>Doldur</span>
                        </template>
                    </span>
                </button>
            @endforeach
        </div>

        <p class="text-[11px] text-zinc-500 dark:text-zinc-400">
            Bir hesaba tıkladığınızda e-posta ve <code>password</code> şifresi forma otomatik dolar. (Diğer müşteriler: <code>musteri3@laramart.test</code> – <code>musteri20@laramart.test</code>)
        </p>
    </div>
</div>
