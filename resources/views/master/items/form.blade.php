<x-app-layout :title="$item->exists ? 'Edit Barang' : 'Tambah Barang'">
    <div class="master-page-heading">
        <div class="min-w-0">
            <h2 class="master-page-title">{{ $item->exists ? 'Edit Barang' : 'Tambah Barang' }}</h2>
            <p class="master-page-description">Identitas barang dan parameter persediaan.</p>
        </div>
        <a href="{{ route('master.items.index') }}" class="btn-secondary shrink-0">Kembali ke Master Barang</a>
    </div>

    <div class="card mx-auto w-full max-w-5xl">
        <div class="card-body p-4 sm:p-6 lg:p-8">
            <form method="POST" action="{{ $item->exists ? route('master.items.update', $item) : route('master.items.store') }}" class="space-y-7">
                @csrf
                @if ($item->exists) @method('PUT') @endif

                <section class="space-y-4">
                    <div>
                        <h3 class="card-title text-base">Informasi barang</h3>
                        <p class="card-subtitle mt-0.5">Identitas dan pengelompokan barang.</p>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="item-code" class="block text-sm font-medium text-slate-700 mb-1.5">Kode barang</label>
                            <input id="item-code" name="code" class="input" value="{{ old('code', $item->code) }}" required />
                        </div>
                        <div>
                            <label for="item-name" class="block text-sm font-medium text-slate-700 mb-1.5">Nama barang</label>
                            <input id="item-name" name="name" class="input" value="{{ old('name', $item->name) }}" required />
                        </div>
                        <div>
                            <label for="item-category" class="block text-sm font-medium text-slate-700 mb-1.5">Kategori</label>
                            <x-searchable-select id="item-category" name="item_category_id"
                                :options="$categories->map(fn ($category) => ['value' => $category->id, 'label' => $category->name])->prepend(['value' => '', 'label' => 'Pilih kategori'])"
                                :selected="old('item_category_id', $item->item_category_id)"
                                placeholder="Pilih kategori"
                                search-placeholder="Cari kategori"
                                required class="mt-1" />
                        </div>
                        <div>
                            <label for="item-unit" class="block text-sm font-medium text-slate-700 mb-1.5">Satuan</label>
                            <x-searchable-select id="item-unit" name="unit_id"
                                :options="$units->map(fn ($unit) => ['value' => $unit->id, 'label' => $unit->name])->prepend(['value' => '', 'label' => 'Pilih satuan'])"
                                :selected="old('unit_id', $item->unit_id)"
                                placeholder="Pilih satuan"
                                search-placeholder="Cari satuan"
                                required class="mt-1" />
                        </div>
                    </div>
                </section>

                <section class="space-y-4 border-t border-slate-100 pt-6">
                    <div>
                        <h3 class="card-title text-base">Harga &amp; persediaan</h3>
                        <p class="card-subtitle mt-0.5">Nilai referensi dan batas stok untuk pemantauan.</p>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="item-price" class="block text-sm font-medium text-slate-700 mb-1.5">Harga referensi</label>
                            <input id="item-price" type="number" step="0.01" min="0" name="reference_price" class="input" value="{{ old('reference_price', $item->reference_price) }}" required />
                            <p class="text-xs text-slate-500 mt-1">Dalam mata uang yang digunakan sistem.</p>
                        </div>
                        <div>
                            <label for="item-min-stock" class="block text-sm font-medium text-slate-700 mb-1.5">Stok minimum</label>
                            <input id="item-min-stock" type="number" step="0.01" min="0" name="min_stock" class="input" value="{{ old('min_stock', $item->min_stock) }}" required />
                            <p class="text-xs text-slate-500 mt-1">Peringatan muncul saat saldo sama dengan atau di bawah nilai ini.</p>
                        </div>
                    </div>
                </section>

                <section class="space-y-4 border-t border-slate-100 pt-6">
                    <div>
                        <h3 class="card-title text-base">Keterangan tambahan</h3>
                        <p class="card-subtitle mt-0.5">Deskripsi bersifat opsional.</p>
                    </div>
                    <div class="grid gap-4">
                        <div>
                            <label for="item-description" class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi <span class="font-normal text-slate-500">(opsional)</span></label>
                            <textarea id="item-description" name="description" rows="3" class="input">{{ old('description', $item->description) }}</textarea>
                        </div>
                    </div>
                </section>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-end">
                    <button type="submit" class="btn-primary justify-center sm:min-w-40">
                        {{ $item->exists ? 'Simpan perubahan' : 'Tambah barang' }}
                    </button>
                    <a href="{{ route('master.items.index') }}" class="btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
