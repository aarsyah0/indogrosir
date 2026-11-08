<x-app-layout title="Penerimaan Barang">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-auto">
            <x-page-header
                header="Penerimaan Barang"
                subheader="Catat penerimaan barang dari transaksi pembelian."
                class="mb-4"
            >
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                    {{ $receipts->total() }} penerimaan
                </span>
            </x-page-header>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-wrap">
                <table class="min-w-full text-sm">
                    <thead class="bg-navy-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nomor</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pembelian</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Supplier</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tanggal terima</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($receipts as $receipt)
                            <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                <td class="px-4 py-3 font-semibold text-navy-900">{{ $receipt->number }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $receipt->order?->number ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $receipt->order?->supplier?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ format_date($receipt->received_at) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('receipts.show', $receipt) }}" class="inline-flex items-center gap-1.5 text-brand font-medium text-sm transition-colors hover:text-brand-600">
                                        Detail
                                        <svg aria-hidden="true" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 13l5 5 5-5M7 21l5-5 5 5"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center">
                                    <div class="empty-state">
                                        <p class="text-sm text-slate-500">Belum ada penerimaan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
            <span class="text-xs text-slate-500">{{ $receipts->total() }} penerimaan</span>
            {{ $receipts->links() }}
        </div>
    </div>
</x-app-layout>
