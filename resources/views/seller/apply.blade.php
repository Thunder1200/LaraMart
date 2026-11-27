<x-layouts.storefront title="Satıcı Başvurusu — LaraMart">
    <section class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div>
            <span class="inline-block rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">LaraMart Satıcı Programı</span>
            <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900">Satıcı Başvurusu</h1>
            <p class="mt-1 text-sm text-slate-600">
                Mağazanızı açarak ürünlerinizi LaraMart müşterilerine ulaştırabilirsiniz. Başvurunuz admin tarafından onaylandıktan sonra satıcı paneliniz aktif olur.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($seller)
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-900">{{ $seller->store_name }}</h2>
                    @if ($seller->status === 'approved')
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">Onaylı</span>
                    @elseif ($seller->status === 'rejected')
                        <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-800">Reddedildi</span>
                    @else
                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">Onay Bekliyor (Beklemede)</span>
                    @endif
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-xs font-semibold text-slate-500">Vergi Numarası</dt>
                        <dd class="font-mono font-semibold text-slate-800">{{ $seller->tax_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-500">IBAN</dt>
                        <dd class="font-mono font-semibold text-slate-800 break-all">{{ $seller->iban }}</dd>
                    </div>
                </dl>

                @if ($seller->status === 'approved')
                    <div class="pt-2">
                        <a href="{{ route('seller.dashboard') }}" class="inline-block rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-700 transition">
                            Satıcı Paneline Git →
                        </a>
                    </div>
                @elseif ($seller->status === 'pending')
                    <p class="rounded-lg bg-amber-50 px-4 py-3 text-xs font-medium text-amber-800">
                        Başvurunuz inceleme aşamasındadır. Admin onaylayana kadar satıcı paneli erişime kapalıdır.
                    </p>
                @endif
            </div>
        @else
            <form method="POST" action="{{ route('seller.apply.store') }}" class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-5">
                @csrf

                <div>
                    <label for="store_name" class="block text-sm font-semibold text-slate-700">Mağaza Adı *</label>
                    <input
                        type="text"
                        id="store_name"
                        name="store_name"
                        value="{{ old('store_name') }}"
                        required
                        placeholder="Örn: Anadolu Teknoloji"
                        class="mt-1.5 w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    >
                    @error('store_name')
                        <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tax_number" class="block text-sm font-semibold text-slate-700">Vergi Numarası *</label>
                    <input
                        type="text"
                        id="tax_number"
                        name="tax_number"
                        value="{{ old('tax_number') }}"
                        required
                        placeholder="10 haneli vergi numarası"
                        class="mt-1.5 w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm font-mono text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    >
                    @error('tax_number')
                        <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="iban" class="block text-sm font-semibold text-slate-700">IBAN *</label>
                    <input
                        type="text"
                        id="iban"
                        name="iban"
                        value="{{ old('iban') }}"
                        required
                        placeholder="TR000000000000000000000000"
                        class="mt-1.5 w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm font-mono text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    >
                    @error('iban')
                        <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full rounded-lg bg-indigo-600 py-3 text-sm font-bold text-white hover:bg-indigo-700 transition">
                        Satıcı Başvurusunu Gönder
                    </button>
                </div>
            </form>
        @endif
    </section>
</x-layouts.storefront>
