<x-app-layout title="Edit Periode">
    <div class="master-page-heading">
        <div class="min-w-0">
            <h2 class="master-page-title">Edit Periode</h2>
            <p class="master-page-description">Perbarui tanggal dan status untuk {{ $period->name }}.</p>
        </div>
        <a href="{{ route('master.periods.index') }}" class="btn-secondary shrink-0">Kembali ke Periode Pengajuan</a>
    </div>

    <section class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('master.periods.update', $period) }}" class="space-y-5">
                @csrf
                @method('PUT')
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="period-start" class="mb-1.5 block text-sm font-medium text-slate-700">Tanggal mulai</label>
                        <input id="period-start" type="date" name="start_date" value="{{ old('start_date', $period->start_date->format('Y-m-d')) }}" class="input" required />
                        @error('start_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="period-deadline" class="mb-1.5 block text-sm font-medium text-slate-700">Batas akhir</label>
                        <input id="period-deadline" type="date" name="deadline_date" value="{{ old('deadline_date', $period->deadline_date->format('Y-m-d')) }}" class="input" required />
                        @error('deadline_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="period-status" class="mb-1.5 block text-sm font-medium text-slate-700">Status</label>
                        <x-searchable-select id="period-status" name="status"
                            :options="[['value' => 'open', 'label' => 'Terbuka'], ['value' => 'closed', 'label' => 'Ditutup']]"
                            :selected="old('status', $period->status)"
                            placeholder="Pilih status"
                            search-placeholder="Cari status"
                            required />
                        @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <a href="{{ route('master.periods.index') }}" class="btn-secondary">Batal</a>
                    <button class="btn-primary">Simpan perubahan</button>
                </div>
            </form>
        </div>
    </section>
</x-app-layout>
