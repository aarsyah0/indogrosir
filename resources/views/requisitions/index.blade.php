<x-app-layout title="Pengajuan Saya">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-auto">
            <x-page-header
                header="Pengajuan Saya"
                subheader="Lihat dan kelola pengajuan kebutuhan barang."
                class="mb-4"
            >
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                    <form method="GET" class="flex flex-1 gap-2 sm:flex-row">
                        <label class="sr-only" for="req-search">Cari nomor pengajuan</label>
                        <input id="req-search" type="search" name="q" value="{{ request('q') }}"
                            class="input min-w-0 flex-1"
                            placeholder="Cari nomor pengajuan..." />
                        <x-searchable-select
                            name="status"
                            aria-label="Filter status pengajuan"
                            :options="$statuses->map(fn ($status) => ['value' => $status->value, 'label' => $status->label()])->prepend(['value' => '', 'label' => 'Semua status'])"
                            :selected="request('status')"
                            placeholder="Semua status"
                            search-placeholder="Cari status"
                            class="w-full sm:w-56"
                        />
                        <button type="submit" class="btn-secondary">Filter</button>
                        @if (request()->filled('q') || request('status'))
                            <a href="{{ route('requisitions.index') }}" class="btn-ghost">Reset</a>
                        @endif
                    </form>
                </div>
            </x-page-header>
        </div>
        @can('create', App\Models\Requisition::class)
            <div class="mt-3 sm:mt-0">
                <a href="{{ route('requisitions.create') }}" class="btn-primary">
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Pengajuan baru
                </a>
            </div>
        @endcan
    </div>

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
</x-app-layout>
