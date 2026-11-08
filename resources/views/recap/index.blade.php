<x-app-layout title="Rekap Pembelian">
    <x-report-navigation />
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-auto">
            <x-page-header
                header="Rekap Pembelian"
                subheader="Rincian kuantitas yang dibeli dan diterima."
                class="mb-4"
            >
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('recap.index', array_merge(request()->query(), ['export' => 'xlsx'])) }}" class="btn-secondary btn-sm">Unduh Excel</a>
                </div>
            </x-page-header>
        </div>
    </div>

    <div class="mt-4 card">
        <div class="card-body">
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label for="recap-year" class="block text-sm font-medium text-slate-700 mb-1.5">Tahun</label>
                    <input id="recap-year" type="number" min="2020" name="year" value="{{ $filters['year'] ?? now()->year }}" class="input" />
                </div>
                <div>
                    <label for="recap-month" class="block text-sm font-medium text-slate-700 mb-1.5">Bulan</label>
                    <input id="recap-month" type="number" min="1" max="12" name="month" value="{{ $filters['month'] ?? now()->month }}" class="input" />
                </div>
                <div>
                    <label for="recap-division" class="block text-sm font-medium text-slate-700 mb-1.5">Divisi</label>
                    <x-searchable-select id="recap-division" name="division_id"
                        :options="$divisions->map(fn ($division) => ['value' => $division->id, 'label' => $division->name])->prepend(['value' => '', 'label' => 'Semua divisi'])"
                        :selected="$filters['division_id'] ?? ''"
                        placeholder="Semua divisi"
                        search-placeholder="Cari divisi"
                        class="mt-1" />
                </div>
                <div>
                    <label for="recap-item" class="block text-sm font-medium text-slate-700 mb-1.5">Barang</label>
                    <x-searchable-select id="recap-item" name="item_id"
                        :options="$items->map(fn ($item) => ['value' => $item->id, 'label' => $item->code . ' — ' . $item->name])->prepend(['value' => '', 'label' => 'Semua barang'])"
                        :selected="$filters['item_id'] ?? ''"
                        placeholder="Semua barang"
                        search-placeholder="Cari kode atau nama barang"
                        class="mt-1" />
                </div>
                <div>
                    <label for="recap-supplier" class="block text-sm font-medium text-slate-700 mb-1.5">Pemasok</label>
                    <x-searchable-select id="recap-supplier" name="supplier_id"
                        :options="$suppliers->map(fn ($supplier) => ['value' => $supplier->id, 'label' => $supplier->name])->prepend(['value' => '', 'label' => 'Semua pemasok'])"
                        :selected="$filters['supplier_id'] ?? ''"
                        placeholder="Semua pemasok"
                        search-placeholder="Cari pemasok"
                        class="mt-1" />
                </div>
                <div>
                    <label for="recap-category" class="block text-sm font-medium text-slate-700 mb-1.5">Kategori</label>
                    <x-searchable-select id="recap-category" name="item_category_id"
                        :options="$categories->map(fn ($category) => ['value' => $category->id, 'label' => $category->name])->prepend(['value' => '', 'label' => 'Semua kategori'])"
                        :selected="$filters['item_category_id'] ?? ''"
                        placeholder="Semua kategori"
                        search-placeholder="Cari kategori"
                        class="mt-1" />
                </div>
                <div>
                    <label for="recap-status" class="block text-sm font-medium text-slate-700 mb-1.5">Status pengajuan</label>
                    <x-searchable-select id="recap-status" name="status"
                        :options="$statuses->map(fn ($status) => ['value' => $status->value, 'label' => $status->label()])->prepend(['value' => '', 'label' => 'Semua status'])"
                        :selected="$filters['status'] ?? ''"
                        placeholder="Semua status"
                        search-placeholder="Cari status"
                        class="mt-1" />
                </div>
                <div class="flex items-end gap-2">
                    <button class="btn-primary flex-1 justify-center">Terapkan</button>
                    <a href="{{ route('recap.index') }}" class="btn-secondary">Reset</a>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5 grid gap-4">
        <section class="card overflow-hidden">
            <div class="card-body">
                <h2 class="section-heading">Detail pembelian</h2>
                <div class="mt-3 table-wrap">
                    <table class="min-w-full text-sm">
                        <thead class="bg-navy-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pengajuan</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Barang</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Qty dibeli</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Diterima</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Harga</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $row['requisition']->number }}</td>
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $row['line']->item_name_snapshot }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ number_format((float) $row['purchased'], 2) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ number_format((float) $row['received'], 2) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ format_money($row['actual_price']) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="badge badge-default">{{ $row['status'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center">
                                        <div class="empty-state">
                                            <p class="text-sm text-slate-500">Belum ada data rekap.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
