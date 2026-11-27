<x-layouts.storefront title="Kategori Yönetimi — Admin">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">← Admin Paneline Dön</a>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">Kategori Yönetimi</h1>
                <p class="text-sm text-slate-600">Ana ve alt kategorileri, URL slug değerlerini, komisyon oranlarını ve sıralamayı yönetin.</p>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                + Yeni Kategori Ekle
            </a>
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

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Sıra</th>
                        <th class="px-4 py-3">Kategori Adı</th>
                        <th class="px-4 py-3">Üst Kategori</th>
                        <th class="px-4 py-3">Slug</th>
                        <th class="px-4 py-3">Komisyon (%)</th>
                        <th class="px-4 py-3">Ürün</th>
                        <th class="px-4 py-3 text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($categories as $category)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-slate-500">{{ $category->sort }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                @if ($category->parent_id)
                                    <span class="text-slate-400 mr-1">↳</span>
                                @endif
                                {{ $category->name }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $category->parent?->name ?? 'Ana Kategori' }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $category->slug }}</td>
                            <td class="px-4 py-3 text-slate-700">%{{ number_format((float) $category->commission_rate, 2) }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $category->products_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="rounded-md border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                        Düzenle
                                    </a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Bu kategoriyi silmek istediğinize emin misiniz?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                            Sil
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">Henüz kayıtlı kategori bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.storefront>
