<x-app-layout title="Kebutuhan Barang">
    <x-report-navigation />
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full min-w-0 sm:flex-1">
            <x-page-header
                header="Kebutuhan Barang"
                subheader="Lihat volume kebutuhan berdasarkan periode dan divisi."
                class="mb-4"
            >
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                    {{ $report['total_requisitions'] }} pengajuan
                </span>
            </x-page-header>
        </div>
        <form method="GET" class="w-full sm:w-auto">
            <label class="sr-only" for="needs-year">Tahun</label>
            <div class="relative">
                <input id="needs-year" type="number" min="2020" name="year" value="{{ $report['filters']['year'] ?? now()->year }}"
                    class="input pl-9"
                    placeholder="Tahun" />
                <svg aria-hidden="true" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                <button type="submit" class="absolute right-1 top-1/2 -translate-y-1/2 btn-sm btn-secondary">Tampilkan</button>
            </div>
        </form>
    </div>

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
            <div class="mt-3 table-wrap">
                <table class="min-w-full text-sm">
                    <thead class="bg-navy-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">No.</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Barang</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Qty diminta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report['top_items'] as $index => $row)
                            <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                <td class="px-4 py-3 text-xs text-slate-500 font-mono">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-4 py-3 font-medium text-slate-800">{{ $row['name'] }}</td>
                                <td class="px-4 py-3 text-right font-semibold tabular-nums text-slate-800">{{ number_format((float) $row['qty'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-10 text-center">
                                    <div class="empty-state">
                                        <p class="text-sm text-slate-500">Belum ada kebutuhan untuk filter ini.</p>
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
