<x-app-layout title="Laporan Bulanan">
    <x-report-navigation />
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full min-w-0 sm:flex-1">
            <x-page-header
                header="Laporan Bulanan"
                subheader="Ringkasan pengajuan dan realisasi pembelian."
                class="mb-4"
            >
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('reports.excel', request()->query()) }}" class="btn-secondary btn-sm">Unduh Excel</a>
                    <a href="{{ route('reports.pdf', request()->query()) }}" class="btn-secondary btn-sm">Unduh PDF</a>
                </div>
            </x-page-header>
        </div>
    </div>

    <div class="mt-4 card">
        <div class="card-body">
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label for="report-year" class="block text-sm font-medium text-slate-700 mb-1.5">Tahun</label>
                    <input id="report-year" type="number" min="2020" name="year" value="{{ $report['filters']['year'] ?? now()->year }}" class="input" />
                </div>
                <div>
                    <label for="report-month" class="block text-sm font-medium text-slate-700 mb-1.5">Bulan</label>
                    <input id="report-month" type="number" min="1" max="12" name="month" value="{{ $report['filters']['month'] ?? now()->month }}" class="input" />
                </div>
                <div>
                    <label for="report-division" class="block text-sm font-medium text-slate-700 mb-1.5">Divisi</label>
                    <x-searchable-select id="report-division" name="division_id"
                        :options="$divisions->map(fn ($division) => ['value' => $division->id, 'label' => $division->name])->prepend(['value' => '', 'label' => 'Semua divisi'])"
                        :selected="$report['filters']['divisionId'] ?? ''"
                        placeholder="Semua divisi"
                        search-placeholder="Cari divisi"
                        class="mt-1" />
                </div>
                <div>
                    <label for="report-category" class="block text-sm font-medium text-slate-700 mb-1.5">Kategori</label>
                    <x-searchable-select id="report-category" name="item_category_id"
                        :options="$categories->map(fn ($category) => ['value' => $category->id, 'label' => $category->name])->prepend(['value' => '', 'label' => 'Semua kategori'])"
                        :selected="$report['filters']['categoryId'] ?? ''"
                        placeholder="Semua kategori"
                        search-placeholder="Cari kategori"
                        class="mt-1" />
                </div>
                <div>
                    <label for="report-item" class="block text-sm font-medium text-slate-700 mb-1.5">Barang</label>
                    <x-searchable-select id="report-item" name="item_id"
                        :options="$items->map(fn ($item) => ['value' => $item->id, 'label' => $item->code . ' — ' . $item->name])->prepend(['value' => '', 'label' => 'Semua barang'])"
                        :selected="$report['filters']['itemId'] ?? ''"
                        placeholder="Semua barang"
                        search-placeholder="Cari kode atau nama barang"
                        class="mt-1" />
                </div>
                <div>
                    <label for="report-supplier" class="block text-sm font-medium text-slate-700 mb-1.5">Pemasok</label>
                    <x-searchable-select id="report-supplier" name="supplier_id"
                        :options="$suppliers->map(fn ($supplier) => ['value' => $supplier->id, 'label' => $supplier->name])->prepend(['value' => '', 'label' => 'Semua pemasok'])"
                        :selected="$report['filters']['supplierId'] ?? ''"
                        placeholder="Semua pemasok"
                        search-placeholder="Cari pemasok"
                        class="mt-1" />
                </div>
                <div>
                    <label for="report-status" class="block text-sm font-medium text-slate-700 mb-1.5">Status</label>
                    <x-searchable-select id="report-status" name="status"
                        :options="$statuses->map(fn ($status) => ['value' => $status->value, 'label' => $status->label()])->prepend(['value' => '', 'label' => 'Semua status'])"
                        :selected="$report['filters']['status'] ?? ''"
                        placeholder="Semua status"
                        search-placeholder="Cari status"
                        class="mt-1" />
                </div>
                <div class="flex items-end gap-2">
                    <button class="btn-primary flex-1 justify-center">Terapkan</button>
                    <a href="{{ route('reports.index') }}" class="btn-secondary">Reset</a>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5 grid gap-4 md:grid-cols-4">
        <div class="stat-card flex items-center justify-between gap-3">
            <div>
            <p class="stat-label">Rekapan</p>
            <p class="stat-value">{{ $report['total_requisitions'] }}</p>
            </div>
            <x-context-icon name="clipboard" tone="blue" />
        </div>
        <div class="stat-card flex items-center justify-between gap-3">
            <div>
            <p class="stat-label">Estimasi</p>
            <p class="stat-value">{{ format_money($report['estimate']) }}</p>
            </div>
            <x-context-icon name="currency" tone="gold" />
        </div>
        <div class="stat-card flex items-center justify-between gap-3">
            <div>
            <p class="stat-label">Pembelian</p>
            <p class="stat-value">{{ format_money($report['actual']) }}</p>
            </div>
            <x-context-icon name="cart" tone="blue" />
        </div>
        <div class="stat-card flex items-center justify-between gap-3">
            <div>
            <p class="stat-label">Variance</p>
            <p class="stat-value {{ $report['variance'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                {{ format_money($report['variance']) }}
            </p>
            </div>
            <x-context-icon name="chart" :tone="$report['variance'] >= 0 ? 'green' : 'red'" />
        </div>
    </div>

    <div class="mt-5 grid gap-4 xl:grid-cols-2">
        <section class="card overflow-hidden">
            <div class="card-body">
                <h2 class="section-heading">Per divisi</h2>
                <div class="mt-3 table-wrap">
                    <table class="min-w-full text-sm">
                        <thead class="bg-navy-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Divisi</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Jumlah</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Estimasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report['by_division'] as $row)
                                <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $row['division'] }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $row['count'] }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ format_money($row['estimate']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-10 text-center">
                                        <div class="empty-state">
                                            <p class="text-sm text-slate-500">Belum ada data.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <section class="card overflow-hidden">
            <div class="card-body">
                <h2 class="section-heading">Top item</h2>
                <div class="mt-3 table-wrap">
                    <table class="min-w-full text-sm">
                        <thead class="bg-navy-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Barang</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Qty diminta</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report['top_items'] as $row)
                                <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $row['name'] }}</td>
                                    <td class="px-4 py-3 text-right font-semibold tabular-nums text-slate-600">{{ number_format((float) $row['qty'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-10 text-center">
                                        <div class="empty-state">
                                            <p class="text-sm text-slate-500">Belum ada data.</p>
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

    <div class="mt-5 card overflow-hidden">
        <div class="card-body">
            <h2 class="section-heading">Daftar pengajuan bulan ini</h2>
            <div class="mt-3 table-wrap">
                <table class="min-w-full text-sm">
                    <thead class="bg-navy-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nomor</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Divisi</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Estimasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report['requisitions'] as $requisition)
                            <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                <td class="px-4 py-3 font-semibold text-navy-900">{{ $requisition->number }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $requisition->division?->name ?? '—' }}</td>
                                <td class="px-4 py-3"><span class="badge badge-default">{{ $requisition->status->label() }}</span></td>
                                <td class="px-4 py-3 font-mono text-sm font-semibold text-slate-800 tabular-nums">{{ format_money($requisition->estimated_total) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center">
                                    <div class="empty-state">
                                        <p class="text-sm text-slate-500">Tidak ada pengajuan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
