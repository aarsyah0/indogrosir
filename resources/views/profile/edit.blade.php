<x-app-layout title="Profil">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full min-w-0 sm:flex-1">
            <x-page-header
                header="Profil"
                subheader="Update informasi akun dan pengaturan keamanan."
                class="mb-4"
            >
                <a href="{{ route('profile.edit') }}" class="btn-secondary">Batal</a>
            </x-page-header>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <!-- Profile information -->
        <section class="card">
            <div class="card-body">
                <h2 class="section-heading">Informasi profil</h2>
                <p class="text-sm text-slate-500 mt-0.5">Update nama dan alamat email akun Anda.</p>
                <form method="post" action="{{ route('profile.update') }}" class="mt-3 space-y-4">
                    @csrf
                    @method('patch')

                    <div>
                        <x-input-label for="name" value="Nama" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <p class="text-sm mt-2 text-slate-500">
                                {{ __('Email belum terverifikasi.') }}
                                <button form="send-verification" class="underline text-sm text-brand font-medium transition-colors hover:text-brand-600">
                                    Klik disini untuk mengirim ulang email verifikasi.
                                </button>
                            </p>
                            @if (session('status') === 'verification-link-sent')
                                <p class="text-sm font-medium text-emerald-700 mt-1">
                                    {{ __('Tautan verifikasi baru telah dikirim.') }}
                                </p>
                            @endif
                        @endif
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>Simpan perubahan</x-primary-button>
                        @if (session('status') === 'profile-updated')
                            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-slate-600">
                                {{ __('Tersimpan.') }}
                            </p>
                        @endif
                    </div>
                </form>
            </div>
        </section>

        <!-- Password & account -->
        <div class="grid gap-4">
            <section class="card">
                <div class="card-body">
                    <h2 class="section-heading">Kata sandi</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Pastikan akun Anda menggunakan kata sandi yang kuat.</p>
                    <form method="post" action="{{ route('password.update') }}" class="mt-3 space-y-4">
                        @csrf
                        @method('put')

                        <div>
                            <x-input-label for="update_password_current_password" value="Kata sandi saat ini" />
                            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="update_password_password" value="Kata sandi baru" />
                            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="update_password_password_confirmation" value="Konfirmasi kata sandi" />
                            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>Simpan kata sandi</x-primary-button>
                            @if (session('status') === 'password-updated')
                                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-slate-600">
                                    {{ __('Disimpan.') }}
                                </p>
                            @endif
                        </div>
                    </form>
                </div>
            </section>

            <section class="card">
                <div class="card-body">
                    <h2 class="section-heading">Hapus akun</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Setelah dihapus, semua data akan hilang secara permanen.</p>
                    <form method="post" action="{{ route('profile.destroy') }}" class="mt-3 space-y-4">
                        @csrf
                        @method('delete')

                        <div>
                            <x-input-label for="password" value="Kata sandi untuk konfirmasi" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-danger-button>Hapus akun</x-danger-button>
                            <p class="text-xs text-slate-500">Tindakan ini tidak dapat dibatalkan.</p>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
