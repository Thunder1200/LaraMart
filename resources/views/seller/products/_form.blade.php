<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <label for="category_id" class="block text-sm font-semibold text-slate-700">Kategori *</label>
            <select
                id="category_id"
                name="category_id"
                required
                class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none"
            >
                <option value="">Kategori seçin</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>
                        {{ $category->parent_id ? '↳ ' : '' }}{{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="brand_id" class="block text-sm font-semibold text-slate-700">Marka</label>
            <select
                id="brand_id"
                name="brand_id"
                class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none"
            >
                <option value="">Marka seçin (isteğe bağlı)</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}" @selected((string) old('brand_id', $product->brand_id ?? '') === (string) $brand->id)>
                        {{ $brand->name }}
                    </option>
                @endforeach
            </select>
            @error('brand_id')
                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="sm:col-span-2">
            <label for="name" class="block text-sm font-semibold text-slate-700">Ürün Adı *</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $product->name ?? '') }}"
                required
                class="mt-1.5 w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none"
            >
            @error('name')
                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="status" class="block text-sm font-semibold text-slate-700">Yayın Durumu *</label>
            <select
                id="status"
                name="status"
                required
                class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none"
            >
                <option value="active" @selected(old('status', $product->status ?? 'active') === 'active')>Aktif</option>
                <option value="inactive" @selected(old('status', $product->status ?? '') === 'inactive')>Pasif</option>
                <option value="draft" @selected(old('status', $product->status ?? '') === 'draft')>Taslak</option>
            </select>
            @error('status')
                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="description" class="block text-sm font-semibold text-slate-700">Ürün Açıklaması</label>
        <textarea
            id="description"
            name="description"
            rows="4"
            class="mt-1.5 w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none"
        >{{ old('description', $product->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="border-t border-slate-200 pt-5 space-y-4">
        <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-700">Varyant, Fiyat ve Stok Bilgileri</h2>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label for="sku" class="block text-sm font-semibold text-slate-700">SKU Kodu *</label>
                <input
                    type="text"
                    id="sku"
                    name="sku"
                    value="{{ old('sku', $primaryVariant->sku ?? '') }}"
                    required
                    placeholder="Örn: LM-ELK-001"
                    class="mt-1.5 w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm font-mono text-slate-900 focus:border-indigo-500 focus:outline-none"
                >
                @error('sku')
                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="color" class="block text-sm font-semibold text-slate-700">Renk</label>
                <input
                    type="text"
                    id="color"
                    name="color"
                    value="{{ old('color', $primaryVariant->color ?? '') }}"
                    placeholder="Örn: Siyah"
                    class="mt-1.5 w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none"
                >
                @error('color')
                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="size" class="block text-sm font-semibold text-slate-700">Beden</label>
                <input
                    type="text"
                    id="size"
                    name="size"
                    value="{{ old('size', $primaryVariant->size ?? '') }}"
                    placeholder="Örn: M veya Standart"
                    class="mt-1.5 w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none"
                >
                @error('size')
                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label for="price" class="block text-sm font-semibold text-slate-700">Fiyat (TL) *</label>
                <input
                    type="number"
                    step="0.01"
                    min="1"
                    id="price"
                    name="price"
                    value="{{ old('price', $primaryVariant->price ?? '') }}"
                    required
                    class="mt-1.5 w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none"
                >
                @error('price')
                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="discount_price" class="block text-sm font-semibold text-slate-700">İndirimli Fiyat (TL)</label>
                <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    id="discount_price"
                    name="discount_price"
                    value="{{ old('discount_price', $primaryVariant->discount_price ?? '') }}"
                    class="mt-1.5 w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none"
                >
                @error('discount_price')
                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="stock" class="block text-sm font-semibold text-slate-700">Stok Adedi *</label>
                <input
                    type="number"
                    min="0"
                    id="stock"
                    name="stock"
                    value="{{ old('stock', $primaryVariant->stock ?? 10) }}"
                    required
                    class="mt-1.5 w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none"
                >
                @error('stock')
                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-5">
        <label for="images" class="block text-sm font-semibold text-slate-700">Ürün Görselleri (Çoklu yükleme, JPG/PNG/WEBP, maks 2 MB)</label>
        <input
            type="file"
            id="images"
            name="images[]"
            multiple
            accept="image/jpeg,image/png,image/webp"
            class="mt-1.5 block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-xs file:font-bold file:text-indigo-700 hover:file:bg-indigo-100"
        >
        @error('images')
            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
        @enderror
        @error('images.*')
            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
        @enderror
    </div>
</div>
