<x-app-layout title="Master Divisi">
    <div class="master-page-heading">
        <div class="min-w-0">
            <h2 class="master-page-title">Master Divisi</h2>
            <p class="master-page-description">Kelola struktur organisasi untuk pengajuan dan persetujuan.</p>
        </div>
    </div>

    <div class="grid gap-4 2xl:grid-cols-12">
        <!-- Create division form -->
        <section class="order-2 card h-fit 2xl:order-1 2xl:col-span-4">
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
        <section class="order-1 card master-data-card 2xl:order-2 2xl:col-span-8">
            <div class="card-header flex-col sm:flex-row sm:items-center">
                <h2 class="card-title">Daftar divisi</h2>
                <x-master-search id="division-search" label="Cari kode atau nama divisi" placeholder="Kode atau nama divisi" reset-route="master.divisions.index" />
            </div>
            <div class="card-body">
                <div class="table-wrap">
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
                <div class="flex flex-col gap-2 border-t border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <span class="text-xs text-slate-500">{{ $divisions->total() }} divisi</span>
                    {{ $divisions->links() }}
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
