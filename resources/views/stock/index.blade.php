<x-app-layout title="Stok & Kartu Gudang">
    <x-report-navigation />
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Total item</p>
                    <p class="stat-value">{{ $items->count() }}</p>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-navy-100 text-sm font-bold text-navy-700">
                    {{ $items->count() }}
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Stok rendah</p>
                    <p class="stat-value text-rose-700">{{ $items->filter(fn($row) => $row['low'])->count() }}</p>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-100 text-sm font-bold text-rose-700">
                    {{ $items->filter(fn($row) => $row['low'])->count() }}
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Total masuk</p>
                    <p class="stat-value">{{ number_format($items->sum(fn($row) => $row['in']), 2) }}</p>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700">
                    <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3.75l-2.25 2.25m0 0L9.75 6.75m3.75-3L18 9m-3.75 3L12 12m-3.75 3L9 15m3.75 3L12 18m-3.75 3L6 21"/></svg>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Total keluar</p>
                    <p class="stat-value text-rose-600">{{ number_format($items->sum(fn($row) => $row['out']), 2) }}</p>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-bold text-slate-600">
                    <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 4h2l2.2 11.3A2 2 0 0 0 7.2 17h9.6a2 2 0 0 0 2-1.6L21 8H6m4 13h.01M17 21h.01"/></svg>
                </div>
            </div>
        </div>
    </div>

    @if (auth()->user()->hasPermission('stock.manage'))
        <section class="mt-5 card">
            <div class="card-body">
                <h2 class="section-heading">Tindakan persediaan</h2>
                <p class="text-sm text-slate-500 mt-0.5">Catat barang keluar atau sesuaikan saldo setelah pemeriksaan fisik.</p>
                <div class="mt-4 grid gap-4 xl:grid-cols-2">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="section-heading">Catat pengeluaran</h3>
                            <form method="POST" action="{{ route('stock.issue') }}" class="mt-3 space-y-3">
                                @csrf
                                <div class="sm:col-span-2">
                                    <label for="issue-item" class="block text-sm font-medium text-slate-700 mb-1.5">Barang</label>
                                    <x-searchable-select id="issue-item" name="item_id"
                                        :options="$items->map(fn ($row) => ['value' => $row['item']->id, 'label' => $row['item']->code . ' — ' . $row['item']->name . ' (stok ' . number_format((float) $row['end'], 2) . ')'])"
                                        :selected="old('item_id')"
                                        placeholder="Pilih barang"
                                        search-placeholder="Cari kode atau nama barang"
                                        required class="mt-1" />
                                </div>
                                <div>
                                    <label for="issue-quantity" class="block text-sm font-medium text-slate-700 mb-1.5">Jumlah keluar</label>
                                    <input id="issue-quantity" type="number" min="0.01" step="0.01" name="quantity" value="{{ old('quantity') }}" class="input" required>
                                </div>
                                <div>
                                    <label for="issue-division" class="block text-sm font-medium text-slate-700 mb-1.5">Divisi tujuan <span class="font-normal text-slate-500">(opsional)</span></label>
                                    <x-searchable-select id="issue-division" name="division_id"
                                        :options="$divisions->map(fn ($division) => ['value' => $division->id, 'label' => $division->name])->prepend(['value' => '', 'label' => 'Divisi akun saya'])"
                                        :selected="old('division_id') ?? ''"
                                        placeholder="Divisi akun saya"
                                        search-placeholder="Cari divisi"
                                        class="mt-1" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="issue-notes" class="block text-sm font-medium text-slate-700 mb-1.5">Catatan pengeluaran</label>
                                    <textarea id="issue-notes" name="notes" rows="2" class="input" required>{{ old('notes') }}</textarea>
                                </div>
                                <div class="sm:col-span-2">
                                    <button class="btn-primary w-full justify-center">Simpan pengeluaran</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h3 class="section-heading">Penyesuaian stok</h3>
                            <p class="text-sm text-slate-500 mt-0.5">Gunakan angka positif untuk menambah dan negatif untuk mengurangi stok.</p>
                            <form method="POST" action="{{ route('stock.adjust') }}" class="mt-3 space-y-3">
                                @csrf
                                <div class="sm:col-span-2">
                                    <label for="adjust-item" class="block text-sm font-medium text-slate-700 mb-1.5">Barang</label>
                                    <x-searchable-select id="adjust-item" name="item_id"
                                        :options="$items->map(fn ($row) => ['value' => $row['item']->id, 'label' => $row['item']->code . ' — ' . $row['item']->name . ' (stok ' . number_format((float) $row['end'], 2) . ')'])"
                                        :selected="old('item_id')"
                                        placeholder="Pilih barang"
                                        search-placeholder="Cari kode atau nama barang"
                                        required class="mt-1" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="adjust-quantity" class="block text-sm font-medium text-slate-700 mb-1.5">Perubahan jumlah</label>
                                    <input id="adjust-quantity" type="number" step="0.01" name="quantity_delta" value="{{ old('quantity_delta') }}" class="input" required>
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="adjust-reason" class="block text-sm font-medium text-slate-700 mb-1.5">Alasan penyesuaian</label>
                                    <textarea id="adjust-reason" name="reason" rows="2" class="input" required>{{ old('reason') }}</textarea>
                                </div>
                                <div class="sm:col-span-2">
                                    <button class="btn-secondary w-full justify-center">Simpan penyesuaian</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <div class="mt-5 grid gap-4">
        <!-- Stok per barang -->
        <section class="card overflow-hidden">
            <div class="card-body">
                <h2 class="section-heading">Kondisi stok per barang</h2>
                <p class="text-sm text-slate-500 mt-0.5">Ringkasan barang masuk, keluar, penyesuaian, dan saldo terkini.</p>
                <div class="mt-3 table-wrap">
                    <table class="min-w-full text-sm">
                        <thead class="bg-navy-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Barang</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Kategori</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Masuk</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Keluar</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Penyesuaian</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Akhir</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $row)
                                @php($item = $row['item'])
                                <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $item->name }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $item->category?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ number_format((float) $row['in'], 2) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ number_format((float) $row['out'], 2) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ number_format((float) $row['adj'], 2) }}</td>
                                    <td class="px-4 py-3 font-mono text-sm font-semibold text-slate-800 tabular-nums">{{ number_format((float) $row['end'], 2) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="badge badge-{{ $row['low'] ? 'danger' : 'success' }}">{{ $row['low'] ? 'Rendah' : 'Normal' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center">
                                        <div class="empty-state">
                                            <p class="text-sm text-slate-500">Belum ada data stok.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Kartu mutasi -->
        <section class="card overflow-hidden">
            <div class="card-body">
                <h2 class="section-heading">Kartu mutasi stok</h2>
                <p class="text-sm text-slate-500 mt-0.5">Riwayat transaksi terbaru beserta saldo setelah transaksi.</p>
                <div class="mt-3 table-wrap">
                    <table class="min-w-full text-sm">
                        <thead class="bg-navy-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tanggal</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Barang</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tipe</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Qty</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Saldo</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ledger as $tx)
                                <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                    <td class="px-4 py-3 text-slate-600">{{ format_date($tx->created_at) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $tx->item?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $tx->type }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ number_format((float) $tx->quantity, 2) }}</td>
                                    <td class="px-4 py-3 font-mono text-sm font-semibold text-slate-800 tabular-nums">{{ number_format((float) $tx->balance_after, 2) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $tx->notes ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center">
                                        <div class="empty-state">
                                            <p class="text-sm text-slate-500">Belum ada mutasi stok.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
                <span class="text-xs text-slate-500">{{ $ledger->total() }} transaksi</span>
                {{ $ledger->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
