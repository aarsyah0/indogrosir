<x-app-layout title="Kebutuhan Barang">
    <x-report-navigation />
    <x-page-header
        header="Kebutuhan Barang"
        subheader="Lihat volume kebutuhan berdasarkan periode dan divisi."
        class="mb-4"
    >
        <x-slot:actions>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                    {{ $report['total_requisitions'] }} pengajuan
                </span>
        </x-slot:actions>
        <form method="GET" class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
            <label class="sr-only" for="needs-year">Tahun</label>
            <input id="needs-year" type="number" min="2020" name="year" value="{{ $report['filters']['year'] ?? now()->year }}"
                class="input w-full sm:w-40"
                placeholder="Tahun" />
            <button type="submit" class="btn-secondary justify-center">Tampilkan</button>
        </form>
    </x-page-header>

    <div class="mt-4 grid gap-3 sm:grid-cols-3">
        <div class="stat-card flex items-center justify-between gap-3">
            <div>
            <p class="stat-label">Pengajuan</p>
            <p class="stat-value">{{ $report['total_requisitions'] }}</p>
            </div>
            <x-context-icon name="clipboard" tone="blue" />
        </div>
        <div class="stat-card flex items-center justify-between gap-3">
            <div>
            <p class="stat-label">Qty diminta</p>
            <p class="stat-value">{{ number_format((float) $report['qty_requested'], 2) }}</p>
            </div>
            <x-context-icon name="scale" tone="gold" />
        </div>
        <div class="stat-card flex items-center justify-between gap-3">
            <div>
            <p class="stat-label">Estimasi kebutuhan</p>
            <p class="stat-value">{{ format_money($report['estimate']) }}</p>
            </div>
            <x-context-icon name="currency" tone="green" />
        </div>
    </div>

    <div class="mt-5 card">
        <div class="card-body">
            <h2 class="section-heading">Barang paling banyak diminta</h2>
            <p class="text-sm text-slate-500 mt-0.5">Diurutkan berdasarkan jumlah yang diminta pada filter aktif.</p>
            @if ($report['top_items']->isEmpty())
                <div class="mt-4 rounded-xl border border-dashed border-slate-200 bg-slate-50/60 px-4 py-7 text-center">
                    <p class="text-sm font-medium text-slate-700">Belum ada kebutuhan untuk {{ $report['filters']['year'] ?? now()->year }}.</p>
                    <p class="mt-1 text-xs text-slate-500">Data barang yang diminta akan muncul di sini.</p>
                </div>
            @else
                <div class="mt-4 table-wrap">
                    <table class="min-w-full text-sm">
                        <thead class="bg-navy-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">No.</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Barang</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Qty diminta</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($report['top_items'] as $index => $row)
                            <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                <td class="px-4 py-3 text-xs text-slate-500 font-mono">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-4 py-3 font-medium text-slate-800">{{ $row['name'] }}</td>
                                <td class="px-4 py-3 text-right font-semibold tabular-nums text-slate-800">{{ number_format((float) $row['qty'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
