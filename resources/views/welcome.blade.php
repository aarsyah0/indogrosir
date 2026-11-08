<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Indogrosir — Pengelolaan ATK &amp; ABT</title>
    <link rel="icon" type="image/png" href="https://www.indogrosir.co.id/images/logo/logo_2feb2026.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 text-slate-800 antialiased">
    <div class="min-h-screen">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <x-indogrosir-logo class="h-10 max-w-44 object-left" />
                    <div>
                        <p class="text-xs text-slate-500">Pengelolaan ATK &amp; ABT</p>
                    </div>
                </div>
                <nav aria-label="Akun" class="flex items-center gap-2">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-primary">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-secondary">
                            Masuk
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-primary">
                                Daftar
                            </a>
                        @endif
                    @endauth
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
            <section class="grid items-center gap-8 lg:grid-cols-2 lg:gap-12">
                <div>
                    <span class="inline-flex rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-800">
                        Operasional internal · PT Inti Cakrawala Citra
                    </span>
                    <h2 class="mt-5 max-w-2xl text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                        Kebutuhan operasional tercatat, pengadaan lebih terkendali.
                    </h2>
                    <p class="mt-4 max-w-xl text-base leading-7 text-slate-600">
                        Satu alur kerja untuk pengajuan, persetujuan, pembelian, penerimaan, persediaan, dan pelaporan
                        kebutuhan ATK &amp; ABT.
                    </p>
                    <div class="mt-7 flex flex-wrap gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn-primary">
                                Lihat Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn-primary">
                                Masuk ke Sistem
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn-secondary">
                                    Buat Akun
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-5 flex items-center justify-between gap-3 border-b border-slate-200 pb-4">
                        <div>
                            <p class="text-xs font-semibold tracking-wide text-brand">SIKLUS OPERASIONAL</p>
                            <h3 class="mt-1 text-base font-semibold text-slate-900">Alur pengelolaan</h3>
                        </div>
                        <span
                            class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">Terintegrasi</span>
                    </div>
                    <ol class="divide-y divide-slate-100">
                        <li class="flex items-center gap-4 py-3 first:pt-0">
                            <span
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-50 text-xs font-semibold text-red-800">01</span>
                            <span class="text-sm font-medium text-slate-700">Pengajuan dan persetujuan berjenjang</span>
                        </li>
                        <li class="flex items-center gap-4 py-3">
                            <span
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-50 text-xs font-semibold text-red-800">02</span>
                            <span class="text-sm font-medium text-slate-700">Verifikasi GA dan pencatatan
                                pembelian</span>
                        </li>
                        <li class="flex items-center gap-4 py-3">
                            <span
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-50 text-xs font-semibold text-red-800">03</span>
                            <span class="text-sm font-medium text-slate-700">Penerimaan barang dan pembaruan stok</span>
                        </li>
                        <li class="flex items-center gap-4 py-3 last:pb-0">
                            <span
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-50 text-xs font-semibold text-red-800">04</span>
                            <span class="text-sm font-medium text-slate-700">Monitoring, laporan, dan audit trail</span>
                    </ol>
                </div>
            </section>
        </main>
    </div>
</body>

</html>
