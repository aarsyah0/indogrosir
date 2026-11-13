<x-guest-layout>
    <div class="auth-form-heading">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Masuk ke akun Anda</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Gunakan email dan kata sandi akun kerja Anda.</p>
    </div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="auth-login-form">
        @csrf

        <!-- Email Address -->
        <div class="auth-floating-field">
            <svg class="auth-field-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="m4 7 8 6 8-6"/></svg>
            <input id="email" class="auth-floating-input peer" type="email" name="email" value="{{ old('email') }}" required
                autofocus autocomplete="username" placeholder=" " />
            <label for="email" class="auth-floating-label">Email kerja</label>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="auth-floating-field">
            <svg class="auth-field-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="11" rx="2.5"/><path d="M8 10V7a4 4 0 1 1 8 0v3"/></svg>
            <input id="password" class="auth-floating-input peer" type="password" name="password" required
                autocomplete="current-password" placeholder=" " />
            <label for="password" class="auth-floating-label">Kata sandi</label>
            <button type="button" class="auth-password-toggle" aria-label="Tampilkan kata sandi" aria-pressed="false" data-password-toggle>
                <svg class="auth-eye-open" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                <svg class="auth-eye-closed" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m3 3 18 18M10.6 6.2A10.8 10.8 0 0 1 12 6c6.1 0 9.5 6 9.5 6a15.6 15.6 0 0 1-3.1 3.7M6.2 6.3C3.8 8 2.5 12 2.5 12s3.4 6 9.5 6c1.3 0 2.4-.3 3.4-.7"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
            </button>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="auth-login-options">
            <label for="remember_me" class="inline-flex min-h-11 cursor-pointer items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded border-slate-300 text-brand focus:ring-brand-500" name="remember">
                <span class="ms-2 text-sm text-slate-600">Ingat saya</span>
            </label>
            @if (Route::has('password.request'))
                <a class="auth-forgot-link" href="{{ route('password.request') }}">Lupa kata sandi?</a>
            @endif
        </div>

        <div class="auth-login-submit">
            <x-primary-button class="auth-login-button">
                Masuk
            </x-primary-button>
        </div>
    </form>

    <script>
        document.querySelector('.auth-login-button')?.addEventListener('click', (event) => {
            const button = event.currentTarget;
            button.classList.remove('is-springing');
            void button.offsetWidth;
            button.classList.add('is-springing');
            window.setTimeout(() => button.classList.remove('is-springing'), 420);
        });

        document.querySelector('[data-password-toggle]')?.addEventListener('click', (event) => {
            const toggle = event.currentTarget;
            const password = document.querySelector('#password');
            const isVisible = password.type === 'text';
            password.type = isVisible ? 'password' : 'text';
            toggle.setAttribute('aria-pressed', String(!isVisible));
            toggle.setAttribute('aria-label', isVisible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
        });
    </script>
</x-guest-layout>
