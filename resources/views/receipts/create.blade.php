<x-app-layout title="Catat Penerimaan Barang">
    <div class="grid gap-4 xl:grid-cols-3">
        <!-- Item detail -->
        <div class="card xl:col-span-2">
            <div class="card-body">
                <h2 class="section-heading">Detil pembelian</h2>
                <div class="mt-3 table-wrap">
                    <table class="min-w-full text-sm">
                        <thead class="bg-navy-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Barang</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Qty dibeli</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($order->items as $item)
                                <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $item->item?->name ?? $item->item_name_snapshot }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $item->quantity_purchased }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ format_money($item->unit_price) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-10 text-center">
                                        <div class="empty-state">
                                            <p class="text-sm text-slate-500">Tidak ada item pembelian.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Receipt form -->
        <div class="card xl:col-span-1">
            <div class="card-body">
                <h2 class="section-heading">Form penerimaan</h2>
                <form method="POST" action="{{ route('receipts.store') }}" enctype="multipart/form-data" class="mt-3 space-y-3">
                    @csrf
                    <input type="hidden" name="procurement_id" value="{{ $order->id }}" />
                    <input type="hidden" name="submission_key" value="{{ old('submission_key', (string) \Illuminate\Support\Str::uuid()) }}" />

                    <div>
                        <label for="received-at" class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal diterima</label>
                        <input id="received-at" type="date" name="received_at" class="input" value="{{ old('received_at', now()->toDateString()) }}" required />
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-slate-700 mb-1.5">Catatan</label>
                        <textarea id="notes" name="notes" rows="2" class="input">{{ old('notes') }}</textarea>
                    </div>

                    <div>
                        <label for="proof" class="block text-sm font-medium text-slate-700 mb-1.5">Bukti penerimaan</label>
                        <input id="proof" type="file" name="proof" class="input" accept=".pdf,.jpg,.jpeg,.png" />
                    </div>

                    <div class="space-y-3">
                        @foreach ($order->items as $index => $item)
                            <div class="rounded-lg border border-slate-200 p-3">
                                <p class="font-medium text-sm text-slate-800">{{ $item->item?->name ?? $item->item_name_snapshot }}</p>
                                <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $item->item_id }}" />
                                <div class="mt-2 grid gap-2 sm:grid-cols-3">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Qty diterima</label>
                                        <input type="number" step="0.01" min="0" max="{{ $remainingByItem[$item->item_id] }}" name="items[{{ $index }}][quantity_received]" value="{{ old('items.' . $index . '.quantity_received', $remainingByItem[$item->item_id]) }}" class="input" required />
                                        <p class="text-xs text-slate-500 mt-1">Sisa yang belum diterima: {{ number_format((float) $remainingByItem[$item->item_id], 2) }}</p>
                                    </div>
                                    <div>
                                        <label for="receipt-condition-{{ $index }}" class="block text-xs font-medium text-slate-600 mb-1.5">Kondisi</label>
                                        <x-searchable-select id="receipt-condition-{{ $index }}" name="items[{{ $index }}][condition]"
                                            :options="[['value' => 'baik', 'label' => 'Baik'], ['value' => 'rusak', 'label' => 'Rusak'], ['value' => 'tidak_sesuai', 'label' => 'Tidak sesuai']]"
                                            :selected="old('items.' . $index . '.condition', 'baik')"
                                            placeholder="Pilih kondisi"
                                            search-placeholder="Cari kondisi"
                                            required class="mt-1" />
                                    </div>
                                    <div>
                                        <label for="discrepancy-{{ $index }}" class="block text-xs font-medium text-slate-600 mb-1.5">Catatan selisih</label>
                                        <input id="discrepancy-{{ $index }}" type="text" name="items[{{ $index }}][discrepancy_notes]" value="{{ old('items.' . $index . '.discrepancy_notes') }}" class="input" />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button class="btn-primary w-full justify-center">Simpan penerimaan</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
