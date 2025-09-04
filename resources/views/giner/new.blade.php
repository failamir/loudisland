<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mandalika KORPRI Fun Night Run 2025</title>

    <link rel="icon" type="image/png" href="{{ asset('giner-assets/img/favicon-new.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Russo+One&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue-900: #071a6d;
            --blue-800: #0c2a9a;
            --blue-700: #143abf;
            --neon: #42ffa9;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
        }

        body {
            margin: 0;
            color: var(--white);
            background: radial-gradient(1200px 600px at 70% 10%, #0b2f9f 0%, #0a2b93 35%, #071e72 75%, #061a64 100%);
            font-family: 'Montserrat', system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            overflow-x: hidden;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .container {
            width: min(1200px, 92%);
            margin: 0 auto;
        }

        /* Header */
        .site-header {
            position: relative;
            z-index: 10;
            padding: 18px 0;
        }

        .nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand img {
            height: 34px;
            width: auto;
            display: block;
        }

        .nav-links {
            display: flex;
            gap: 28px;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: .04em;
            opacity: .95;
        }

        .nav-links a {
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: -8px;
            height: 2px;
            background: transparent;
            transition: background .2s ease;
        }

        .nav-links a:hover::after {
            background: var(--neon);
        }

        /* Hero */
        .hero {
            position: relative;
            padding: 28px 0 0;
            min-height: 100vh;
            /* isi viewport agar divider bisa nempel di bawah layar */
            overflow: hidden;
        }

        .hero-inner {
            position: static;
            display: grid;
            grid-template-columns: 1fr;
            justify-items: center;
            margin-bottom: -20%;
            /* kurangi ruang bawah agar pas ke layar */
        }

        /* Framed banner */
        .frame {
            position: relative;
            width: min(1200px, 100%);
            margin-top: 0;
        }

        .frame::before {
            display: none !important;
        }

        .frame-content {
            position: relative;
            z-index: 2;
            border-radius: 0;
            padding: 0;
            background: transparent;
            overflow: hidden;
        }

        /* Runners image fills and bleeds outside for a bigger look */
        .runners {
            position: relative;
            perspective: 600px;
            /* pastikan kontainer punya tinggi agar layout tidak collapse */
            height: clamp(220px, 36vw, 540px);
            width: 111%;
            margin-left: -5%;
            /* tarik konten berikutnya (counter) sedikit lebih dekat */
            margin-bottom: clamp(-12px, -2.2vw, -26px);
            pointer-events: none;
            opacity: .98;
        }

        .runners img {
            position: relative;
            z-index: 1;
            width: 100%;
            height: auto;
            display: block;
            filter: none;
            /* If you want a touch more pop without losing green: saturate(1.2) contrast(1.05) */
        }

        /* Speed lines overlay (subtle) */
        .runners .speedlines {
            position: absolute;
            inset: 0;
            z-index: 0;
            background: repeating-linear-gradient(-20deg,
                    rgba(66, 255, 169, 0.18) 0 2px,
                    rgba(66, 255, 169, 0.00) 2px 11px);
            opacity: .26;
            mix-blend-mode: screen;
            animation: linesSlide 4.5s linear infinite;
        }

        @keyframes linesSlide {
            from {
                background-position: 0 0;
            }

            to {
                background-position: 160px 0;
            }
        }

        /* Light sweep overlay */
        .runners .sweep {
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
            background: linear-gradient(75deg,
                    rgba(255, 255, 255, 0) 0%,
                    rgba(255, 255, 255, 0.0) 35%,
                    rgba(255, 255, 255, 0.16) 50%,
                    rgba(255, 255, 255, 0.0) 65%,
                    rgba(255, 255, 255, 0) 100%);
            transform: translateX(-150%);
            animation: sweep 5.5s linear infinite;
            opacity: .5;
            mix-blend-mode: screen;
        }

        @keyframes sweep {
            from {
                transform: translateX(-150%);
            }

            to {
                transform: translateX(150%);
            }
        }

        /* Hormati prefers-reduced-motion: matikan animasi dekoratif */
        @media (prefers-reduced-motion: reduce) {

            .runners .sweep,
            .runners .speedlines {
                animation: none !important;
                opacity: 0 !important;
            }
        }

        /* Countdown */
        .countdown {
            /* perkecil jarak atas agar lebih dekat ke gambar */
            margin: clamp(6px, 1.6vw, 16px) auto 18px;
            display: grid;
            grid-auto-flow: column;
            gap: clamp(10px, 3vw, 28px);
            align-items: end;
            justify-content: center;
        }

        .cd-item {
            text-align: center;
            min-width: 64px;
        }

        .cd-value {
            font-family: 'Russo One', sans-serif;
            font-size: clamp(32px, 7.2vw, 64px);
            line-height: 1.1;
            letter-spacing: .04em;
            color: var(--neon);
            /* hijau */
            text-shadow: 0 2px 0 rgba(0, 0, 0, .18);
        }

        .cd-label {
            font-size: clamp(10px, 2.3vw, 14px);
            opacity: .9;
            margin-top: 4px;
            text-transform: capitalize;
        }

        /* Register button */
        .cta {
            display: flex;
            justify-content: center;
            margin: 18px 0 28px;
        }

        .btn-primary {
            background: #fff;
            color: #0c2a9a;
            border: 0;
            border-radius: 6px;
            padding: 14px 34px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .18);
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, .22);
        }

        /* Footer stripe / shape hint */
        .hero-shape {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 6%;
            /* naikkan sedikit agar tidak perlu scroll */
            /* kompensasi pixel transparan agar terlihat nempel */
            width: 100%;
            /* full-bleed tanpa memicu scroll horizontal */
            height: auto;
            max-height: 88px;
            display: block;
            pointer-events: none;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .nav-links {
                display: none;
            }

            .runners {
                width: 130%;
                margin: 0 0 0 -15%;
            }

            .frame::before {
                transform: none;
                inset: -12px;
                border-width: 12px;
                border-radius: 26px;
            }

            .cta {
                margin: 16px 0 22px;
            }
        }

        @media (max-width: 640px) {
            .brand img {
                height: 28px;
            }

            .btn-primary {
                padding: 12px 26px;
                font-size: 13px;
            }

            .frame {
                width: 94%;
            }

            .frame-content {
                padding: 18px;
            }

            /* offset sedikit lebih kecil di layar kecil */
            .hero-shape {
                bottom: 0px;
                left: 0;
                right: 0;
                width: 100%;
            }

            .hero-inner {
                margin-bottom: 72px;
            }
        }
    </style>
</head>

<body style="background-image: url({{ asset('giner-assets/img/bg.png') }}); background-size: cover;">
    <!-- Header -->
    <header class="site-header">
        <div class="container nav">
            <a class="brand" href="{{ url('/') }}" aria-label="Mandalika KORPRI Fun Night Run">
                <img src="{{ asset('giner-assets/img/logo.png') }}" alt="Logo">
            </a>
            <nav class="nav-links">
                <a href="#home">Home</a>
                <a href="#about">About Us</a>
                <a href="#schedule">Schedule</a>
                <a href="#location">Location</a>
                <a href="#register">Register</a>
                <a href="#news">News</a>
            </nav>
        </div>
    </header>

    <!-- Hero -->
    <main class="hero" id="home">
        <div class="container hero-inner">
            <div class="frame">
                <div class="frame-content">
                    <div class="runners" id="runners" style="will-change: transform; transition: transform .12s ease-out;">
                        <div class="speedlines" aria-hidden="true"></div>
                        <!-- <img src="{{ asset('giner-assets/img/fg.png') }}" alt="Runners"> -->
                        <img src="{{ asset('giner-assets/img/fg.webp') }}" alt="Runners">
                        <div class="sweep" aria-hidden="true"></div>
                    </div>
                </div>
            </div>

            <!-- Countdown -->
            <div class="countdown" aria-label="Countdown to event">
                <div class="cd-item">
                    <div id="cd-days" class="cd-value">--</div>
                    <div class="cd-label">Days</div>
                </div>
                <div class="cd-item">
                    <div id="cd-hours" class="cd-value">--</div>
                    <div class="cd-label">Hours</div>
                </div>
                <div class="cd-item">
                    <div id="cd-mins" class="cd-value">--</div>
                    <div class="cd-label">Minutes</div>
                </div>
                <div class="cd-item">
                    <div id="cd-secs" class="cd-value">--</div>
                    <div class="cd-label">Seconds</div>
                </div>
            </div>

            <!-- CTA -->
            <div class="cta" id="register">
                @guest
                <a href="https://daftar.mandalikakorprirun.com/" class="btn-primary">Register</a>
                @else
                <a href="{{ url('/') }}" class="btn-primary">Dashboard</a>
                @endguest
            </div>
        </div>
        <img class="hero-shape" src="{{ asset('giner-assets/img/divider.png') }}" alt="divider" />
    </main>

    <script>
        (function() {
            const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const hero = document.querySelector('.hero');
            const runners = document.getElementById('runners');
            if (!hero || !runners) return;
            if (prefersReduced) return; // hormati setting reduce motion

            // Nonaktifkan di perangkat sentuh (hemat dan hindari jitter)
            const hasTouch = 'ontouchstart' in window || (navigator.maxTouchPoints || 0) > 0;
            if (hasTouch) return;

            // Strength & smoothing
            const T = 22; // px translate (lebih kuat)
            const R = 5; // deg tilt (sedikit lebih tinggi)
            const damping = 0.16; // respon sedikit lebih cepat
            const idleAmp = 0.022; // amplitudo breathing (scale)
            const idlePeriod = 2200; // ms per siklus nafas (lebih cepat)

            let targetX = 0,
                targetY = 0; // -0.5..0.5
            let x = 0,
                y = 0;
            let rafId = null;
            let hovering = false;

            function apply(nx, ny) {
                const tx = nx * T;
                const ty = ny * T;
                const rx = -ny * R;
                const ry = nx * R;
                // idle breathing scale saat tidak hover
                const now = performance.now();
                const scale = hovering ? 1 : 1 + Math.sin((now % idlePeriod) / idlePeriod * Math.PI * 2) * idleAmp;
                runners.style.transform = `translate3d(${tx}px, ${ty}px, 0) rotateX(${rx}deg) rotateY(${ry}deg) scale(${scale})`;
            }

            function loop() {
                x += (targetX - x) * damping;
                y += (targetY - y) * damping;
                apply(x, y);
                if (!hovering && Math.abs(x) < 0.002 && Math.abs(y) < 0.002) {
                    x = y = 0;
                    apply(0, 0);
                    rafId = null;
                    return;
                }
                rafId = requestAnimationFrame(loop);
            }

            function start() {
                if (rafId == null) rafId = requestAnimationFrame(loop);
            }

            function onMove(e) {
                const rect = hero.getBoundingClientRect();
                targetX = (e.clientX - rect.left) / rect.width - 0.5;
                targetY = (e.clientY - rect.top) / rect.height - 0.5;
                start();
            }

            function onEnter() {
                hovering = true;
                start();
            }

            function onLeave() {
                hovering = false;
                targetX = 0;
                targetY = 0;
                start();
            }

            document.addEventListener('visibilitychange', () => {
                if (document.hidden && rafId) {
                    cancelAnimationFrame(rafId);
                    rafId = null;
                } else if (!document.hidden && (hovering || Math.abs(x) > 0.002 || Math.abs(y) > 0.002)) {
                    start();
                }
            }, {
                passive: true
            });

            hero.addEventListener('mouseenter', onEnter, {
                passive: true
            });
            hero.addEventListener('mousemove', onMove, {
                passive: true
            });
            hero.addEventListener('mouseleave', onLeave, {
                passive: true
            });
        })();
    </script>

    <script>
        (function() {
            const target = new Date('2025-12-06T00:00:00+08:00').getTime();
            const $d = document.getElementById('cd-days');
            const $h = document.getElementById('cd-hours');
            const $m = document.getElementById('cd-mins');
            const $s = document.getElementById('cd-secs');

            function pad(n) {
                return String(n).padStart(2, '0');
            }

            function tick() {
                const now = Date.now();
                let diff = Math.max(0, target - now);
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                diff -= days * 86400000;
                const hours = Math.floor(diff / (1000 * 60 * 60));
                diff -= hours * 3600000;
                const mins = Math.floor(diff / (1000 * 60));
                diff -= mins * 60000;
                const secs = Math.floor(diff / 1000);
                $d.textContent = days;
                $h.textContent = pad(hours);
                $m.textContent = pad(mins);
                $s.textContent = pad(secs);
            }
            tick();
            setInterval(tick, 1000);
        })();
    </script>

</body>

</html>