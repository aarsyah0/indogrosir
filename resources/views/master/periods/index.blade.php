<x-app-layout title="Periode Pengajuan">
    <div class="master-page-heading">
        <div class="min-w-0">
            <h2 class="master-page-title">Periode Pengajuan</h2>
            <p class="master-page-description">Atur kapan staf dapat mengirim pengajuan kebutuhan.</p>
        </div>
    </div>

    <div class="grid gap-4 2xl:grid-cols-12">
        <!-- Create period form -->
        <section class="order-2 card h-fit 2xl:order-1 2xl:col-span-4">
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
        <section class="order-1 card master-data-card 2xl:order-2 2xl:col-span-8">
            <div class="card-header flex-col sm:flex-row sm:items-center">
                <h2 class="card-title">Daftar periode</h2>
                <x-master-search id="period-search" label="Cari nama atau tahun periode" placeholder="Nama atau tahun periode" reset-route="master.periods.index" />
            </div>
            <div class="card-body">
                <div class="table-wrap">
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
                <div class="flex flex-col gap-2 border-t border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <span class="text-xs text-slate-500">{{ $periods->total() }} periode</span>
                    {{ $periods->links() }}
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
