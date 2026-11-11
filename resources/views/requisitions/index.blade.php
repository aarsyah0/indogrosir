<x-app-layout title="Pengajuan Saya">
    <x-page-header
        header="Pengajuan Saya"
        subheader="Lihat dan kelola pengajuan kebutuhan barang."
        class="mb-4"
    >
        @can('create', App\Models\Requisition::class)
            <x-slot:actions>
                <a href="{{ route('requisitions.create') }}" class="btn-primary">
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Pengajuan baru
                </a>
            </x-slot:actions>
        @endcan
        <form method="GET" class="grid grid-cols-1 gap-2 sm:grid-cols-[minmax(0,1fr)_14rem_auto_auto] sm:items-center">
            <label class="sr-only" for="req-search">Cari nomor pengajuan</label>
            <input id="req-search" type="search" name="q" value="{{ request('q') }}"
                class="input min-w-0"
                placeholder="Cari nomor pengajuan..." />
            <x-searchable-select
                name="status"
                aria-label="Filter status pengajuan"
                :options="$statuses->map(fn ($status) => ['value' => $status->value, 'label' => $status->label()])->prepend(['value' => '', 'label' => 'Semua status'])"
                :selected="request('status')"
                placeholder="Semua status"
                search-placeholder="Cari status"
                class="w-full"
            />
            <button type="submit" class="btn-secondary justify-center">Filter</button>
            @if (request()->filled('q') || request('status'))
                <a href="{{ route('requisitions.index') }}" class="btn-ghost justify-center">Reset</a>
            @endif
        </form>
    </x-page-header>

    @if ($requisitions->isEmpty())
        <div class="card">
            <div class="card-body">
                <div class="empty-state py-8">
                    <svg aria-hidden="true" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h8l5 5v13H7z"/><path d="M15 3v6h5M10 13h7m-7 4h7"/></svg>
                    @if (request()->filled('q') || request('status'))
                        <p class="empty-title">Tidak ada pengajuan yang cocok</p>
                        <p class="empty-desc">Coba ubah kata kunci atau status filter.</p>
                    @else
                        <p class="empty-title">Belum ada pengajuan</p>
                        <p class="empty-desc">Pengajuan yang Anda buat akan muncul di sini.</p>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <x-requisition-table :requisitions="$requisitions" />
            </div>
            <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
                <span class="text-xs text-slate-500">{{ $requisitions->total() }} pengajuan</span>
                <div class="flex items-center gap-2">
                    {{ $requisitions->links() }}
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
