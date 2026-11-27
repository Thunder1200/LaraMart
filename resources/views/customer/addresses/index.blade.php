<x-layouts.storefront title="Adres Defterim — LaraMart">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        <div>
            <a href="{{ route('customer.account') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">← Hesabıma Dön</a>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Adres Defterim</h1>
            <p class="text-sm text-slate-600">İl ve ilçe seçerek teslimat adreslerinizi yönetebilir, varsayılan adres belirleyebilirsiniz.</p>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Yeni Adres Formu (İl / İlçe Dinamik Seçim) --}}
            <div class="lg:col-span-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-bold text-slate-900 mb-4">Yeni Adres Ekle</h2>

                <form
                    method="POST"
                    action="{{ route('customer.addresses.store') }}"
                    x-data='{
                        cities: @json($cities),
                        selectedCity: @json(old("city", array_key_first($cities))),
                        selectedDistrict: @json(old("district", ""))
                    }'
                    x-init="if (!selectedDistrict || !(cities[selectedCity] || []).includes(selectedDistrict)) { selectedDistrict = (cities[selectedCity] || [])[0] || ''; }"
                    class="space-y-4"
                >
                    @csrf

                    <div>
                        <label for="title" class="block text-xs font-bold text-slate-700">Adres Başlığı *</label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title') }}"
                            required
                            placeholder="Örn: Ev Adresim, İş Yeri"
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none"
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
                                class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none"
                            >
                                @foreach (array_keys($cities) as $cityName)
                                    <option value="{{ $cityName }}">{{ $cityName }}</option>
                                @endforeach
                            </select>
                            @error('city')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="district" class="block text-xs font-bold text-slate-700">İlçe *</label>
                            <select
                                id="district"
                                name="district"
                                x-model="selectedDistrict"
                                required
                                class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none"
                            >
                                <template x-for="district in (cities[selectedCity] || [])" :key="district">
                                    <option :value="district" x-text="district"></option>
                                </template>
                            </select>
                            @error('district')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="full_address" class="block text-xs font-bold text-slate-700">Açık Adres *</label>
                        <textarea
                            id="full_address"
                            name="full_address"
                            rows="3"
                            required
                            placeholder="Mahalle, sokak, bina ve daire bilgileri..."
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none"
                        >{{ old('full_address') }}</textarea>
                        @error('full_address')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                        <input type="checkbox" name="is_default" value="1" @checked(old('is_default')) class="rounded border-slate-300 text-indigo-600">
                        <span>Varsayılan teslimat adresi olarak kaydet</span>
                    </label>

                    <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 transition">
                        Adresi Kaydet
                    </button>
                </form>
            </div>

            {{-- Kayıtlı Adresler --}}
            <div class="lg:col-span-7 space-y-4">
                @forelse ($addresses as $address)
                    <article class="rounded-2xl border p-5 bg-white shadow-sm {{ $address->is_default ? 'border-indigo-400 ring-1 ring-indigo-200' : 'border-slate-200' }}">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-slate-900">{{ $address->title }}</h3>
                                    @if ($address->is_default)
                                        <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-[11px] font-bold text-indigo-800">Varsayılan</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-xs font-semibold text-slate-600">{{ $address->district }} / {{ $address->city }}</p>
                                <p class="mt-1.5 text-sm text-slate-700">{{ $address->full_address }}</p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                @if (! $address->is_default)
                                    <form method="POST" action="{{ route('customer.addresses.default', $address) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-md border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                                            Varsayılan Yap
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('customer.addresses.edit', $address) }}" class="rounded-md border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    Düzenle
                                </a>
                                <form method="POST" action="{{ route('customer.addresses.destroy', $address) }}" onsubmit="return confirm('Bu adresi silmek istediğinize emin misiniz?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-md border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                        Sil
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-500">
                        Henüz kayıtlı teslimat adresiniz bulunmuyor. Soldaki formdan yeni adres ekleyebilirsiniz.
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</x-layouts.storefront>
