<x-app-layout title="Manajemen Pengguna">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full min-w-0 sm:flex-1">
            <x-page-header
                header="Manajemen Pengguna"
                subheader="Akun, divisi, peran, dan akses aplikasi."
                class="mb-4"
            >
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                    {{ $users->total() }} pengguna
                </span>
            </x-page-header>
        </div>
        <form method="GET" class="w-full sm:w-auto">
            <label class="sr-only" for="users-search">Cari nama atau email</label>
            <div class="relative w-full sm:w-80">
                <input id="users-search" type="search" name="q" value="{{ request('q') }}"
                    class="input pl-9"
                    placeholder="Nama atau email..." />
                <svg aria-hidden="true" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
                <button type="submit" class="absolute right-1 top-1/2 -translate-y-1/2 btn-sm btn-secondary">Cari</button>
                @if (request()->filled('q'))
                    <a href="{{ route('users.index') }}" class="absolute right-10 top-1/2 -translate-y-1/2 btn-sm btn-ghost">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-12">
        <!-- Add user form -->
        <section class="card xl:col-span-4">
            <div class="card-body">
                <h2 class="section-heading">Tambah pengguna</h2>
                <p class="text-xs text-slate-500 mt-0.5">Tentukan identitas, divisi, dan minimal satu peran.</p>
                <form method="POST" action="{{ route('users.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label for="new-user-name" class="block text-sm font-medium text-slate-700 mb-1.5">Nama lengkap</label>
                        <input id="new-user-name" name="name" class="input" value="{{ old('name') }}" autocomplete="name" required />
                    </div>
                    <div>
                        <label for="new-user-email" class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                        <input id="new-user-email" type="email" name="email" class="input" value="{{ old('email') }}" autocomplete="email" required />
                    </div>
                    <div>
                        <label for="new-user-password" class="block text-sm font-medium text-slate-700 mb-1.5">Kata sandi awal</label>
                        <input id="new-user-password" type="password" name="password" class="input" autocomplete="new-password" required />
                        <p class="text-xs text-slate-500 mt-1">Minimal 8 karakter.</p>
                    </div>
                    <div>
                        <label for="new-user-division" class="block text-sm font-medium text-slate-700 mb-1.5">Divisi</label>
                        <x-searchable-select id="new-user-division" name="division_id"
                            :options="$divisions->map(fn ($division) => ['value' => $division->id, 'label' => $division->name])->prepend(['value' => '', 'label' => 'Tanpa divisi'])"
                            :selected="old('division_id') ?? ''"
                            placeholder="Tanpa divisi"
                            search-placeholder="Cari divisi"
                            class="mt-1" />
                    </div>
                    <fieldset>
                        <legend class="text-sm font-semibold text-slate-700 mb-2.5">Peran</legend>
                        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                            @foreach ($roles as $role)
                                <label class="flex items-center gap-2.5 text-sm font-normal cursor-pointer">
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

        <!-- Users list -->
        <section class="card xl:col-span-8">
            <div class="card-body">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="section-heading">Daftar pengguna</h2>
                </div>
                <div class="mt-3 table-wrap">
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
                        <tbody>
                            @forelse ($users as $user)
                                <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-900">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ $user->division?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse ($user->roles as $role)
                                                <span class="badge badge-default">{{ $role->name }}</span>
                                            @empty
                                                <span class="text-xs text-slate-400">Belum ada peran</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge badge-{{ $user->is_active ? 'success' : 'default' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <details class="group text-left">
                                            <summary class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50">
                                                <svg aria-hidden="true" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                                Edit pengguna
                                                <svg aria-hidden="true" class="h-3 w-3 transition-transform group-open:rotate-180" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 3 5 5-5 5"/></svg>
                                            </summary>
                                            <form method="POST" action="{{ route('users.update', $user) }}" class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-left space-y-4">
                                                @csrf
                                                @method('PUT')
                                                <div>
                                                    <label for="user-name-{{ $user->id }}" class="block text-xs font-medium text-slate-600 mb-1.5">Nama</label>
                                                    <input id="user-name-{{ $user->id }}" name="name" value="{{ old('name', $user->name) }}" class="input" required />
                                                </div>
                                                <div>
                                                    <label for="user-email-{{ $user->id }}" class="block text-xs font-medium text-slate-600 mb-1.5">Email</label>
                                                    <input id="user-email-{{ $user->id }}" type="email" name="email" value="{{ old('email', $user->email) }}" class="input" required />
                                                </div>
                                                <div>
                                                    <label for="user-division-{{ $user->id }}" class="block text-xs font-medium text-slate-600 mb-1.5">Divisi</label>
                                                    <x-searchable-select id="user-division-{{ $user->id }}" name="division_id"
                                                        :options="$divisions->map(fn ($division) => ['value' => $division->id, 'label' => $division->name])->prepend(['value' => '', 'label' => 'Tanpa divisi'])"
                                                        :selected="old('division_id', $user->division_id) ?? ''"
                                                        placeholder="Tanpa divisi"
                                                        search-placeholder="Cari divisi"
                                                        class="mt-1" />
                                                </div>
                                                <div>
                                                    <label for="user-password-{{ $user->id }}" class="block text-xs font-medium text-slate-600 mb-1.5">Kata sandi baru</label>
                                                    <input id="user-password-{{ $user->id }}" type="password" name="password" class="input" placeholder="Kosongkan jika tidak berubah" autocomplete="new-password" />
                                                </div>
                                                <fieldset>
                                                    <legend class="text-sm font-semibold text-slate-700 mb-2.5">Peran</legend>
                                                    <div class="grid gap-2 sm:grid-cols-2">
                                                        @foreach ($roles as $role)
                                                            <label class="flex items-center gap-2.5 text-sm font-normal cursor-pointer">
                                                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" style="width: auto;" />
                                                                {{ $role->name }}
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </fieldset>
                                                <label class="flex items-center gap-2.5 text-sm font-normal">
                                                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) />
                                                    Akun aktif
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
                                            <p class="text-sm text-slate-500">Belum ada pengguna.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
                    <span class="text-xs text-slate-500">{{ $users->total() }} pengguna</span>
                    {{ $users->links() }}
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
