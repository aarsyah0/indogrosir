<x-app-layout :title="'Penerimaan ' . $receipt->number">
    <div class="grid gap-4 xl:grid-cols-3">
        <!-- Receipt details -->
        <div class="card xl:col-span-2">
            <div class="card-body">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Pembelian: {{ $receipt->order?->number ?? '—' }}
                        </p>
                        <h2 class="text-lg font-semibold text-slate-900 mt-0.5">
                            {{ $receipt->order?->supplier?->name ?? '—' }}
                        </h2>
                    </div>
                    <span class="badge badge-default">{{ format_date($receipt->received_at) }}</span>
                </div>

                <div class="mt-4 table-wrap">
                    <table class="min-w-full text-sm">
                        <thead class="bg-navy-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Barang</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Qty diterima</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Kondisi</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($receipt->items as $item)
                                <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $item->item?->name ?? $item->item_name_snapshot }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $item->quantity_received }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $item->condition }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $item->discrepancy_notes ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center">
                                        <div class="empty-state">
                                            <p class="text-sm text-slate-500">Belum ada item diterima.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="card">
            <div class="card-body">
                <h2 class="section-heading">Ringkasan</h2>
                <dl class="mt-3 space-y-3 text-sm">
                    <div class="flex justify-between gap-4 rounded-lg bg-slate-50 px-3.5 py-2.5">
                        <dt class="text-slate-500">Nomor</dt>
                        <dd class="text-slate-800 font-medium">{{ $receipt->number }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 rounded-lg bg-slate-50 px-3.5 py-2.5">
                        <dt class="text-slate-500">Diterima</dt>
                        <dd class="text-slate-800 font-medium">{{ format_date($receipt->received_at) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 rounded-lg bg-slate-50 px-3.5 py-2.5">
                        <dt class="text-slate-500">Catatan</dt>
                        <dd class="text-slate-800 font-medium">{{ $receipt->notes ?: '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
