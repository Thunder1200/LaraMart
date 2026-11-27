<x-layouts.storefront title="Adres Düzenle — LaraMart">
    <section class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div>
            <a href="{{ route('customer.addresses.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">← Adres Defterime Dön</a>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Adresi Düzenle</h1>
        </div>

        <form
            method="POST"
            action="{{ route('customer.addresses.update', $address) }}"
            x-data='{
                cities: @json($cities),
                selectedCity: @json(old("city", $address->city)),
                selectedDistrict: @json(old("district", $address->district))
            }'
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4"
        >
            @csrf
            @method('PUT')

            <div>
                <label for="title" class="block text-xs font-bold text-slate-700">Adres Başlığı *</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title', $address->title) }}"
                    required
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900"
                >
                @error('title')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="city" class="block text-xs font-bold text-slate-700">İl *</label>
                    <select
                        id="city"
                        name="city"
                        x-model="selectedCity"
                        @change="selectedDistrict = (cities[selectedCity] || [])[0] || ''"
                        required
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900"
                    >
                        @foreach (array_keys($cities) as $cityName)
                            <option value="{{ $cityName }}">{{ $cityName }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="district" class="block text-xs font-bold text-slate-700">İlçe *</label>
                    <select
                        id="district"
                        name="district"
                        x-model="selectedDistrict"
                        required
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900"
                    >
                        <template x-for="district in (cities[selectedCity] || [])" :key="district">
                            <option :value="district" x-text="district"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div>
                <label for="full_address" class="block text-xs font-bold text-slate-700">Açık Adres *</label>
                <textarea
                    id="full_address"
                    name="full_address"
                    rows="3"
                    required
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900"
                >{{ old('full_address', $address->full_address) }}</textarea>
            </div>

            <label class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $address->is_default)) class="rounded border-slate-300 text-indigo-600">
                <span>Varsayılan teslimat adresi</span>
            </label>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('customer.addresses.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Vazgeç</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-bold text-white hover:bg-indigo-700">Güncelle</button>
            </div>
        </form>
    </section>
</x-layouts.storefront>
