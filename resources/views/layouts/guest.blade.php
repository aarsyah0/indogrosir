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
        <aside class="auth-aside login-hero" data-parallax-scene>
            <div class="auth-aside-inner">
                <a href="/" class="auth-logo-panel" aria-label="Indogrosir ATK dan ABT">
                    <x-indogrosir-logo class="h-9 max-w-40 object-left" />
                    <span class="auth-logo-divider"></span>
                    <span class="auth-logo-caption"><strong>ATK &amp; ABT</strong><small>Operasional</small></span>
                </a>

                <div class="auth-aside-copy">
                    <span class="auth-brand-label">Sistem pengadaan internal</span>
                    <h2>Semua kebutuhan, dalam satu alur.</h2>
                    <p>Kelola pengajuan, persetujuan, pembelian, dan stok dengan lebih tertib.</p>
                </div>

                <div class="hero-scene" aria-hidden="true">
                    <div class="scene-light" data-scene-light></div>
                    <div class="scene-orbit scene-orbit-one"></div>
                    <div class="scene-orbit scene-orbit-two"></div>
                    <div class="scene-floor"></div>
                    <div class="scene-float" data-scene-object>
                        <div class="scene-cube">
                            <div class="cube-face cube-front">
                                <span class="cube-red-mark"></span>
                                <span class="cube-small-label">SUPPLY<br>CONTROL</span>
                                <span class="cube-lines"><i></i><i></i><i></i></span>
                            </div>
                            <div class="cube-face cube-back"></div>
                            <div class="cube-face cube-right"></div>
                            <div class="cube-face cube-left"></div>
                            <div class="cube-face cube-top"><span class="cube-top-mark"></span></div>
                            <div class="cube-face cube-bottom"></div>
                        </div>
                        <div class="scene-paper-stack"><i></i><i></i><i></i><i></i></div>
                        <div class="scene-base"></div>
                    </div>
                </div>

                <div class="hero-flow" aria-label="Alur aplikasi">
                    <span><b>01</b> Pengajuan</span><i></i>
                    <span><b>02</b> Persetujuan</span><i></i>
                    <span><b>03</b> Pengadaan</span>
                </div>
                <div class="hero-mobile-note">Pengajuan <span>·</span> Persetujuan <span>·</span> Pengadaan</div>
                <button type="button" class="motion-toggle" data-motion-toggle aria-label="Aktifkan gerakan perangkat">Gerakan perangkat: nonaktif</button>
                <p class="auth-aside-footer">PT Inti Cakrawala Citra <span aria-hidden="true">·</span> Sistem internal</p>
            </div>
        </aside>

        <section class="auth-content">
            <div class="auth-content-inner">
                <a href="/" class="auth-mobile-brand" aria-label="Kembali ke halaman awal">
                    <span class="auth-mobile-logo"><x-indogrosir-logo class="h-9 max-w-40 object-left" /></span>
                    <span class="auth-mobile-caption">ATK &amp; ABT</span>
                </a>
                <section class="auth-card page-enter">
                    {{ $slot }}
                </section>
                <p class="auth-content-footer">Gunakan akun kerja yang terdaftar untuk masuk.</p>
            </div>
        </section>
      </div>
    </main>
    <script>
        (() => {
            const scene = document.querySelector('[data-parallax-scene]');
            const object = document.querySelector('.scene-cube');
            const light = document.querySelector('[data-scene-light]');
            const motionButton = document.querySelector('[data-motion-toggle]');
            if (!scene || !object) return;

            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            let targetX = 0;
            let targetY = 0;
            let currentX = 0;
            let currentY = 0;

            scene.addEventListener('pointermove', (event) => {
                const rect = scene.getBoundingClientRect();
                targetX = ((event.clientX - rect.left) / rect.width - 0.5) * 2;
                targetY = ((event.clientY - rect.top) / rect.height - 0.5) * 2;
            });
            scene.addEventListener('pointerleave', () => { targetX = 0; targetY = 0; });

            const animate = () => {
                currentX += (targetX - currentX) * 0.055;
                currentY += (targetY - currentY) * 0.055;
                object.style.setProperty('--tilt-x', `${currentX * 12}deg`);
                object.style.setProperty('--tilt-y', `${currentY * -8}deg`);
                object.style.setProperty('--depth-x', `${currentX * 10}px`);
                object.style.setProperty('--depth-y', `${currentY * 8}px`);
                scene.style.setProperty('--depth-x', `${currentX * 10}px`);
                scene.style.setProperty('--depth-y', `${currentY * 8}px`);
                scene.style.setProperty('--orbit-x', `${currentX * -5}px`);
                scene.style.setProperty('--orbit-y', `${currentY * -4}px`);
                scene.style.perspectiveOrigin = `${50 + currentX * 3}% ${45 + currentY * 3}%`;
                if (light) light.style.transform = `translate3d(${currentX * 18}px, ${currentY * 12}px, 0)`;
                if (!reduceMotion) window.requestAnimationFrame(animate);
            };
            if (!reduceMotion) window.requestAnimationFrame(animate);

            if (motionButton) {
                if (reduceMotion) motionButton.hidden = true;
                motionButton.addEventListener('click', async () => {
                    const orientation = window.DeviceOrientationEvent;
                    if (!orientation) {
                        motionButton.textContent = 'Gerakan perangkat tidak tersedia';
                        return;
                    }
                    try {
                        if (typeof orientation.requestPermission === 'function'
                            && await orientation.requestPermission() !== 'granted') {
                            motionButton.textContent = 'Izin gerakan tidak diberikan';
                            return;
                        }
                        window.addEventListener('deviceorientation', (event) => {
                            targetX = Math.max(-1, Math.min(1, (event.gamma || 0) / 35));
                            targetY = Math.max(-1, Math.min(1, ((event.beta || 0) - 35) / 45));
                        });
                        motionButton.textContent = 'Gerakan perangkat: aktif';
                    } catch {
                        motionButton.textContent = 'Gerakan perangkat tidak tersedia';
                    }
                });
            }
        })();
    </script>
</body>

</html>
