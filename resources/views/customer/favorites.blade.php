<x-layouts.storefront title="Favorilerim — LaraMart">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
                <a href="{{ route('customer.account') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">← Hesabıma Dön</a>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">Favori Ürünlerim</h1>
                <p class="text-sm text-slate-600">Beğendiğiniz ürünleri buradan takip edebilir, kalp ikonuna tıklayarak çıkarabilirsiniz.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($products->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($products as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>

            <div>
                {{ $products->links() }}
            </div>
        @else
            <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center space-y-3">
                <p class="text-base font-bold text-slate-800">Henüz favori ürününüz yok.</p>
                <a href="{{ route('home') }}" class="inline-block rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    Ürünleri Keşfet
                </a>
            </div>
        @endif
    </section>
</x-layouts.storefront>
