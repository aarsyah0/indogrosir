<x-app-layout title="Pembelian">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full min-w-0 sm:flex-1">
            <x-page-header
                header="Pembelian"
                subheader="Daftar transaksi pembelian yang telah dicatat."
                class="mb-4"
            >
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                    {{ $orders->total() }} pembelian
                </span>
            </x-page-header>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="section-heading">Pengajuan siap dibeli</h2>
                    <p class="mt-1 text-sm text-slate-500">Pengajuan yang sudah disetujui dan masih memiliki barang yang belum dibeli.</p>
                </div>
                <span class="inline-flex w-fit items-center rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">
                    {{ $readyRequisitions->total() }} pengajuan
                </span>
            </div>

            <div class="mt-4 divide-y divide-slate-100">
                @forelse ($readyRequisitions as $requisition)
                    <div class="flex flex-col gap-3 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-semibold text-slate-800">{{ $requisition->number }} <span class="font-normal text-slate-500">· {{ $requisition->division?->name ?? '—' }}</span></p>
                            <p class="mt-1 text-sm text-slate-500">{{ $requisition->procurement_status->label() }} · {{ $requisition->latestVersionRecord?->items?->count() ?? 0 }} barang</p>
                        </div>
                        <a href="{{ route('procurements.create', ['requisition_id' => $requisition->id]) }}" class="btn-primary w-full justify-center sm:w-auto">Mulai pembelian</a>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500">Belum ada pengajuan yang menunggu pembelian.</p>
                @endforelse
            </div>
            <div class="mt-3">{{ $readyRequisitions->links() }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-wrap">
                <table class="min-w-full text-sm">
                    <thead class="bg-navy-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nomor</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Rekaman</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pemasok</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tanggal</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Total</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                <td class="px-4 py-3 font-semibold text-navy-900">{{ $order->number }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $order->requisition?->number ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $order->supplier?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ format_date($order->purchased_at) }}</td>
                                <td class="px-4 py-3 font-mono text-sm font-semibold text-slate-800 tabular-nums">{{ format_money($order->actual_total) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('procurements.show', $order) }}" class="inline-flex items-center gap-1.5 text-brand font-medium text-sm transition-colors hover:text-brand-600">
                                        Detail
                                        <svg aria-hidden="true" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 13l5 5 5-5M7 21l5-5 5 5"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center">
                                    <div class="empty-state">
                                        <p class="text-sm text-slate-500">Belum ada pembelian.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
            <span class="text-xs text-slate-500">{{ $orders->total() }} pembelian</span>
            {{ $orders->links() }}
        </div>
    </div>
</x-app-layout>
