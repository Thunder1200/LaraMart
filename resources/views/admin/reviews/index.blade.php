<x-layouts.storefront title="Yorum Moderasyonu — Admin Paneli">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-indigo-600 hover:underline">← Admin Paneline Dön</a>
                <h1 class="mt-1 text-2xl font-extrabold text-slate-900">Yorum Moderasyonu</h1>
                <p class="text-sm text-slate-600">Müşteri değerlendirmeleri yönetici onayının ardından ürün sayfalarında görünür.</p>
            </div>
            <span class="rounded-full bg-amber-100 px-3.5 py-1.5 text-xs font-bold text-amber-800">
                Onay Bekleyen: {{ $pendingCount }}
            </span>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-2">
            @foreach (['bekleyen' => 'Onay Bekleyenler', 'onayli' => 'Onaylı Yorumlar', 'tumu' => 'Tümü'] as $key => $label)
                <a
                    href="{{ route('admin.reviews.index', ['durum' => $key]) }}"
                    class="rounded-full px-3.5 py-1.5 text-xs font-bold transition {{ $filter === $key ? 'bg-indigo-600 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:border-indigo-300' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($reviews->isNotEmpty())
            <div class="space-y-4">
                @foreach ($reviews as $review)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm flex flex-wrap items-start justify-between gap-4">
                        <div class="space-y-1.5 min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="font-bold text-slate-900">{{ $review->user?->name ?? 'Müşteri' }}</span>
                                <span class="text-amber-500 font-bold">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                                <span class="rounded-full px-2 py-0.5 font-bold {{ $review->is_approved ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $review->is_approved ? 'Onaylı' : 'Onay Bekliyor' }}
                                </span>
                                <span class="text-slate-400">{{ $review->created_at->format('d.m.Y H:i') }}</span>
                            </div>
                            @if ($review->product)
                                <a href="{{ route('products.show', $review->product->slug) }}" class="inline-block text-xs font-semibold text-indigo-600 hover:underline">
                                    Ürün: {{ $review->product->name }}
                                </a>
                            @endif
                            <p class="text-sm text-slate-700">{{ $review->comment }}</p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if (! $review->is_approved)
                                <form method="POST" action="{{ route('admin.reviews.approve', $review) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-lg bg-emerald-600 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-emerald-700 transition cursor-pointer">
                                        Onayla
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg bg-rose-600 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-rose-700 transition cursor-pointer">
                                    Sil
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <div>
                {{ $reviews->links() }}
            </div>
        @else
            <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center">
                <p class="text-base font-bold text-slate-700">Bu filtrede yorum bulunmuyor.</p>
            </div>
        @endif
    </section>
</x-layouts.storefront>
