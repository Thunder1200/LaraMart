<x-layouts.storefront title="Ödeme Başarısız — LaraMart">
    <section class="max-w-xl mx-auto px-4 py-14">
        <div class="rounded-2xl border border-rose-200 bg-white p-8 shadow-sm text-center space-y-5">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-rose-100 text-2xl font-bold text-rose-600">
                !
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900">Ödeme Tamamlanamadı</h1>
            <p class="text-sm text-rose-700 font-medium">{{ $errorMessage }}</p>
            <p class="text-xs text-slate-500">
                Veritabanında hiçbir sipariş oluşturulmadı, stoklarınız ve sepetiniz olduğu gibi korundu.
            </p>
            <div class="flex flex-wrap justify-center gap-3 pt-2">
                <a href="{{ route('checkout.index') }}" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">
                    Ödemeyi Tekrar Dene
                </a>
                <a href="{{ route('cart.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Sepete Dön
                </a>
            </div>
        </div>
    </section>
</x-layouts.storefront>
