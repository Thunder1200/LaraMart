<x-layouts.storefront :title="$product->name . ' — LaraMart'">
    @php
        $specs = $product->formattedAttributes();
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">
        {{-- Breadcrumb --}}
        <nav aria-label="Sayfa Yolu" class="text-xs text-slate-500 flex flex-wrap items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-indigo-700">Ana Sayfa</a>
            @if ($product->category?->parent)
                <span>/</span>
                <a href="{{ route('categories.show', $product->category->parent->slug) }}" class="hover:text-indigo-700">
                    {{ $product->category->parent->name }}
                </a>
            @endif
            @if ($product->category)
                <span>/</span>
                <a href="{{ route('categories.show', $product->category->slug) }}" class="hover:text-indigo-700">
                    {{ $product->category->name }}
                </a>
            @endif
            <span>/</span>
            <span class="font-semibold text-slate-800">{{ $product->name }}</span>
        </nav>

        {{-- Livewire Ürün Detay & Varyant Seçimi --}}
        <livewire:storefront.product-detail :product="$product" />

        {{-- Ürün Açıklaması & Teknik Özellikler --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <section class="{{ $specs !== [] ? 'lg:col-span-7' : 'lg:col-span-12' }} rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Ürün Açıklaması</h2>
                <div class="mt-3 text-sm leading-relaxed text-slate-700 whitespace-pre-line">
                    {{ $product->description ?: 'Bu ürün için detaylı açıklama yakında eklenecektir.' }}
                </div>
            </section>

            @if ($specs !== [])
                <section class="lg:col-span-5 rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900">Teknik Özellikler</h2>
                    <dl class="mt-4 divide-y divide-slate-100 text-sm">
                        @if ($product->brand)
                            <div class="py-2.5 flex items-center justify-between gap-4">
                                <dt class="text-slate-500">Marka</dt>
                                <dd class="font-semibold text-slate-900 text-right">{{ $product->brand->name }}</dd>
                            </div>
                        @endif
                        @foreach ($specs as $label => $value)
                            <div class="py-2.5 flex items-center justify-between gap-4">
                                <dt class="text-slate-500">{{ $label }}</dt>
                                <dd class="font-semibold text-slate-900 text-right">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endif
        </div>

        {{-- Onaylı Müşteri Değerlendirmeleri --}}
        <section id="yorum-yaz" class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Müşteri Değerlendirmeleri</h2>
                    <p class="text-xs text-slate-500">Yalnızca teslim edilmiş ürünü satın alan müşterilerin onaylı yorumları yayınlanır</p>
                </div>
                <div class="flex items-center gap-2 rounded-xl bg-amber-50 border border-amber-200 px-4 py-2">
                    <span class="text-xl text-amber-500">★</span>
                    <span class="text-lg font-extrabold text-slate-900">{{ number_format((float) ($product->approved_reviews_avg_rating ?? 0), 1) }}</span>
                    <span class="text-xs text-slate-600">({{ $product->approved_reviews_count ?? 0 }} değerlendirme)</span>
                </div>
            </div>

            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Yalnızca teslim edilmiş ürünü satın alan müşteri yorum formunu görebilir --}}
            @if ($eligibleOrderItem)
                <form
                    method="POST"
                    action="{{ route('customer.reviews.store', $product->slug) }}"
                    data-test="review-form"
                    x-data="{ rating: {{ (int) old('rating', 5) }} }"
                    class="rounded-xl border border-indigo-200 bg-indigo-50/40 p-5 space-y-4"
                >
                    @csrf
                    <input type="hidden" name="order_item_id" value="{{ $eligibleOrderItem->id }}">
                    <input type="hidden" name="rating" :value="rating">

                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900">Bu Ürünü Değerlendir</h3>
                            <p class="text-xs text-slate-600">Doğrulanmış satın alma • Yorumunuz admin onayından sonra yayınlanır.</p>
                        </div>
                        <div class="flex items-center gap-1" role="radiogroup" aria-label="Puanınız">
                            @for ($star = 1; $star <= 5; $star++)
                                <button
                                    type="button"
                                    @click="rating = {{ $star }}"
                                    :class="rating >= {{ $star }} ? 'text-amber-500 scale-110' : 'text-slate-300'"
                                    class="text-2xl leading-none transition cursor-pointer"
                                    aria-label="{{ $star }} yıldız"
                                >
                                    ★
                                </button>
                            @endfor
                            <span class="ml-2 text-xs font-bold text-slate-700" x-text="rating + ' / 5'">5 / 5</span>
                        </div>
                    </div>

                    <div>
                        <label for="review-comment" class="block text-xs font-bold text-slate-700">Yorumunuz</label>
                        <textarea
                            id="review-comment"
                            name="comment"
                            rows="3"
                            required
                            minlength="10"
                            placeholder="Ürün kalitesi, kargo süreci ve deneyiminizi paylaşın (en az 10 karakter)..."
                            class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800"
                        >{{ old('comment') }}</textarea>
                        @error('comment')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="rounded-lg bg-indigo-600 px-5 py-2 text-xs font-bold text-white hover:bg-indigo-700 transition cursor-pointer"
                    >
                        Değerlendirmeyi Gönder
                    </button>
                </form>
            @elseif ($pendingUserReview)
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-800">
                    Değerlendirmeniz alındı ({{ $pendingUserReview->rating }} ★). Yönetici onayının ardından burada yayınlanacaktır.
                </div>
            @endif

            @if ($product->approvedReviews->isNotEmpty())
                <div class="divide-y divide-slate-100">
                    @foreach ($product->approvedReviews as $review)
                        <article class="py-4 space-y-1.5">
                            <div class="flex items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900">{{ $review->user?->name ?? 'Müşteri' }}</span>
                                    <span class="text-amber-500 font-bold">
                                        {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                                    </span>
                                </div>
                                <span class="text-slate-400">{{ $review->created_at->format('d.m.Y') }}</span>
                            </div>
                            <p class="text-sm text-slate-700">{{ $review->comment }}</p>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500">Bu ürün için henüz onaylı değerlendirme bulunmuyor.</p>
            @endif
        </section>

        {{-- Benzer Ürünler --}}
        @if ($relatedProducts->isNotEmpty())
            <section class="space-y-5">
                <h2 class="text-xl font-extrabold text-slate-900">Benzer Ürünler</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($relatedProducts as $related)
                        <x-storefront.product-card :product="$related" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.storefront>
