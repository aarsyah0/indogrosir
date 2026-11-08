<x-app-layout :title="'Pengajuan ' . $requisition->number">
    <div class="flex flex-col gap-4 xl:grid xl:grid-cols-3">
        <!-- Main content: requisition details -->
        <div class="card xl:col-span-2">
            <div class="card-body">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            {{ $requisition->division->name }} &middot; {{ $requisition->period->name }}
                        </p>
                        <h2 class="text-lg font-semibold text-slate-900 mt-0.5">{{ $requisition->number }}</h2>
                    </div>
                    <span class="badge badge-default">{{ $requisition->status->label() }}</span>
                </div>

                <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                    <div class="flex items-start gap-2.5 rounded-lg bg-slate-50 px-3.5 py-3">
                        <svg aria-hidden="true" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <div>
                            <p class="text-xs text-slate-500 font-medium">Pemohon</p>
                            <p class="text-sm text-slate-800 font-medium">{{ $requisition->requester->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5 rounded-lg bg-slate-50 px-3.5 py-3">
                        <svg aria-hidden="true" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <div>
                            <p class="text-xs text-slate-500 font-medium">Pembelian</p>
                            <p class="text-sm text-slate-800 font-medium">{{ $requisition->procurement_status->label() }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5 rounded-lg bg-slate-50 px-3.5 py-3">
                        <svg aria-hidden="true" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M8 12h8"/></svg>
                        <div>
                            <p class="text-xs text-slate-500 font-medium">Penerimaan</p>
                            <p class="text-sm text-slate-800 font-medium">{{ $requisition->receipt_status->label() }}</p>
                        </div>
                    </div>
                </dl>

                <div class="mt-4 table-wrap">
                    <table class="min-w-full text-sm">
                        <thead class="bg-navy-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Barang</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Qty</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Harga</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Subtotal</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Alasan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($version?->items ?? [] as $line)
                                <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-slate-800">{{ $line->item_code_snapshot }} {{ $line->item_name_snapshot }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ $line->quantity_requested }} {{ $line->unit_name_snapshot }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ format_money($line->reference_unit_price) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ format_money($line->estimated_subtotal) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $line->need_reason }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex items-center justify-between rounded-lg bg-navy-50 px-4 py-3.5">
                    <span class="text-sm text-slate-600">Total estimasi</span>
                    <span class="text-base font-bold text-navy-900 tracking-tight">{{ format_money($requisition->estimated_total) }}</span>
                </div>
            </div>
        </div>

        <!-- Sidebar: actions & history -->
        <div class="space-y-4">
            <div class="card">
                <div class="card-body">
                    @can('update', $requisition)
                        <a href="{{ route('requisitions.edit', $requisition) }}" class="w-full btn-primary">
                            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Perbaiki pengajuan
                        </a>
                    @endcan
                    <a href="{{ route('requisitions.preview', $requisition) }}" class="w-full btn-secondary mt-2">
                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        Pratinjau
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Riwayat approval</h2>
                </div>
                <div class="card-body">
                    @forelse ($requisition->approvalHistories as $h)
                        <div class="mb-3 last:mb-0 flex flex-wrap items-start gap-2.5 border-b border-slate-100 pb-3 last:border-0">
                            <span class="min-w-0 flex-1 text-sm font-medium text-slate-800">{{ $h->step }} &middot; {{ $h->action }}</span>
                            <span class="text-xs text-slate-500 shrink-0">{{ $h->actor?->name ?? 'Sistem' }} &middot; {{ format_date($h->decided_at) }}</span>
                            @if ($h->comments)
                                <span class="text-xs text-slate-600 max-w-xs truncate">&ldquo;{{ $h->comments }}&rdquo;</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada keputusan.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
