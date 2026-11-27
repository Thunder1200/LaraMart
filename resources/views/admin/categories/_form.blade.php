<div class="space-y-5">
    <div>
        <label for="parent_id" class="block text-sm font-semibold text-slate-700">Üst Kategori (İsteğe Bağlı)</label>
        <select
            id="parent_id"
            name="parent_id"
            class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
        >
            <option value="">— Ana Kategori —</option>
            @foreach ($parentCategories as $parent)
                <option value="{{ $parent->id }}" @selected((string) old('parent_id', $category->parent_id ?? '') === (string) $parent->id)>
                    {{ $parent->name }}
                </option>
            @endforeach
        </select>
        @error('parent_id')
            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="name" class="block text-sm font-semibold text-slate-700">Kategori Adı *</label>
        <input
            type="text"
            id="name"
            name="name"
            value="{{ old('name', $category->name ?? '') }}"
            required
            class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            placeholder="Örn: Akıllı Telefonlar"
        >
        @error('name')
            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="slug" class="block text-sm font-semibold text-slate-700">URL Kısa Adı (Slug)</label>
        <input
            type="text"
            id="slug"
            name="slug"
            value="{{ old('slug', $category->slug ?? '') }}"
            class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-mono text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            placeholder="Boş bırakırsanız kategori adından otomatik üretilir"
        >
        @error('slug')
            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <label for="commission_rate" class="block text-sm font-semibold text-slate-700">Komisyon Oranı (%) *</label>
            <input
                type="number"
                step="0.01"
                min="0"
                max="100"
                id="commission_rate"
                name="commission_rate"
                value="{{ old('commission_rate', $category->commission_rate ?? '10.00') }}"
                required
                class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            >
            @error('commission_rate')
                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="sort" class="block text-sm font-semibold text-slate-700">Sıralama *</label>
            <input
                type="number"
                min="0"
                max="9999"
                id="sort"
                name="sort"
                value="{{ old('sort', $category->sort ?? 0) }}"
                required
                class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            >
            @error('sort')
                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
