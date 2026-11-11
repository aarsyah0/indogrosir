<x-app-layout title="Master Barang">
    <div class="master-page-heading">
        <div class="min-w-0">
            <h2 class="master-page-title">Master Barang</h2>
            <p class="master-page-description">Katalog barang, satuan, harga referensi, dan batas stok.</p>
        </div>
        <div class="flex shrink-0 flex-wrap gap-2">
            <a href="{{ route('master.categories.index') }}" class="btn-secondary">Kelola kategori</a>
            <a href="{{ route('master.items.create') }}" class="btn-primary">
                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Tambah barang
            </a>
        </div>
    </div>

    <section class="card">
        <div class="card-header flex-col sm:flex-row sm:items-center">
            <div>
                <h3 class="card-title">Katalog barang</h3>
                <p class="card-subtitle mt-0.5">{{ $items->total() }} barang ditemukan</p>
            </div>
            <x-master-search id="items-search" label="Cari kode atau nama barang" placeholder="Cari kode atau nama barang" reset-route="master.items.index" />
        </div>
        <div class="card-body">
            <div class="grid gap-3 sm:grid-cols-2 2xl:grid-cols-3">
                @forelse ($items as $item)
                    <article class="flex min-w-0 items-center gap-3 rounded-xl border border-slate-200/80 p-3 transition duration-150 hover:border-navy-200 hover:bg-navy-50/40 hover:shadow-sm">
                        <div class="flex min-h-11 min-w-12 shrink-0 items-center justify-center rounded-lg bg-navy-50 px-2 text-center text-xs font-bold leading-tight text-navy-700 ring-1 ring-inset ring-navy-100">
                            {{ $item->code }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-900" title="{{ $item->name }}">{{ $item->name }}</p>
                            <p class="mt-0.5 truncate text-xs text-slate-500">{{ $item->category?->name ?? '—' }} &middot; {{ $item->unit?->name ?? '—' }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-sm font-semibold tabular-nums text-slate-800">{{ format_money($item->reference_price) }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">Stok: {{ $item->currentStock() }}</p>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full flex items-center justify-center py-10">
                        <div class="empty-state">
                            <p class="text-sm text-slate-500">{{ request()->filled('q') ? 'Tidak ada barang yang cocok. Periksa kata kunci pencarian.' : 'Belum ada data barang. Tambahkan barang untuk mulai membangun katalog.' }}</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
            <div class="flex items-center gap-2">
                {{ $items->links() }}
            </div>
        </div>
    </section>
</x-app-layout>
