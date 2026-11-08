<x-app-layout title="Master Divisi">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-auto">
            <x-page-header
                header="Master Divisi"
                subheader="Kelola struktur organisasi untuk pengajuan dan persetujuan."
                class="mb-4"
            >
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                    {{ $divisions->total() }} divisi
                </span>
            </x-page-header>
        </div>
        <form method="GET" class="w-full sm:w-auto">
            <label class="sr-only" for="division-search">Cari kode atau nama divisi</label>
            <div class="relative w-full sm:w-80">
                <input id="division-search" type="search" name="q" value="{{ request('q') }}"
                    class="input pl-9"
                    placeholder="Kode atau nama..." />
                <svg aria-hidden="true" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                <button type="submit" class="absolute right-1 top-1/2 -translate-y-1/2 btn-sm btn-secondary">Cari</button>
                @if (request()->filled('q'))
                    <a href="{{ route('master.divisions.index') }}" class="absolute right-10 top-1/2 -translate-y-1/2 btn-sm btn-ghost">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-12">
        <!-- Create division form -->
        <section class="card xl:col-span-4">
            <div class="card-body">
                <h2 class="section-heading">Tambah divisi</h2>
                <p class="text-xs text-slate-500 mt-0.5">Gunakan kode unik yang mudah dikenali.</p>
                <form method="POST" action="{{ route('master.divisions.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label for="division-code" class="block text-sm font-medium text-slate-700 mb-1.5">Kode divisi</label>
                        <input id="division-code" name="code" class="input" required value="{{ old('code') }}" />
                    </div>
                    <div>
                        <label for="division-name" class="block text-sm font-medium text-slate-700 mb-1.5">Nama divisi</label>
                        <input id="division-name" name="name" class="input" required value="{{ old('name') }}" />
                    </div>
                    <button class="btn-primary w-full justify-center">Tambah divisi</button>
                </form>
            </div>
        </section>

        <!-- Divisions list -->
        <section class="card xl:col-span-8">
            <div class="card-body">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="section-heading">Daftar divisi</h2>
                </div>
                <div class="mt-3 table-wrap">
                    <table class="min-w-full text-sm">
                        <thead class="bg-navy-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Kode</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nama</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Manager aktif</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Kelola</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($divisions as $division)
                                <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-semibold text-navy-900">{{ $division->code }}</td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-slate-800">{{ $division->name }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ $division->activeManagers->first()?->name ?? 'Belum ditetapkan' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="badge badge-{{ $division->is_active ? 'success' : 'default' }}">{{ $division->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <details class="group text-left">
                                            <summary class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50">
                                                <svg aria-hidden="true" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                                Edit divisi
                                                <svg aria-hidden="true" class="h-3 w-3 transition-transform group-open:rotate-180" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 3 5 5-5 5"/></svg>
                                            </summary>
                                            <form method="POST" action="{{ route('master.divisions.update', $division) }}" class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-left space-y-4">
                                                @csrf
                                                @method('PUT')
                                                <div>
                                                    <label for="division-code-{{ $division->id }}" class="block text-xs font-medium text-slate-600 mb-1.5">Kode</label>
                                                    <input id="division-code-{{ $division->id }}" name="code" value="{{ old('code', $division->code) }}" class="input" required />
                                                </div>
                                                <div>
                                                    <label for="division-name-{{ $division->id }}" class="block text-xs font-medium text-slate-600 mb-1.5">Nama</label>
                                                    <input id="division-name-{{ $division->id }}" name="name" value="{{ old('name', $division->name) }}" class="input" required />
                                                </div>
                                                <div>
                                                    <label for="division-manager-{{ $division->id }}" class="block text-xs font-medium text-slate-600 mb-1.5">Ganti manager <span class="font-normal text-slate-500">(opsional)</span></label>
                                                    <x-searchable-select id="division-manager-{{ $division->id }}" name="manager_id"
                                                        :options="$users->map(fn ($user) => ['value' => $user->id, 'label' => $user->name])->prepend(['value' => '', 'label' => 'Pertahankan manager saat ini'])"
                                                        :selected="old('manager_id', $division->activeManagers->first()?->id) ?? ''"
                                                        placeholder="Pertahankan manager saat ini"
                                                        search-placeholder="Cari nama manager"
                                                        class="mt-1" />
                                                </div>
                                                <label class="flex items-center gap-2.5 text-sm font-normal">
                                                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $division->is_active)) />
                                                    Divisi aktif
                                                </label>
                                                <div class="flex gap-2">
                                                    <button class="btn-primary flex-1 justify-center">Simpan perubahan</button>
                                                    <button type="button" class="btn-secondary" @click="document.querySelector('details').open = false">Batal</button>
                                                </div>
                                            </form>
                                        </details>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center">
                                        <div class="empty-state">
                                            <p class="text-sm text-slate-500">Belum ada divisi.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
                    <span class="text-xs text-slate-500">{{ $divisions->total() }} divisi</span>
                    {{ $divisions->links() }}
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
