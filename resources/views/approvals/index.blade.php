<x-app-layout :title="$title">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full min-w-0 sm:flex-1">
            <x-page-header
                header="Antrian Persetujuan"
                subheader="Lihat dan kelola pengajuan yang menunggu persetujuan."
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
            <div class="table-wrap" role="region" aria-label="Tabel antrian persetujuan" tabindex="0">
                <table class="min-w-full text-sm">
                    <thead class="bg-navy-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nomor</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Divisi</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Requester</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Estimasi</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requisitions as $req)
                            <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                <td class="px-4 py-3 font-semibold text-navy-900">{{ $req->number }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $req->division?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $req->requester?->name ?? '—' }}</td>
                                <td class="px-4 py-3 font-mono text-sm font-semibold text-slate-800 tabular-nums">{{ format_money($req->estimated_total) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route($showRoute, $req) }}" class="inline-flex min-h-11 items-center gap-1.5 rounded-lg px-3 text-brand font-medium text-sm transition-colors duration-150 hover:bg-brand-50 hover:text-brand-700">
                                        Periksa
                                        <svg aria-hidden="true" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 13l5 5 5-5M7 21l5-5 5 5"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center">
                                    <div class="empty-state">
                                        <p class="text-sm text-slate-500">Tidak ada pengajuan pada antrian ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
            <span class="text-xs text-slate-500">{{ $requisitions->total() }} pengajuan</span>
            {{ $requisitions->links() }}
        </div>
    </div>
</x-app-layout>
