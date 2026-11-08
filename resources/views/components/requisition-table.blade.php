@props(['requisitions'])
<div class="table-wrap">
    <table class="min-w-full text-sm">
        <thead class="bg-navy-50">
            <tr>
                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nomor</th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Divisi</th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Estimasi</th>
                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($requisitions as $req)
                <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                    <td class="px-4 py-3 font-semibold text-navy-900">{{ $req->number }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $req->division?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="badge badge-default">{{ $req->status->label() }}</span>
                    </td>
                    <td class="px-4 py-3 font-mono text-sm font-semibold text-slate-800 tabular-nums">{{ format_money($req->estimated_total) }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('requisitions.show', $req) }}" class="inline-flex items-center gap-1.5 text-brand font-medium text-sm transition-colors hover:text-brand-600">
                            Detail
                            <svg aria-hidden="true" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 13l5 5 5-5M7 21l5-5 5 5"/></svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center">
                        <div class="empty-state">
                            <p class="text-sm text-slate-500">Belum ada pengajuan.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
