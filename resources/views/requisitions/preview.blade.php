<x-app-layout :title="'Pratinjau ' . $requisition->number">
    <style>
        @media print {
            body * { visibility: hidden !important; }
            #requisition-print, #requisition-print * { visibility: visible !important; }
            #requisition-print { position: absolute; inset: 0; width: 100%; margin: 0 !important; padding: 0 !important; }
            #requisition-print .print-hidden { display: none !important; }
            #requisition-print .card { border: 0 !important; box-shadow: none !important; }
        }
    </style>

    <div id="requisition-print">
    <div class="print-hidden flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold text-slate-900">{{ $requisition->number }}</h1>
            <p class="text-sm text-slate-500 mt-0.5">
                {{ $requisition->division->name }} &middot; {{ format_date($requisition->created_at) }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('requisitions.show', $requisition) }}" class="btn-secondary">
                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M14 2v6h6"/></svg>
                Kembali
            </a>
            <button type="button" onclick="window.print()" class="btn-primary">
                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                Cetak
            </button>
        </div>
    </div>

    <div class="mt-4 card">
        <div class="card-body">
            <div class="table-wrap">
                <table class="min-w-full text-sm">
                    <thead class="bg-navy-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Barang</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Qty</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($version?->items ?? [] as $line)
                            <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                <td class="px-4 py-3 font-medium text-slate-800">{{ $line->item_name_snapshot }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $line->quantity_requested }} {{ $line->unit_name_snapshot }}</td>
                                <td class="px-4 py-3 font-mono text-sm font-semibold text-slate-800 tabular-nums">{{ format_money($line->estimated_subtotal) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-10 text-center">
                                    <div class="empty-state">
                                        <p class="text-sm text-slate-500">Belum ada item dalam pengajuan ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex items-center justify-between rounded-lg bg-navy-50 px-4 py-3.5">
                <span class="text-sm text-slate-600">Total estimasi</span>
                <span class="text-lg font-bold text-navy-900 tracking-tight">{{ format_money($requisition->estimated_total) }}</span>
            </div>
        </div>
    </div>
    </div>

    @push('scripts')
        @if ($autoPrint)
            <script>
                window.addEventListener('load', () => window.setTimeout(() => window.print(), 300));
            </script>
        @endif
    @endpush
</x-app-layout>
