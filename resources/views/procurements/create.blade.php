<x-app-layout title="Catat Pembelian">
    <div class="grid gap-4 xl:grid-cols-3">
        <!-- Requisition recap -->
        <div class="card xl:col-span-2">
            <div class="card-body">
                <h2 class="section-heading">Rekapan pengajuan</h2>
                <div class="mt-3 table-wrap">
                    <table class="min-w-full text-sm">
                        <thead class="bg-navy-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Barang</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Qty disetujui</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($version?->items ?? [] as $line)
                                <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $line->item_name_snapshot }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $line->quantity_sm_approved ?? $line->quantity_requested }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ format_money($line->reference_unit_price) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-10 text-center">
                                        <div class="empty-state">
                                            <p class="text-sm text-slate-500">Tidak ada item.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Purchase form -->
        <div class="card xl:col-span-1">
            <div class="card-body">
                <h2 class="section-heading">Form pembelian</h2>
                <form method="POST" action="{{ route('procurements.store') }}" enctype="multipart/form-data" class="mt-3 space-y-3">
                    @csrf
                    <input type="hidden" name="requisition_id" value="{{ $requisition->id }}" />

                    <div>
                        <label for="procurement-supplier" class="block text-sm font-medium text-slate-700 mb-1.5">Pemasok</label>
                        <x-searchable-select id="procurement-supplier" name="supplier_id"
                            :options="$suppliers->map(fn ($supplier) => ['value' => $supplier->id, 'label' => $supplier->name])->prepend(['value' => '', 'label' => 'Pilih pemasok'])"
                            :selected="old('supplier_id')"
                            placeholder="Pilih pemasok"
                            search-placeholder="Cari nama pemasok"
                            required class="mt-1" />
                    </div>

                    <div>
                        <label for="purchased-at" class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal pembelian</label>
                        <input id="purchased-at" type="date" name="purchased_at" class="input" value="{{ old('purchased_at', now()->toDateString()) }}" required />
                    </div>

                    <div>
                        <label for="invoice-number" class="block text-sm font-medium text-slate-700 mb-1.5">Nomor invoice</label>
                        <input id="invoice-number" name="invoice_number" class="input" value="{{ old('invoice_number') }}" />
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <div>
                            <label for="discount" class="block text-sm font-medium text-slate-700 mb-1.5">Diskon</label>
                            <input id="discount" type="number" step="0.01" min="0" name="discount" class="input" value="{{ old('discount', 0) }}" />
                        </div>
                        <div>
                            <label for="tax" class="block text-sm font-medium text-slate-700 mb-1.5">Pajak</label>
                            <input id="tax" type="number" step="0.01" min="0" name="tax" class="input" value="{{ old('tax', 0) }}" />
                        </div>
                        <div>
                            <label for="extra-cost" class="block text-sm font-medium text-slate-700 mb-1.5">Biaya lain</label>
                            <input id="extra-cost" type="number" step="0.01" min="0" name="extra_cost" class="input" value="{{ old('extra_cost', 0) }}" />
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-slate-700 mb-1.5">Catatan</label>
                        <textarea id="notes" name="notes" rows="2" class="input">{{ old('notes') }}</textarea>
                    </div>

                    @if (auth()->user()->hasPermission('procurement.authorize_over_qty'))
                        <div class="space-y-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                            <label class="flex items-start gap-2">
                                <input type="checkbox" name="over_qty_authorized" value="1" @checked(old('over_qty_authorized')) class="mt-0.5" />
                                <span><strong>Otorisasi pembelian melebihi jumlah disetujui</strong><span class="mt-0.5 block text-xs text-amber-800">Gunakan hanya jika jumlah tambahan sudah mendapat persetujuan.</span></span>
                            </label>
                            <div>
                                <label for="over-qty-reason" class="mb-1 block text-xs font-medium">Alasan otorisasi (wajib jika dicentang)</label>
                                <textarea id="over-qty-reason" name="over_qty_reason" rows="2" class="input">{{ old('over_qty_reason') }}</textarea>
                            </div>
                        </div>
                    @endif

                    <div>
                        <label for="proof" class="block text-sm font-medium text-slate-700 mb-1.5">Bukti pembelian</label>
                        <input id="proof" type="file" name="proof" class="input" accept=".pdf,.jpg,.jpeg,.png" />
                    </div>

                    <div class="space-y-3">
                        @foreach ($version?->items ?? [] as $index => $line)
                            <div class="rounded-lg border border-slate-200 p-3">
                                <p class="font-medium text-sm text-slate-800">{{ $line->item_name_snapshot }}</p>
                                <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $line->item_id }}" />
                                <div class="mt-2 grid gap-2 sm:grid-cols-3">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Qty dibeli</label>
                                        <input type="number" step="0.01" min="0" name="items[{{ $index }}][quantity_purchased]" value="{{ old('items.' . $index . '.quantity_purchased', $remainingByItem[$line->item_id] ?? ($line->quantity_sm_approved ?? $line->quantity_requested)) }}" class="input" required />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Harga satuan</label>
                                        <input type="number" step="0.01" min="0" name="items[{{ $index }}][unit_price]" value="{{ old('items.' . $index . '.unit_price', $line->reference_unit_price) }}" class="input" required />
                                    </div>
                                    <div>
                                        <label class="flex items-center gap-2.5 text-xs font-normal">
                                            <input type="checkbox" name="items[{{ $index }}][over_qty]" value="1" />
                                            Over qty
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button class="btn-primary w-full justify-center">Simpan pembelian</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
