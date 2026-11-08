<x-app-layout title="Audit Trail">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full min-w-0 sm:flex-1">
            <x-page-header
                header="Audit Trail"
                subheader="Riwayat perubahan dan tindakan pengguna di aplikasi."
                class="mb-4"
            >
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                    {{ $logs->total() }} aktivitas
                </span>
            </x-page-header>
        </div>
    </div>

    <section class="card overflow-hidden">
        <div class="card-body">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="section-heading">Aktivitas terbaru</h2>
                <form method="GET" class="flex w-full gap-2 sm:max-w-sm">
                    <div class="relative flex-1">
                        <label class="sr-only" for="audit-search">Cari nama aksi</label>
                        <input id="audit-search" type="search" name="q" value="{{ request('q') }}" class="input pl-9" placeholder="Cari nama aksi" />
                        <svg aria-hidden="true" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                        <button type="submit" class="absolute right-1 top-1/2 -translate-y-1/2 btn-sm btn-secondary">Cari</button>
                    </div>
                    @if (request()->filled('q'))
                        <a href="{{ route('audit.index') }}" class="btn-secondary">Reset</a>
                    @endif
                </form>
            </div>
            <div class="mt-3 table-wrap">
                <table class="min-w-full text-sm">
                    <thead class="bg-navy-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Waktu</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pelaku</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Jenis data</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-800">{{ format_date($log->created_at) }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $log->created_at->format('H:i') }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge badge-default">{{ $log->action }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $log->actor?->name ?? 'Sistem' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $log->model_type ? class_basename($log->model_type) : '—' }}</td>
                                <td class="px-4 py-3 max-w-md text-slate-600">{{ $log->notes ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center">
                                    <div class="empty-state">
                                        <p class="text-sm text-slate-500">Tidak ada aktivitas yang cocok.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
            <span class="text-xs text-slate-500">{{ $logs->total() }} aktivitas</span>
            {{ $logs->withQueryString()->links() }}
        </div>
    </section>
</x-app-layout>
