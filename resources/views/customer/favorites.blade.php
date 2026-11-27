<x-layouts.storefront title="Favorilerim — LaraMart">
    <section
        x-data="{
            remainingCount: {{ $products->count() }},
            removedIds: [],
            handleFavoriteToggled(detail) {
                if (!detail || detail.favorited) {
                    return;
                }
                const id = Number(detail.productId);
                if (this.removedIds.includes(id)) {
                    return;
                }
                this.removedIds.push(id);
                this.remainingCount = Math.max(0, this.remainingCount - 1);
                const card = this.$el.querySelector('[data-favorite-card-id=\'' + id + '\']');
                if (card) {
                    card.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
                    setTimeout(() => card.remove(), 180);
                }
            }
        }"
        @favorite-toggled.window="handleFavoriteToggled($event.detail)"
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6"
    >
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-4">
            <div>
                <a href="{{ route('customer.account') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">← Hesabıma Dön</a>
                <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">Favori Ürünlerim</h1>
                <p class="text-sm text-slate-600 dark:text-slate-400">Beğendiğiniz ürünleri buradan takip edebilir, kalp ikonuna tıklayarak anında çıkarabilirsiniz.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($products->isNotEmpty())
            <div
                x-show="remainingCount > 0"
                data-test="favorites-grid"
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
            >
                @foreach ($products as $product)
                    <div
                        data-favorite-card-id="{{ $product->id }}"
                        class="transition-all duration-200"
                    >
                        <x-storefront.product-card :product="$product" />
                    </div>
                @endforeach
            </div>

            <div x-show="remainingCount > 0">
                {{ $products->links() }}
            </div>

            <div
                x-show="remainingCount === 0"
                x-cloak
                x-transition.opacity.duration.200ms
                data-test="favorites-empty-state"
                class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-12 text-center space-y-3"
            >
                <p class="text-base font-bold text-slate-800 dark:text-slate-100">Favori ürününüz bulunmuyor.</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">Beğendiğiniz ürünleri kalp simgesine tıklayarak favorilerinize ekleyebilirsiniz.</p>
                <a href="{{ route('home') }}" class="inline-block rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                    Ürünleri Keşfet
                </a>
            </div>
        @else
            <div
                data-test="favorites-empty-state"
                class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-12 text-center space-y-3"
            >
                <p class="text-base font-bold text-slate-800 dark:text-slate-100">Favori ürününüz bulunmuyor.</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">Beğendiğiniz ürünleri kalp simgesine tıklayarak favorilerinize ekleyebilirsiniz.</p>
                <a href="{{ route('home') }}" class="inline-block rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                    Ürünleri Keşfet
                </a>
            </div>
        @endif
    </section>
</x-layouts.storefront>
