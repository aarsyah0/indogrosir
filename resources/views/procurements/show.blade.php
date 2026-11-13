<x-app-layout :title="'Pembelian ' . $order->number">
    <div class="grid gap-4 xl:grid-cols-3">
        <!-- Purchase details -->
        <div class="card xl:col-span-2">
            <div class="card-body">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Rekaman: {{ $order->requisition?->number ?? '—' }}
                        </p>
                        <h2 class="text-lg font-semibold text-slate-900 mt-0.5">
                            {{ $order->supplier?->name ?? '—' }}
                        </h2>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="badge badge-success-subtle">{{ format_money($order->actual_total) }}</span>
                        @if (auth()->user()?->hasPermission('receipts.manage') && $hasRemaining)
                            <a href="{{ route('receipts.create', ['procurement_id' => $order->id]) }}" class="btn-primary">
                                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                Catat penerimaan
                            </a>
                        @endif
                    </div>
                </div>

                <div class="mt-4 table-wrap">
                    <table class="min-w-full text-sm">
                        <thead class="bg-navy-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Barang</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Dibeli</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Diterima</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Sisa</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Harga</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($order->items as $item)
                                <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $item->item?->name ?? $item->item_name_snapshot }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ number_format((float) $item->quantity_purchased, 2) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ number_format((float) ($item->quantity_purchased - $remainingByItem[$item->item_id]), 2) }}</td>
                                    <td class="px-4 py-3 font-mono text-sm font-semibold text-slate-800 tabular-nums">{{ number_format((float) $remainingByItem[$item->item_id], 2) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ format_money($item->unit_price) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ format_money($item->subtotal) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center">
                                        <div class="empty-state">
                                            <p class="text-sm text-slate-500">Belum ada item pembelian.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary + attachments -->
        <div class="space-y-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="section-heading">Ringkasan</h2>
                    <dl class="mt-3 space-y-3 text-sm">
                        <div class="flex justify-between gap-4 rounded-lg bg-slate-50 px-3.5 py-2.5">
                            <dt class="text-slate-500">Invoice</dt>
                            <dd class="text-slate-800 font-medium">{{ $order->invoice_number ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 rounded-lg bg-slate-50 px-3.5 py-2.5">
                            <dt class="text-slate-500">Tanggal</dt>
                            <dd class="text-slate-800 font-medium">{{ format_date($order->purchased_at) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 rounded-lg bg-slate-50 px-3.5 py-2.5">
                            <dt class="text-slate-500">Diskon</dt>
                            <dd class="text-slate-800 font-medium">{{ format_money($order->discount) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 rounded-lg bg-slate-50 px-3.5 py-2.5">
                            <dt class="text-slate-500">Pajak</dt>
                            <dd class="text-slate-800 font-medium">{{ format_money($order->tax) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 rounded-lg bg-slate-50 px-3.5 py-2.5">
                            <dt class="text-slate-500">Biaya lain</dt>
                            <dd class="text-slate-800 font-medium">{{ format_money($order->extra_cost) }}</dd>
                        </div>
                        @if ($order->over_qty_authorized)
                            <div class="rounded-lg border border-amber-200 bg-amber-50 px-3.5 py-2.5">
                                <dt class="font-semibold text-amber-900">Otorisasi pembelian berlebih</dt>
                                <dd class="mt-1 text-amber-800">{{ $order->over_qty_reason ?: 'Alasan tidak tersedia.' }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between gap-4 rounded-lg bg-navy-50 px-3.5 py-2.5">
                            <dt class="text-sm text-slate-600 font-medium">Total akhir</dt>
                            <dd class="text-base font-bold text-navy-900">{{ format_money($order->actual_total) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if ($order->attachments->isNotEmpty())
                <div class="card">
                    <div class="card-body">
                        <h2 class="section-heading">Lampiran</h2>
                        <ul class="mt-3 space-y-2 text-sm">
                            @foreach ($order->attachments as $attachment)
                                <li class="flex items-center gap-2.5 rounded-lg px-3 py-2 transition-colors hover:bg-slate-50">
                                    <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                                    <div>
                                        <p class="font-medium text-slate-800">{{ $attachment->original_name }}</p>
                                        <p class="text-xs text-slate-500">{{ $attachment->mime_type }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
