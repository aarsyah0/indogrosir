<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Indogrosir | ATK/ABT') }}</title>
    <link rel="icon" type="image/png" href="https://www.indogrosir.co.id/images/logo/logo_2feb2026.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="auth-page font-sans antialiased">
    <div class="auth-backdrop-glow auth-backdrop-glow-one" aria-hidden="true"></div>
    <div class="auth-backdrop-glow auth-backdrop-glow-two" aria-hidden="true"></div>
    <main class="auth-shell">
      <div class="auth-frame">
        <aside aria-hidden="true" class="auth-aside">
            <div class="auth-orbit auth-orbit-one"></div>
            <div class="auth-orbit auth-orbit-two"></div>
            <div class="auth-aside-inner">
                <div class="auth-logo-panel">
                    <x-indogrosir-logo class="h-10 max-w-44 object-left" />
                    <span class="auth-logo-divider"></span>
                    <span class="auth-logo-caption"><strong>ATK &amp; ABT</strong><small>Portal operasional</small></span>
                </div>
                <div class="auth-aside-copy">
                    <span class="auth-brand-label">Pengelolaan ATK &amp; ABT</span>
                    <h2>Alur operasional yang lebih tertata.</h2>
                    <p>Sistem internal untuk mengelola kebutuhan, persetujuan, pengadaan, dan persediaan.</p>
                    <div class="auth-flow-list">
                        <div class="auth-flow-item">
                            <span class="auth-flow-icon"><x-context-icon name="clipboard" tone="red" /></span>
                            <span><strong>Pengajuan</strong><small>Kebutuhan tercatat</small></span>
                            <span class="auth-flow-number">01</span>
                        </div>
                        <div class="auth-flow-item">
                            <span class="auth-flow-icon"><x-context-icon name="check" tone="gold" /></span>
                            <span><strong>Persetujuan</strong><small>Proses terarah</small></span>
                            <span class="auth-flow-number">02</span>
                        </div>
                        <div class="auth-flow-item">
                            <span class="auth-flow-icon"><x-context-icon name="package-check" tone="green" /></span>
                            <span><strong>Pengadaan &amp; stok</strong><small>Pencatatan terpadu</small></span>
                            <span class="auth-flow-number">03</span>
                        </div>
                    </div>
                </div>
                <p class="auth-aside-footer">PT Inti Cakrawala Citra <span aria-hidden="true">&middot;</span> Sistem internal</p>
            </div>
        </aside>

        <section class="auth-content">
            <div class="auth-content-inner">
                <a href="/" class="auth-mobile-brand" aria-label="Kembali ke halaman awal">
                    <span class="auth-mobile-logo"><x-indogrosir-logo class="h-9 max-w-40 object-left" /></span>
                    <span class="auth-mobile-caption">Pengelolaan ATK &amp; ABT</span>
                </a>
                <section class="auth-card page-enter">
                    {{ $slot }}
                </section>
                <p class="auth-content-footer">Akses hanya untuk pengguna yang memiliki akun kerja.</p>
            </div>
        </section>
      </div>
    </main>
</body>

</html>
