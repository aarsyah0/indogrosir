@props(['requisitions'])
@if ($requisitions->isEmpty())
    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 px-4 py-7 text-center">
        <p class="text-sm font-medium text-slate-700">Belum ada pengajuan.</p>
        <p class="mt-1 text-xs text-slate-500">Pengajuan yang masuk akan muncul di sini.</p>
    </div>
@else
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
            @foreach ($requisitions as $req)
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
            @endforeach
        </tbody>
    </table>
</div>
@endif
