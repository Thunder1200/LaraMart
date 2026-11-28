<x-layouts.storefront title="Bildirimlerim — LaraMart">
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
                <a href="{{ route('customer.account') }}" class="text-xs font-semibold text-indigo-600 hover:underline">← Hesabıma Dön</a>
                <h1 class="mt-1 text-2xl font-extrabold text-slate-900">Bildirimlerim</h1>
                <p class="text-sm text-slate-600">Sipariş durumu, kargo ve iade güncellemeleriniz burada listelenir.</p>
            </div>
            @if ($notifications->isNotEmpty())
                <form method="POST" action="{{ route('customer.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                        Tümünü Okundu İşaretle
                    </button>
                </form>
            @endif
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($notifications->isNotEmpty())
            <div class="space-y-3">
                @foreach ($notifications as $notification)
                    @php $unread = $notification->read_at === null; @endphp
                    <article class="rounded-2xl border p-5 shadow-sm flex flex-wrap items-center justify-between gap-4 {{ $unread ? 'border-indigo-300 bg-indigo-50/40' : 'border-slate-200 bg-white' }}">
                        <div class="space-y-1 min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                @if ($unread)
                                    <span class="h-2.5 w-2.5 rounded-full bg-indigo-600 shrink-0"></span>
                                @endif
                                <h2 class="text-sm font-bold text-slate-900">{{ $notification->data['title'] ?? 'Bildirim' }}</h2>
                                <span class="text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-slate-700">{{ $notification->data['message'] ?? '' }}</p>
                        </div>

                        <form method="POST" action="{{ route('customer.notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit" class="rounded-lg bg-indigo-600 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-indigo-700 transition cursor-pointer">
                                İncele →
                            </button>
                        </form>
                    </article>
                @endforeach
            </div>

            <div>
                {{ $notifications->links() }}
            </div>
        @else
            <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center">
                <p class="text-base font-bold text-slate-700">Henüz bildiriminiz bulunmuyor.</p>
            </div>
        @endif
    </section>
</x-layouts.storefront>
