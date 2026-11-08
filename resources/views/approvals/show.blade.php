<x-app-layout :title="'Persetujuan ' . $requisition->number">
    <div class="grid gap-4 xl:grid-cols-3">
        <!-- Requisition details -->
        <div class="card xl:col-span-2">
            <div class="card-body">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            {{ $requisition->division?->name }} &middot; {{ $requisition->period?->name }}
                        </p>
                        <h2 class="text-lg font-semibold text-slate-900 mt-0.5">{{ $requisition->number }}</h2>
                        <p class="text-sm text-slate-600">Pemohon: {{ $requisition->requester?->name }}</p>
                    </div>
                    <span class="badge badge-default">{{ $requisition->status->label() }}</span>
                </div>

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
                            @forelse ($version?->items ?? [] as $line)
                                <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $line->item_name_snapshot }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $line->quantity_requested }} {{ $line->unit_name_snapshot }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ format_money($line->reference_unit_price) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ format_money($line->estimated_subtotal) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $line->need_reason }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center">
                                        <div class="empty-state">
                                            <p class="text-sm text-slate-500">Belum ada item.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex items-center justify-between rounded-lg bg-navy-50 px-4 py-3.5">
                    <span class="text-sm text-slate-600">Total estimasi</span>
                    <span class="text-base font-bold text-navy-900 tracking-tight">{{ format_money($requisition->estimated_total) }}</span>
                </div>
            </div>
        </div>

        <!-- Decision + history -->
        <div class="space-y-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="section-heading">Keputusan</h2>
                    <form method="POST" action="{{ route($actionRoute, $requisition) }}" class="mt-3 space-y-3">
                        @csrf
                        <div>
                            @php
                                $decisionOptions = [['value' => 'approved', 'label' => 'Approve']];
                                if ($allowReject) $decisionOptions[] = ['value' => 'rejected', 'label' => 'Reject'];
                                $decisionOptions[] = ['value' => 'returned', 'label' => 'Return'];
                            @endphp
                            <label for="approval-action" class="block text-sm font-medium text-slate-700 mb-1.5">Aksi</label>
                            <x-searchable-select id="approval-action" name="action"
                                :options="$decisionOptions"
                                :selected="old('action', 'approved')"
                                placeholder="Pilih aksi"
                                search-placeholder="Cari aksi"
                                required class="mt-1" />
                        </div>
                        <div>
                            <label for="comment" class="block text-sm font-medium text-slate-700 mb-1.5">Catatan</label>
                            <textarea id="comment" name="comment" rows="4" class="input" placeholder="Tambahkan catatan..."></textarea>
                        </div>
                        <button class="btn-primary w-full justify-center">Simpan keputusan</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h2 class="section-heading">Riwayat</h2>
                    @forelse ($requisition->approvalHistories as $history)
                        <div class="mb-3 last:mb-0 flex flex-wrap items-start gap-2.5 border-b border-slate-100 pb-3 last:border-0">
                            <span class="min-w-0 flex-1 text-sm font-medium text-slate-800">{{ $history->step }}</span>
                            <span class="text-xs text-slate-500 shrink-0">{{ $history->actor?->name ?? 'Sistem' }} &middot; {{ format_date($history->decided_at) }}</span>
                            @if ($history->comments)
                                <span class="text-xs text-slate-600 max-w-xs truncate">&ldquo;{{ $history->comments }}&rdquo;</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada riwayat persetujuan.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
