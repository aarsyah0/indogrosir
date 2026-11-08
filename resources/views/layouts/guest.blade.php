<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Indogrosir | ATK/ABT') }}</title>
    <link rel="icon" type="image/png" href="https://www.indogrosir.co.id/images/logo/logo_2feb2026.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <main class="grid min-h-screen bg-canvas lg:grid-cols-[minmax(0,1fr)_minmax(28rem,42rem)]">
        <aside aria-hidden="true" class="relative hidden min-h-screen overflow-hidden bg-navy-700 px-10 py-12 text-white lg:flex lg:flex-col lg:justify-between xl:px-16">
            <div class="absolute -right-28 -top-24 h-96 w-96 rounded-full bg-brand-500/90"></div>
            <div class="absolute -bottom-44 -left-32 h-[30rem] w-[30rem] rounded-full border-[3rem] border-accent-500/90"></div>
            <div class="relative z-10 inline-flex w-fit items-center rounded-xl bg-white px-4 py-3 shadow-lg">
                <x-indogrosir-logo class="h-10 max-w-48 object-left" />
            </div>
            <div class="relative z-10 max-w-lg">
                <p class="text-sm font-semibold uppercase tracking-[.16em] text-white/75">Pengelolaan ATK &amp; ABT</p>
                <div class="mt-6 h-1 w-16 rounded-full bg-accent-500"></div>
            </div>
            <p class="relative z-10 text-sm text-white/70">Sistem internal pengelolaan kebutuhan operasional</p>
        </aside>

        <section class="flex min-h-screen items-center justify-center px-4 py-8 sm:px-8 lg:px-10 xl:px-16">
            <div class="w-full max-w-md page-enter">
                <a href="/" class="mb-6 flex items-center justify-center gap-3 lg:hidden">
                    <x-indogrosir-logo class="h-9 max-w-40 object-left" />
                    <span class="text-left">
                        <span class="block text-xs text-slate-500">Pengelolaan ATK &amp; ABT</span>
                    </span>
                </a>
                <section class="rounded-2xl border border-border bg-white p-6 shadow-lg shadow-slate-900/5 sm:p-9">
                    {{ $slot }}
                </section>
                <p class="mt-5 text-center text-xs text-slate-400 lg:hidden">Sistem internal pengelolaan kebutuhan operasional</p>
            </div>
        </section>
    </main>
</body>

</html>
