<x-app-layout title="Pengajuan Masuk">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-auto">
            <x-page-header
                header="Pengajuan Masuk"
                subheader="Lihat dan kelola pengajuan dari divisi lain."
                class="mb-4"
            >
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                    {{ $requisitions->total() }} pengajuan
                </span>
            </x-page-header>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form class="flex gap-2" method="GET">
                <label class="sr-only" for="inbox-search">Cari nomor pengajuan</label>
                <div class="relative flex-1">
                    <input id="inbox-search" type="search" name="q" value="{{ request('q') }}"
                        class="input pl-9"
                        placeholder="Cari nomor pengajuan..." />
                    <svg aria-hidden="true" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                    <button type="submit" class="absolute right-1 top-1/2 -translate-y-1/2 btn-sm btn-secondary">Cari</button>
                </div>
            </form>
            <x-requisition-table :requisitions="$requisitions" />
        </div>
        <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
            <span class="text-xs text-slate-500">{{ $requisitions->total() }} pengajuan</span>
            {{ $requisitions->links() }}
        </div>
    </div>
</x-app-layout>
