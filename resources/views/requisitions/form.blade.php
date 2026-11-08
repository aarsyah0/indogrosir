<x-app-layout :title="$requisition ? 'Ubah pengajuan' : 'Pengajuan baru'">
    @php
        $existing = old('items');
        if (!$existing && $version) {
            $existing = $version->items->map(fn ($i) => [
                'item_id' => $i->item_id,
                'quantity' => (float) $i->quantity_requested,
                'need_reason' => $i->need_reason,
            ])->values();
        }
        $existing = $existing ?: [['item_id' => '', 'quantity' => '', 'need_reason' => '']];
    @endphp
    <form method="POST" action="{{ $requisition ? route('requisitions.update', $requisition) : route('requisitions.store') }}"
        x-data="form()" @searchable-change="calc()" class="space-y-4">
        @csrf
        @if ($requisition) @method('PUT') @endif

        <div class="card">
            <div class="card-body">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="request-period" class="block text-sm font-medium text-slate-700 mb-1.5">Periode</label>
                        <x-searchable-select
                            id="request-period"
                            name="request_period_id"
                            :options="$periods->map(fn ($period) => ['value' => $period->id, 'label' => $period->name])"
                            :selected="old('request_period_id', $requisition?->request_period_id)"
                            placeholder="Pilih periode"
                            search-placeholder="Cari periode"
                            required
                            class="mt-1"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Divisi</label>
                        <input class="input mt-1 bg-slate-50 text-slate-500" value="{{ auth()->user()->division?->name }}" disabled>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-slate-700 mb-1.5">Catatan</label>
                        <textarea id="notes" name="notes" class="input mt-1" rows="2">{{ old('notes', $requisition?->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Daftar barang</h2>
                <button type="button" class="btn-secondary" @click="add()">
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Tambah barang
                </button>
            </div>
            <p class="card-subtitle">Pilih dari daftar atau ketik kode/nama barang untuk mencari. Barang yang sama hanya boleh ditambahkan satu kali.</p>
            <div class="card-body">
                <template x-for="(row, index) in rows" :key="index">
                    <div class="grid md:grid-cols-12 gap-2 mb-3 border-b border-slate-100 pb-3">
                        <div class="md:col-span-4">
                            <label class="sr-only" :for="`item-search-${index}`" x-text="`Barang ${index + 1}`"></label>
                            <x-searchable-select
                                x-model="row.item_id"
                                x-bind:id="`item-search-${index}`"
                                x-bind:name="`items[${index}][item_id]`"
                                :options-expression="'itemOptions'"
                                placeholder="Pilih barang"
                                search-placeholder="Cari kode atau nama barang"
                                required
                            />
                        </div>
                        <div class="md:col-span-2">
                            <input type="number" step="0.01" min="0.01" class="input" :name="`items[${index}][quantity]`" x-model="row.quantity" @input="calc()" placeholder="Qty" required>
                        </div>
                        <div class="md:col-span-5">
                            <input class="input" :name="`items[${index}][need_reason]`" x-model="row.need_reason" placeholder="Alasan kebutuhan" required>
                        </div>
                        <div class="md:col-span-1">
                            <button type="button" class="btn-secondary w-full" @click="remove(index)">Hapus</button>
                        </div>
                    </div>
                </template>
                <p class="text-sm text-slate-600">Estimasi total: <span class="font-semibold text-slate-900" x-text="totalLabel"></span></p>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-end">
            <button class="btn-secondary" name="submit" value="0">Simpan draft</button>
            <button class="btn-primary" name="submit" value="1" onclick="return confirm('Kirim pengajuan kepada Manager?')">Kirim pengajuan</button>
        </div>
    </form>
    @push('scripts')
        <script>
            function form() {
                const catalog = @json($items->map(fn($i) => ['id' => $i->id, 'price' => (float)$i->reference_price, 'label' => $i->code . ' — ' . $i->name]));
                return {
                    itemOptions: catalog.map(item => ({value: item.id, label: item.label})),
                    rows: @json($existing),
                    totalLabel: 'Rp 0',
                    add() { this.rows.push({item_id:'', quantity:'', need_reason:''}); },
                    remove(i) { this.rows.splice(i,1); this.calc(); },
                    calc() {
                        let t = 0;
                        this.rows.forEach(r => {
                            const item = catalog.find(c => String(c.id) === String(r.item_id));
                            t += (parseFloat(r.quantity)||0) * (item?.price || 0);
                        });
                        this.totalLabel = new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR', maximumFractionDigits:0}).format(t);
                    },
                    init() { this.calc(); }
                }
            }
        </script>
    @endpush
</x-app-layout>
