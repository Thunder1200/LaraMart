<x-layouts.storefront title="3D Secure Doğrulama — iyzico Sandbox">
    <section class="max-w-lg mx-auto px-4 py-12">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-[11px] font-bold text-indigo-800">iyzico Sandbox</span>
                    <h1 class="mt-1 text-xl font-extrabold text-slate-900">3D Secure Doğrulama</h1>
                </div>
                <span class="text-lg font-extrabold text-indigo-700">{{ number_format($total, 2, ',', '.') }} TL</span>
            </div>

            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 text-xs text-slate-600 space-y-1">
                <p>Kart: <strong>**** **** **** {{ $cardLast4 }}</strong></p>
                <p>İşlem Kodu: <code class="font-mono">{{ $token }}</code></p>
                <p class="pt-1 text-indigo-700 font-semibold">Sandbox SMS Doğrulama Kodu: <code>123456</code></p>
            </div>

            <form method="POST" action="{{ route('checkout.iyzico.callback') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="status" value="success">
                <input type="hidden" name="mdStatus" value="1">
                <input type="hidden" name="paymentId" value="{{ $token }}">

                <div>
                    <label for="sms_code" class="block text-xs font-bold text-slate-700">SMS Doğrulama Kodu</label>
                    <input
                        type="text"
                        id="sms_code"
                        name="sms_code"
                        value="123456"
                        required
                        class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-center text-lg font-mono font-bold tracking-widest text-slate-900"
                    >
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    <button
                        type="submit"
                        name="action"
                        value="approve"
                        class="rounded-lg bg-emerald-600 py-3 text-sm font-bold text-white hover:bg-emerald-700 transition"
                    >
                        Ödemeyi Onayla ✓
                    </button>
                    <button
                        type="submit"
                        name="action"
                        value="reject"
                        class="rounded-lg border border-rose-300 bg-rose-50 py-3 text-sm font-bold text-rose-700 hover:bg-rose-100 transition"
                    >
                        Reddet / İptal Et ✕
                    </button>
                </div>
            </form>
        </div>
    </section>
</x-layouts.storefront>
