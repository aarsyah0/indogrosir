<x-app-layout title="Rekap Pembelian">
    <header class="mb-5">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Rekap Pembelian</h1>
        <p class="mt-1 text-sm text-slate-500">Rincian kuantitas yang dibeli dan diterima.</p>
    </header>

    <section class="card overflow-hidden">
        <div class="recap-card-body">
            <div class="recap-card-header">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Detail pembelian</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ count($rows) }} baris rekap</p>
                </div>
                <div class="recap-export-actions">
                    <a href="{{ route('recap.index', array_merge(request()->query(), ['export' => 'xlsx'])) }}" class="btn-secondary btn-sm inline-flex items-center gap-2">
                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M10 3v9m0 0 3-3m-3 3L7 9M4 13.5v2A1.5 1.5 0 0 0 5.5 17h9a1.5 1.5 0 0 0 1.5-1.5v-2"/></svg>
                        Excel
                    </a>
                    <a href="{{ route('recap.pdf', request()->query()) }}" class="btn-secondary btn-sm inline-flex items-center gap-2">
                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5.5 2.75h6l3 3v11a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-13a1 1 0 0 1 1-1Z"/><path d="M11.5 2.75v3h3M7 10h5M7 13h5"/></svg>
                        PDF
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('recap.index') }}" class="recap-filter-form">
                <div class="recap-filter-grid">
                    <label for="recap-period" class="block min-w-0 text-xs font-medium text-slate-600">
                        <span class="mb-1.5 block">Periode</span>
                        <input id="recap-period" type="month" name="period" value="{{ sprintf('%04d-%02d', $filters['year'] ?? now()->year, $filters['month'] ?? now()->month) }}" class="input" />
                    </label>
                    <div class="min-w-0">
                        <label for="recap-division" class="mb-1.5 block text-xs font-medium text-slate-600">Divisi</label>
                        <x-searchable-select id="recap-division" name="division_id"
                            :options="$divisions->map(fn ($division) => ['value' => $division->id, 'label' => $division->name])->prepend(['value' => '', 'label' => 'Semua divisi'])"
                            :selected="$filters['division_id'] ?? ''" placeholder="Semua divisi" search-placeholder="Cari divisi" />
                    </div>
                    <div class="min-w-0">
                        <label for="recap-item" class="mb-1.5 block text-xs font-medium text-slate-600">Barang</label>
                        <x-searchable-select id="recap-item" name="item_id"
                            :options="$items->map(fn ($item) => ['value' => $item->id, 'label' => $item->code . ' — ' . $item->name])->prepend(['value' => '', 'label' => 'Semua barang'])"
                            :selected="$filters['item_id'] ?? ''" placeholder="Semua barang" search-placeholder="Cari kode atau nama barang" />
                    </div>
                    <div class="min-w-0">
                        <label for="recap-supplier" class="mb-1.5 block text-xs font-medium text-slate-600">Pemasok</label>
                        <x-searchable-select id="recap-supplier" name="supplier_id"
                            :options="$suppliers->map(fn ($supplier) => ['value' => $supplier->id, 'label' => $supplier->name])->prepend(['value' => '', 'label' => 'Semua pemasok'])"
                            :selected="$filters['supplier_id'] ?? ''" placeholder="Semua pemasok" search-placeholder="Cari pemasok" />
                    </div>
                    <div class="min-w-0">
                        <label for="recap-category" class="mb-1.5 block text-xs font-medium text-slate-600">Kategori</label>
                        <x-searchable-select id="recap-category" name="item_category_id"
                            :options="$categories->map(fn ($category) => ['value' => $category->id, 'label' => $category->name])->prepend(['value' => '', 'label' => 'Semua kategori'])"
                            :selected="$filters['item_category_id'] ?? ''" placeholder="Semua kategori" search-placeholder="Cari kategori" />
                    </div>
                    <div class="min-w-0">
                        <label for="recap-status" class="mb-1.5 block text-xs font-medium text-slate-600">Status pengajuan</label>
                        <x-searchable-select id="recap-status" name="status"
                            :options="$statuses->map(fn ($status) => ['value' => $status->value, 'label' => $status->label()])->prepend(['value' => '', 'label' => 'Semua status'])"
                            :selected="$filters['status'] ?? ''" placeholder="Semua status" search-placeholder="Cari status" />
                    </div>
                    <div class="recap-filter-actions">
                        <button class="btn-primary min-h-11 px-5 text-sm">Terapkan filter</button>
                        <a href="{{ route('recap.index') }}" class="btn-secondary min-h-11 px-4 text-sm">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-wrap">
            <table class="recap-table min-w-full text-sm">
                <colgroup>
                    <col class="w-[24%]">
                    <col class="w-[23%]">
                    <col class="w-[13%]">
                    <col class="w-[13%]">
                    <col class="w-[15%]">
                    <col class="w-[12%]">
                </colgroup>
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
    </section>
</x-app-layout>
