<x-app-layout title="Edit Divisi">
    <div class="master-page-heading">
        <div class="min-w-0">
            <h2 class="master-page-title">Edit Divisi</h2>
            <p class="master-page-description">Perbarui informasi {{ $division->name }} dan manager aktifnya.</p>
        </div>
        <a href="{{ route('master.divisions.index') }}" class="btn-secondary shrink-0">Kembali ke Master Divisi</a>
    </div>

    <section class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('master.divisions.update', $division) }}" class="space-y-5">
                @csrf
                @method('PUT')
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="division-code" class="mb-1.5 block text-sm font-medium text-slate-700">Kode divisi</label>
                        <input id="division-code" name="code" value="{{ old('code', $division->code) }}" class="input" required />
                        @error('code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="division-name" class="mb-1.5 block text-sm font-medium text-slate-700">Nama divisi</label>
                        <input id="division-name" name="name" value="{{ old('name', $division->name) }}" class="input" required />
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="division-manager" class="mb-1.5 block text-sm font-medium text-slate-700">Ganti manager <span class="font-normal text-slate-500">(opsional)</span></label>
                        <x-searchable-select id="division-manager" name="manager_id"
                            :options="$users->map(fn ($user) => ['value' => $user->id, 'label' => $user->name])->prepend(['value' => '', 'label' => 'Pertahankan manager saat ini'])"
                            :selected="old('manager_id', $division->activeManagers->first()?->id) ?? ''"
                            placeholder="Pertahankan manager saat ini"
                            search-placeholder="Cari nama manager" />
                        @error('manager_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <label class="flex items-center gap-2.5 text-sm font-medium text-slate-700">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $division->is_active)) />
                    Divisi aktif
                </label>
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <a href="{{ route('master.divisions.index') }}" class="btn-secondary">Batal</a>
                    <button class="btn-primary">Simpan perubahan</button>
                </div>
            </form>
        </div>
    </section>
</x-app-layout>
