<x-app-layout title="Verifikasi GA">
    <form method="POST" action="{{ route('approvals.ga.decide', $requisition) }}">
        @csrf
        <div class="grid gap-4 xl:grid-cols-3">
            <!-- Requisition detail -->
            <section class="card min-w-0 xl:col-span-2">
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
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Diminta</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Stok</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Rekomendasi GA</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Qty final</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Harga referensi</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Alasan perubahan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $row)
                                    @php($line = $row['line'])
                                    <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                        <td class="px-4 py-3">
                                            <input type="hidden" name="lines[{{ $line->id }}][id]" value="{{ $line->id }}">
                                            <p class="font-medium text-slate-800">{{ $line->item_name_snapshot }}</p>
                                            @if ($row['unreasonable'])
                                                <span class="badge badge-warning mt-1.5">Perlu ditinjau</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">{{ number_format((float) $line->quantity_requested, 2) }} {{ $line->unit_name_snapshot }}</td>
                                        <td class="px-4 py-3">
                                            <span class="text-sm font-semibold text-slate-800">{{ number_format((float) $row['stock'], 2) }}</span>
                                            <span class="text-xs text-slate-500">Rata-rata {{ number_format((float) $row['average_usage'], 2) }}/bln</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" min="0" step="0.01" name="lines[{{ $line->id }}][quantity_ga_recommended]" value="{{ old('lines.'.$line->id.'.quantity_ga_recommended', $line->quantity_ga_recommended ?? $row['recommended']) }}" class="input w-28">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" min="0" step="0.01" name="lines[{{ $line->id }}][quantity_ga_final]" value="{{ old('lines.'.$line->id.'.quantity_ga_final', $line->quantity_ga_final ?? $line->quantity_manager_approved ?? $line->quantity_requested) }}" class="input w-28" required>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">{{ format_money($line->reference_unit_price) }}</td>
                                        <td class="px-4 py-3">
                                            <input type="text" maxlength="1000" name="lines[{{ $line->id }}][ga_change_reason]" value="{{ old('lines.'.$line->id.'.ga_change_reason', $line->ga_change_reason) }}" class="input min-w-48" placeholder="Wajib jika qty berubah">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-10 text-center">
                                            <div class="empty-state">
                                                <p class="text-sm text-slate-500">Tidak ada barang untuk diverifikasi.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Decision aside -->
            <aside class="card h-fit xl:col-span-1">
                <div class="card-body">
                    <h2 class="section-heading">Keputusan GA</h2>
                    <div class="mt-3 space-y-3">
                        <div>
                            <label for="intent" class="block text-sm font-medium text-slate-700 mb-1.5">Tindakan</label>
                            <x-searchable-select id="intent" name="intent"
                                :options="[['value' => 'save', 'label' => 'Simpan verifikasi'], ['value' => 'submit_sam', 'label' => 'Finalisasi & kirim ke SAM'], ['value' => 'return_division', 'label' => 'Kembalikan ke divisi']]"
                                :selected="old('intent', 'submit_sam')"
                                placeholder="Pilih tindakan"
                                search-placeholder="Cari tindakan"
                                required class="mt-1" />
                        </div>
                        <div>
                            <label for="return_comment" class="block text-sm font-medium text-slate-700 mb-1.5">Catatan pengembalian</label>
                            <textarea id="return_comment" name="return_comment" rows="4" class="input" placeholder="Wajib diisi jika dikembalikan">{{ old('return_comment') }}</textarea>
                        </div>
                        <button type="submit" class="btn-primary w-full justify-center">Simpan keputusan</button>
                    </div>
                </div>
            </aside>
        </div>
    </form>
</x-app-layout>
