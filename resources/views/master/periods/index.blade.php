<x-app-layout title="Periode Pengajuan">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-auto">
            <x-page-header
                header="Periode Pengajuan"
                subheader="Atur kapan staf dapat mengirim pengajuan kebutuhan."
                class="mb-4"
            >
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                    {{ $periods->total() }} periode
                </span>
            </x-page-header>
        </div>
        <form method="GET" class="w-full sm:w-auto">
            <label class="sr-only" for="period-search">Cari nama atau tahun periode</label>
            <div class="relative w-full sm:w-80">
                <input id="period-search" type="search" name="q" value="{{ request('q') }}"
                    class="input pl-9"
                    placeholder="Nama atau tahun..." />
                <svg aria-hidden="true" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                <button type="submit" class="absolute right-1 top-1/2 -translate-y-1/2 btn-sm btn-secondary">Cari</button>
                @if (request()->filled('q'))
                    <a href="{{ route('master.periods.index') }}" class="absolute right-10 top-1/2 -translate-y-1/2 btn-sm btn-ghost">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-12">
        <!-- Create period form -->
        <section class="card xl:col-span-4">
            <div class="card-body">
                <h2 class="section-heading">Buka periode baru</h2>
                <p class="text-xs text-slate-500 mt-0.5">Tentukan bulan dan rentang tanggal pengajuan.</p>
                <form method="POST" action="{{ route('master.periods.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="period-year" class="block text-sm font-medium text-slate-700 mb-1.5">Tahun</label>
                            <input id="period-year" type="number" min="2020" name="year" class="input" value="{{ old('year', date('Y')) }}" required />
                        </div>
                        <div>
                            <label for="period-month" class="block text-sm font-medium text-slate-700 mb-1.5">Bulan</label>
                            <input id="period-month" type="number" min="1" max="12" name="month" class="input" value="{{ old('month', date('n')) }}" required />
                        </div>
                    </div>
                    <div>
                        <label for="period-start" class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal mulai</label>
                        <input id="period-start" type="date" name="start_date" class="input" value="{{ old('start_date') }}" required />
                    </div>
                    <div>
                        <label for="period-deadline" class="block text-sm font-medium text-slate-700 mb-1.5">Batas akhir</label>
                        <input id="period-deadline" type="date" name="deadline_date" class="input" value="{{ old('deadline_date') }}" required />
                        <p class="text-xs text-slate-500 mt-1">Tidak boleh lebih awal dari tanggal mulai.</p>
                    </div>
                    <button class="btn-primary w-full justify-center">Buka periode</button>
                </form>
            </div>
        </section>

        <!-- Periods list -->
        <section class="card xl:col-span-8">
            <div class="card-body">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="section-heading">Daftar periode</h2>
                </div>
                <div class="mt-3 table-wrap">
                    <table class="min-w-full text-sm">
                        <thead class="bg-navy-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Periode</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Rentang pengajuan</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Kelola</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($periods as $period)
                                <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-semibold text-navy-900">{{ $period->name }}</td>
                                    <td class="px-4 py-3">
                                        <p class="text-slate-600">{{ format_date($period->start_date) }} — {{ format_date($period->deadline_date) }}</p>
                                        <p class="text-xs text-slate-500">Batas akhir pengajuan</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge badge-{{ $period->status === 'open' ? 'success' : 'default' }}">{{ $period->status === 'open' ? 'Terbuka' : 'Ditutup' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <details class="group text-left">
                                            <summary class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50">
                                                <svg aria-hidden="true" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                                Edit periode
                                                <svg aria-hidden="true" class="h-3 w-3 transition-transform group-open:rotate-180" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 3 5 5-5 5"/></svg>
                                            </summary>
                                            <form method="POST" action="{{ route('master.periods.update', $period) }}" class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-left space-y-3">
                                                @csrf
                                                @method('PUT')
                                                <div>
                                                    <label for="period-start-{{ $period->id }}" class="block text-xs font-medium text-slate-600 mb-1.5">Tanggal mulai</label>
                                                    <input id="period-start-{{ $period->id }}" type="date" name="start_date" value="{{ old('start_date', $period->start_date->format('Y-m-d')) }}" class="input" required />
                                                </div>
                                                <div>
                                                    <label for="period-deadline-{{ $period->id }}" class="block text-xs font-medium text-slate-600 mb-1.5">Batas akhir</label>
                                                    <input id="period-deadline-{{ $period->id }}" type="date" name="deadline_date" value="{{ old('deadline_date', $period->deadline_date->format('Y-m-d')) }}" class="input" required />
                                                </div>
                                                <div>
                                                    <label for="period-status-{{ $period->id }}" class="block text-xs font-medium text-slate-600 mb-1.5">Status</label>
                                                    <x-searchable-select id="period-status-{{ $period->id }}" name="status"
                                                        :options="[['value' => 'open', 'label' => 'Terbuka'], ['value' => 'closed', 'label' => 'Ditutup']]"
                                                        :selected="old('status', $period->status)"
                                                        placeholder="Pilih status"
                                                        search-placeholder="Cari status"
                                                        required class="mt-1" />
                                                </div>
                                                <button class="btn-primary w-full justify-center">Simpan perubahan</button>
                                            </form>
                                        </details>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center">
                                        <div class="empty-state">
                                            <p class="text-sm text-slate-500">Belum ada periode. Buat periode agar staf dapat mulai mengirim pengajuan.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
                    <span class="text-xs text-slate-500">{{ $periods->total() }} periode</span>
                    {{ $periods->links() }}
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
