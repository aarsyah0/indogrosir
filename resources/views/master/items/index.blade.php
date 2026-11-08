<x-app-layout title="Master Barang">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-auto">
            <x-page-header
                header="Master Barang"
                subheader="Katalog barang, satuan, harga referensi, dan batas stok."
                class="mb-4"
            >
                <a href="{{ route('master.items.create') }}" class="btn-primary">
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Tambah barang
                </a>
            </x-page-header>
        </div>
        <form method="GET" class="w-full sm:w-auto">
            <label class="sr-only" for="items-search">Cari kode atau nama barang</label>
            <div class="relative w-full sm:w-80">
                <input id="items-search" type="search" name="q" value="{{ request('q') }}"
                    class="input pl-9"
                    placeholder="Cari kode atau nama..." />
                <svg aria-hidden="true" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                <button type="submit" class="absolute right-1 top-1/2 -translate-y-1/2 btn-sm btn-secondary">Cari</button>
                @if (request()->filled('q'))
                    <a href="{{ route('master.items.index') }}" class="absolute right-10 top-1/2 -translate-y-1/2 btn-sm btn-ghost">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($items as $item)
                    <div class="flex items-start gap-3 rounded-lg border border-slate-200/80 p-3 transition-colors hover:bg-slate-50">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-navy-100 text-sm font-bold text-navy-700">
                            {{ $item->code }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-slate-900 truncate">{{ $item->name }}</p>
                            <p class="text-xs text-slate-500">{{ $item->category?->name ?? '—' }} &middot; {{ $item->unit?->name ?? '—' }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-semibold text-slate-800 tabular-nums">{{ format_money($item->reference_price) }}</p>
                            <p class="text-xs text-slate-500">Stok: {{ $item->currentStock() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 flex items-center justify-center py-10">
                        <div class="empty-state">
                            <p class="text-sm text-slate-500">{{ request()->filled('q') ? 'Tidak ada barang yang cocok. Periksa kata kunci pencarian.' : 'Belum ada data barang. Tambahkan barang untuk mulai membangun katalog.' }}</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
            <span class="text-xs text-slate-500">{{ $items->total() }} barang ditemukan</span>
            <div class="flex items-center gap-2">
                {{ $items->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
