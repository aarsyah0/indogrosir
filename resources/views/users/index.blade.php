<x-app-layout title="Manajemen Pengguna">
    <div class="master-page-heading">
        <div class="min-w-0">
            <h2 class="master-page-title">Manajemen Pengguna</h2>
            <p class="master-page-description">Akun, divisi, peran, dan akses aplikasi.</p>
        </div>
    </div>

    <div class="grid gap-4 2xl:grid-cols-12">
        <!-- Users list -->
        <section class="users-list-card master-data-card order-1 card min-w-0 2xl:order-2 2xl:col-span-8">
            <div class="card-header flex-col sm:flex-row sm:items-center">
                <div>
                    <h2 class="card-title">Daftar pengguna</h2>
                    <p class="card-subtitle mt-0.5">{{ $users->total() }} pengguna</p>
                </div>
                <x-master-search id="users-search" label="Cari nama atau email" placeholder="Cari nama atau email" reset-route="users.index" />
            </div>

            <div class="card-body">
                <div class="table-wrap">
                    <table class="min-w-full text-sm">
                        <thead class="bg-navy-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pengguna</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Divisi</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Peran</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Kelola</th>
                            </tr>
                        </thead>
                        <tbody x-data="{ editingUser: null }">
                            @forelse ($users as $user)
                                <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                    <td data-label="Pengguna" class="px-4 py-3">
                                        <p class="font-semibold text-slate-900">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                    </td>
                                    <td data-label="Divisi" class="px-4 py-3 text-slate-600">{{ $user->division?->name ?? '—' }}</td>
                                    <td data-label="Peran" class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse ($user->roles as $role)
                                                <span class="badge badge-default">{{ $role->name }}</span>
                                            @empty
                                                <span class="text-xs text-slate-400">Belum ada peran</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td data-label="Status" class="px-4 py-3">
                                        <span class="badge badge-{{ $user->is_active ? 'success' : 'default' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </td>
                                    <td data-label="Kelola" class="px-4 py-3 text-right">
                                        <button type="button"
                                            class="users-edit-trigger inline-flex min-h-11 items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-600 transition-colors hover:border-navy-200 hover:bg-navy-50 hover:text-navy-800"
                                            @click="editingUser = editingUser === {{ $user->id }} ? null : {{ $user->id }}"
                                            :aria-expanded="(editingUser === {{ $user->id }}).toString()"
                                            aria-controls="user-edit-panel-{{ $user->id }}"
                                            aria-label="Edit pengguna {{ $user->name }}">
                                            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>
                                            <span>Edit pengguna</span>
                                        </button>
                                    </td>
                                </tr>
                                <tr id="user-edit-panel-{{ $user->id }}" x-show="editingUser === {{ $user->id }}" x-cloak x-transition.opacity.duration.150ms class="users-edit-row">
                                    <td colspan="5" class="p-0">
                                        <form method="POST" action="{{ route('users.update', $user) }}" class="users-edit-form">
                                            @csrf
                                            @method('PUT')
                                            <div class="users-edit-form-heading">
                                                <div>
                                                    <h3 class="text-sm font-semibold text-slate-900">Edit pengguna</h3>
                                                    <p class="mt-0.5 text-xs text-slate-500">Perbarui informasi akun, divisi, peran, atau status.</p>
                                                </div>
                                                <span class="badge badge-default max-w-full truncate">{{ $user->email }}</span>
                                            </div>
                                            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                                <div>
                                                    <label for="user-name-{{ $user->id }}" class="mb-1.5 block text-xs font-medium text-slate-600">Nama</label>
                                                    <input id="user-name-{{ $user->id }}" name="name" value="{{ old('name', $user->name) }}" class="input" required />
                                                </div>
                                                <div>
                                                    <label for="user-email-{{ $user->id }}" class="mb-1.5 block text-xs font-medium text-slate-600">Email</label>
                                                    <input id="user-email-{{ $user->id }}" type="email" name="email" value="{{ old('email', $user->email) }}" class="input" required />
                                                </div>
                                                <div>
                                                    <label for="user-division-{{ $user->id }}" class="mb-1.5 block text-xs font-medium text-slate-600">Divisi</label>
                                                    <x-searchable-select id="user-division-{{ $user->id }}" name="division_id"
                                                        :options="$divisions->map(fn ($division) => ['value' => $division->id, 'label' => $division->name])->prepend(['value' => '', 'label' => 'Tanpa divisi'])"
                                                        :selected="old('division_id', $user->division_id) ?? ''"
                                                        placeholder="Tanpa divisi"
                                                        search-placeholder="Cari divisi"
                                                        class="mt-1" />
                                                </div>
                                                <div>
                                                    <label for="user-password-{{ $user->id }}" class="mb-1.5 block text-xs font-medium text-slate-600">Kata sandi baru</label>
                                                    <input id="user-password-{{ $user->id }}" type="password" name="password" class="input" placeholder="Kosongkan jika tidak berubah" autocomplete="new-password" />
                                                </div>
                                                <fieldset class="users-role-fieldset sm:col-span-2 xl:col-span-3">
                                                    <legend class="text-sm font-semibold text-slate-700">Peran</legend>
                                                    <div class="mt-2 grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                                                        @foreach ($roles as $role)
                                                            <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-normal transition-colors hover:border-navy-200 hover:bg-navy-50/50">
                                                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" style="width: auto;" />
                                                                {{ $role->name }}
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </fieldset>
                                                <label class="flex cursor-pointer items-center gap-2.5 self-end rounded-lg border border-slate-200 bg-white px-3 py-3 text-sm font-medium text-slate-700">
                                                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) />
                                                    Akun aktif
                                                </label>
                                            </div>
                                            <div class="users-edit-actions">
                                                <button class="btn-primary justify-center">Simpan perubahan</button>
                                                <button type="button" class="btn-secondary" @click="editingUser = null">Batal</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center">
                                        <div class="empty-state">
                                            <p class="text-sm text-slate-500">Belum ada pengguna.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="users-pagination">
                    <span class="text-xs text-slate-500">{{ $users->total() }} pengguna</span>
                    {{ $users->links() }}
                </div>
            </div>
        </section>

        <!-- Add user form -->
        <section class="order-2 card h-fit 2xl:order-1 2xl:col-span-4">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Tambah pengguna</h2>
                    <p class="card-subtitle mt-0.5">Tentukan identitas, divisi, dan minimal satu peran.</p>
                </div>
            </div>
            <div class="card-body pt-0">
                <form method="POST" action="{{ route('users.store') }}" class="users-create-form">
                    @csrf
                    <div>
                        <label for="new-user-name" class="mb-1.5 block text-sm font-medium text-slate-700">Nama lengkap</label>
                        <input id="new-user-name" name="name" class="input" value="{{ old('name') }}" autocomplete="name" required />
                    </div>
                    <div>
                        <label for="new-user-email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                        <input id="new-user-email" type="email" name="email" class="input" value="{{ old('email') }}" autocomplete="email" required />
                    </div>
                    <div>
                        <label for="new-user-password" class="mb-1.5 block text-sm font-medium text-slate-700">Kata sandi awal</label>
                        <input id="new-user-password" type="password" name="password" class="input" autocomplete="new-password" required />
                        <p class="mt-1 text-xs text-slate-500">Minimal 8 karakter.</p>
                    </div>
                    <div>
                        <label for="new-user-division" class="mb-1.5 block text-sm font-medium text-slate-700">Divisi</label>
                        <x-searchable-select id="new-user-division" name="division_id"
                            :options="$divisions->map(fn ($division) => ['value' => $division->id, 'label' => $division->name])->prepend(['value' => '', 'label' => 'Tanpa divisi'])"
                            :selected="old('division_id') ?? ''"
                            placeholder="Tanpa divisi"
                            search-placeholder="Cari divisi"
                            class="mt-1" />
                    </div>
                    <fieldset class="users-role-fieldset">
                        <legend class="text-sm font-semibold text-slate-700">Peran</legend>
                        <div class="mt-2 grid gap-2 sm:grid-cols-2 2xl:grid-cols-1">
                            @foreach ($roles as $role)
                                <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-normal transition-colors hover:border-navy-200 hover:bg-navy-50/50">
                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" style="width: auto;" />
                                    {{ $role->name }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <button class="btn-primary w-full justify-center">Buat akun</button>
                </form>
            </div>
        </section>
    </div>
</x-app-layout>
