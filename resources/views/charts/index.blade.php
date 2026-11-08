<x-app-layout title="Grafik & Analisis">
    <x-report-navigation />
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-auto">
            <x-page-header
                header="Grafik & Analisis"
                subheader="Tren penggunaan barang, pengeluaran, dan harga referensi."
                class="mb-4"
            >
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                    {{ $filters['year'] ?? now()->year }}
                </span>
            </x-page-header>
        </div>
    </div>

    <div class="mt-4 card">
        <div class="card-body">
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <div>
                    <label for="chart-year" class="block text-sm font-medium text-slate-700 mb-1.5">Tahun</label>
                    <input id="chart-year" type="number" min="2020" name="year" value="{{ $filters['year'] ?? now()->year }}" class="input" />
                </div>
                <div>
                    <label for="chart-division" class="block text-sm font-medium text-slate-700 mb-1.5">Divisi</label>
                    <x-searchable-select id="chart-division" name="division_id"
                        :options="$divisions->map(fn ($division) => ['value' => $division->id, 'label' => $division->name])->prepend(['value' => '', 'label' => 'Semua divisi'])"
                        :selected="$filters['division_id'] ?? ''"
                        placeholder="Semua divisi"
                        search-placeholder="Cari divisi"
                        class="mt-1" />
                </div>
                <div>
                    <label for="chart-category" class="block text-sm font-medium text-slate-700 mb-1.5">Kategori</label>
                    <x-searchable-select id="chart-category" name="item_category_id"
                        :options="$categories->map(fn ($category) => ['value' => $category->id, 'label' => $category->name])->prepend(['value' => '', 'label' => 'Semua kategori'])"
                        :selected="$filters['item_category_id'] ?? ''"
                        placeholder="Semua kategori"
                        search-placeholder="Cari kategori"
                        class="mt-1" />
                </div>
                <div>
                    <label for="chart-item" class="block text-sm font-medium text-slate-700 mb-1.5">Barang</label>
                    <x-searchable-select id="chart-item" name="item_id"
                        :options="$items->map(fn ($item) => ['value' => $item->id, 'label' => $item->code . ' — ' . $item->name])->prepend(['value' => '', 'label' => 'Semua barang'])"
                        :selected="$filters['item_id'] ?? ''"
                        placeholder="Semua barang"
                        search-placeholder="Cari kode atau nama barang"
                        class="mt-1" />
                </div>
                <div class="flex items-end gap-2">
                    <button class="btn-primary flex-1 justify-center">Tampilkan</button>
                    <a href="{{ route('charts.index') }}" class="btn-secondary">Reset</a>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5 grid gap-4 xl:grid-cols-2">
        <section class="card">
            <div class="card-body">
                <h2 class="section-heading">Penggunaan per bulan</h2>
                <p class="text-sm text-slate-500 mt-0.5">Jumlah barang keluar pada tahun terpilih.</p>
                <div class="mt-3 relative h-56 w-full">
                    <canvas id="usageChart" role="img" aria-label="Grafik batang penggunaan barang per bulan"></canvas>
                </div>
            </div>
        </section>
        <section class="card">
            <div class="card-body">
                <h2 class="section-heading">Pengeluaran per bulan</h2>
                <p class="text-sm text-slate-500 mt-0.5">Nilai pembelian pada tahun terpilih.</p>
                <div class="mt-3 relative h-56 w-full">
                    <canvas id="spendChart" role="img" aria-label="Grafik batang pengeluaran per bulan"></canvas>
                </div>
            </div>
        </section>
    </div>

    <div class="mt-5 grid gap-4 xl:grid-cols-2">
        <section class="card overflow-hidden">
            <div class="card-body">
                <h2 class="section-heading">Pengeluaran per divisi</h2>
                <div class="mt-3 table-wrap">
                    <table class="min-w-full text-sm">
                        <thead class="bg-navy-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Divisi</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($charts['byDivision'] as $division)
                                <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $division->name }}</td>
                                    <td class="px-4 py-3 text-right font-semibold tabular-nums text-slate-600">{{ format_money($division->total) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-10 text-center">
                                        <div class="empty-state">
                                            <p class="text-sm text-slate-500">Belum ada data pengeluaran per divisi.</p>
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
                <h2 class="section-heading">Pengeluaran per kategori</h2>
                <div class="mt-3 table-wrap">
                    <table class="min-w-full text-sm">
                        <thead class="bg-navy-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Kategori</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($charts['byCategory'] as $category)
                                <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $category->name }}</td>
                                    <td class="px-4 py-3 text-right font-semibold tabular-nums text-slate-600">{{ format_money($category->total) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-10 text-center">
                                        <div class="empty-state">
                                            <p class="text-sm text-slate-500">Belum ada data pengeluaran per kategori.</p>
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
            <h2 class="section-heading">Riwayat harga referensi</h2>
            <p class="text-sm text-slate-500 mt-0.5">Perubahan harga terbaru yang tercatat di master barang.</p>
            <div class="mt-3 table-wrap">
                <table class="min-w-full text-sm">
                    <thead class="bg-navy-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tanggal</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Barang</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Sumber</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Harga</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($charts['prices'] as $price)
                            <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                <td class="px-4 py-3 text-slate-600">{{ format_date($price->recorded_at) }}</td>
                                <td class="px-4 py-3 font-medium text-slate-800">{{ $price->item?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ ucfirst($price->source) }}</td>
                                <td class="px-4 py-3 text-right font-semibold tabular-nums text-slate-600">{{ format_money($price->price) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $price->notes ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center">
                                    <div class="empty-state">
                                        <p class="text-sm text-slate-500">Belum ada riwayat harga referensi.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.Chart) return;
                const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                const setup = (id, values, color, money = false) => {
                    const canvas = document.getElementById(id);
                    if (!canvas) return;
                    new Chart(canvas, {
                        type: 'bar',
                        data: { labels, datasets: [{ data: values, backgroundColor: color, borderRadius: 4, maxBarThickness: 34 }] },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            plugins: { legend: { display: false }, tooltip: { callbacks: { label: context => money ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.raw) : new Intl.NumberFormat('id-ID').format(context.raw) } } },
                            scales: {
                                x: { grid: { display: false }, ticks: { color: '#667085' } },
                                y: { beginAtZero: true, grid: { color: '#eef0f3' }, ticks: { color: '#667085', callback: value => money ? new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 1 }).format(value) : value } }
                            }
                        }
                    });
                };
                setup('usageChart', @json($charts['usage']), '#D71920');
                setup('spendChart', @json($charts['spend']), '#0B4EA2', true);
            });
        </script>
    @endpush
</x-app-layout>
