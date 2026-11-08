<x-guest-layout>
    <div class="auth-form-heading">
        <span class="auth-form-kicker">Akses pengguna</span>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Masuk ke sistem</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Gunakan akun kerja Anda untuk melanjutkan.</p>
    </div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="auth-login-form">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Kata sandi" />

            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="auth-login-options">
            <label for="remember_me" class="inline-flex min-h-11 cursor-pointer items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded border-slate-300 text-brand focus:ring-brand-500" name="remember">
                <span class="ms-2 text-sm text-slate-600">Ingat saya</span>
            </label>
        </div>

        <div class="auth-login-submit">
            @if (Route::has('password.request'))
                <a class="rounded-md text-sm font-semibold text-brand transition-colors hover:text-brand-700 hover:underline"
                    href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif

            <x-primary-button class="auth-login-button">
                Masuk
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
