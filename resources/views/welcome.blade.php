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

<body class="welcome-page antialiased">
    <header class="welcome-header">
        <div class="welcome-header-inner">
            <a href="/" class="welcome-brand" aria-label="Indogrosir — halaman awal">
                <x-indogrosir-logo class="h-10 max-w-44 object-left" />
                <span class="welcome-brand-caption">Pengelolaan ATK &amp; ABT</span>
            </a>
            <nav aria-label="Akun" class="welcome-nav">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-primary">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-secondary">Masuk</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-primary">Daftar</a>
                    @endif
                @endauth
            </nav>
        </div>
    </header>

    <main class="welcome-main">
        <section class="welcome-hero">
            <div class="welcome-copy">
                <span class="welcome-eyebrow"><span></span>Operasional internal · PT Inti Cakrawala Citra</span>
                <h1>Kebutuhan operasional tercatat, <span>pengadaan lebih terkendali.</span></h1>
                <p class="welcome-lead">Satu alur kerja untuk pengajuan, persetujuan, pembelian, penerimaan, persediaan, dan pelaporan kebutuhan ATK &amp; ABT.</p>
                <div class="welcome-actions">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-primary">Lihat Dashboard <span aria-hidden="true">&rarr;</span></a>
                    @else
                        <a href="{{ route('login') }}" class="btn-primary">Masuk ke Sistem <span aria-hidden="true">&rarr;</span></a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-secondary">Buat Akun</a>
                        @endif
                    @endauth
                </div>
                <p class="welcome-footnote"><span aria-hidden="true"></span>Lingkungan kerja internal Indogrosir</p>
            </div>

            <section class="welcome-flow-card" aria-labelledby="welcome-flow-title">
                <div class="welcome-flow-heading">
                    <div>
                        <p class="welcome-card-eyebrow">SIKLUS OPERASIONAL</p>
                        <h2 id="welcome-flow-title">Alur pengelolaan</h2>
                    </div>
                    <span class="welcome-integrated"><span></span>Terintegrasi</span>
                </div>
                <ol class="welcome-flow-list">
                    <li>
                        <x-context-icon name="clipboard" tone="red" class="welcome-flow-icon" />
                        <span class="welcome-step-number">01</span>
                        <span class="welcome-flow-label">Pengajuan dan persetujuan berjenjang</span>
                        <span class="welcome-flow-arrow" aria-hidden="true">&rarr;</span>
                    </li>
                    <li>
                        <x-context-icon name="check" tone="blue" class="welcome-flow-icon" />
                        <span class="welcome-step-number">02</span>
                        <span class="welcome-flow-label">Verifikasi GA dan pencatatan pembelian</span>
                        <span class="welcome-flow-arrow" aria-hidden="true">&rarr;</span>
                    </li>
                    <li>
                        <x-context-icon name="package-check" tone="green" class="welcome-flow-icon" />
                        <span class="welcome-step-number">03</span>
                        <span class="welcome-flow-label">Penerimaan barang dan pembaruan stok</span>
                        <span class="welcome-flow-arrow" aria-hidden="true">&rarr;</span>
                    </li>
                    <li>
                        <x-context-icon name="chart" tone="gold" class="welcome-flow-icon" />
                        <span class="welcome-step-number">04</span>
                        <span class="welcome-flow-label">Monitoring, laporan, dan audit trail</span>
                        <span class="welcome-flow-arrow" aria-hidden="true">&rarr;</span>
                    </li>
                </ol>
                <div class="welcome-flow-footer">
                    <span class="welcome-flow-footer-mark" aria-hidden="true"></span>
                    <span>Dari kebutuhan hingga persediaan</span>
                </div>
            </section>
        </section>
    </main>
</body>

</html>
