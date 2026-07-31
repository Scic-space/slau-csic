<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'Cybersecurity & Innovations Club - SLAU' }}</title>
    <meta name="description" content="Cybersecurity and Innovations Club at St. Lawrence University, Uganda - A student-led community for practical cybersecurity learning, responsible innovation, and visible campus participation.">

    @vite(['resources/css/app.css', 'resources/js/app.tsx', 'resources/js/app.js'])

    <script>
        (function () {
            const savedTheme = localStorage.getItem('slau-theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.dataset.theme = savedTheme || systemTheme;
        })();
    </script>

    <style>
        :root {
            --page-bg: #07101d;
            --page-bg-soft: #0b1628;
            --page-surface: rgba(11, 22, 40, 0.86);
            --page-surface-strong: #0d1b2f;
            --page-surface-elevated: rgba(13, 27, 47, 0.92);
            --page-border: rgba(124, 145, 170, 0.2);
            --page-border-strong: rgba(255, 255, 255, 0.12);
            --page-text: #f8fafc;
            --page-text-soft: #cbd5e1;
            --page-text-muted: #94a3b8;
            --page-text-dim: #64748b;
            --page-primary: #19e28f;
            --page-secondary: #34b6ff;
            --page-warning: #fbbf24;
            --header-bg: rgba(7, 16, 29, 0.72);
            --grid-line: rgba(148, 163, 184, 0.06);
            --hero-overlay-left: rgba(7, 16, 29, 0.82);
            --hero-overlay-right: rgba(7, 16, 29, 0.68);
            --glow-primary: rgba(25, 226, 143, 0.14);
            --glow-secondary: rgba(52, 182, 255, 0.12);
            --quote-bg:
                radial-gradient(circle at top right, rgba(52, 182, 255, 0.1), transparent 32%),
                linear-gradient(180deg, rgba(12, 26, 45, 0.96) 0%, rgba(7, 16, 29, 0.98) 100%);
        }

        html[data-theme='light'] {
            --page-bg: #eef4fb;
            --page-bg-soft: #f6f9fd;
            --page-surface: rgba(255, 255, 255, 0.92);
            --page-surface-strong: #ffffff;
            --page-surface-elevated: rgba(255, 255, 255, 0.96);
            --page-border: rgba(15, 23, 42, 0.1);
            --page-border-strong: rgba(15, 23, 42, 0.14);
            --page-text: #0f172a;
            --page-text-soft: #334155;
            --page-text-muted: #64748b;
            --page-text-dim: #94a3b8;
            --header-bg: rgba(255, 255, 255, 0.84);
            --grid-line: rgba(15, 23, 42, 0.06);
            --hero-overlay-left: rgba(238, 244, 251, 0.9);
            --hero-overlay-right: rgba(238, 244, 251, 0.75);
            --glow-primary: rgba(25, 226, 143, 0.09);
            --glow-secondary: rgba(52, 182, 255, 0.08);
            --quote-bg:
                radial-gradient(circle at top right, rgba(52, 182, 255, 0.08), transparent 32%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(242, 246, 252, 1) 100%);
        }

        body {
            background:
                radial-gradient(circle at top left, var(--glow-secondary), transparent 24%),
                radial-gradient(circle at bottom right, var(--glow-primary), transparent 18%),
                linear-gradient(180deg, var(--page-bg) 0%, var(--page-bg-soft) 48%, var(--page-bg) 100%);
            color: var(--page-text);
            transition: background 260ms ease, color 260ms ease;
        }

        .page-shell {
            position: relative;
            overflow-x: clip;
            color: var(--page-text);
        }

        .page-shell::before,
        .page-shell::after {
            content: '';
            position: fixed;
            z-index: 0;
            border-radius: 9999px;
            pointer-events: none;
            filter: blur(22px);
            opacity: 0.7;
        }

        .page-shell::before {
            left: -6rem;
            top: 7rem;
            height: 16rem;
            width: 16rem;
            background: radial-gradient(circle, rgba(52, 182, 255, 0.2) 0%, rgba(52, 182, 255, 0.04) 48%, transparent 76%);
        }

        .page-shell::after {
            right: -7rem;
            bottom: 4rem;
            height: 18rem;
            width: 18rem;
            background: radial-gradient(circle, rgba(25, 226, 143, 0.18) 0%, rgba(25, 226, 143, 0.04) 48%, transparent 78%);
        }

        .site-header-shell {
            border: 1px solid var(--page-border-strong);
            background: var(--header-bg);
            backdrop-filter: blur(18px);
            box-shadow: 0 18px 40px rgba(2, 8, 23, 0.16);
        }

        .theme-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            border: 1px solid var(--page-border);
            background: color-mix(in srgb, var(--page-surface) 84%, transparent);
            color: var(--page-text-soft);
            border-radius: 9999px;
            padding: 0.6rem 0.85rem;
            transition: all 180ms ease;
        }

        .theme-toggle:hover {
            color: var(--page-text);
            border-color: color-mix(in srgb, var(--page-primary) 35%, var(--page-border));
        }

        .theme-toggle-indicator {
            display: inline-flex;
            height: 1.9rem;
            width: 1.9rem;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            background: color-mix(in srgb, var(--page-primary) 16%, var(--page-surface-strong));
            color: var(--page-text);
        }

        .nav-link {
            position: relative;
            color: var(--page-text-soft);
            transition: color 0.25s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -4px;
            width: 0;
            height: 2px;
            background: var(--page-primary);
            transition: width 0.24s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--page-text);
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }

        .eyebrow {
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: color-mix(in srgb, var(--page-primary) 70%, var(--page-secondary));
        }

        .section-title {
            font-size: clamp(2rem, 3vw, 3rem);
            font-weight: 700;
            line-height: 1.02;
            letter-spacing: -0.04em;
            color: var(--page-text);
        }

        .lead-copy,
        .body-copy {
            color: var(--page-text-soft);
            line-height: 1.95;
        }

        .lead-copy {
            font-size: 1.05rem;
        }

        .body-copy {
            font-size: 0.96rem;
        }

        .cyber-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: var(--page-primary);
            color: #06111b;
            font-weight: 700;
            padding: 0.85rem 1.5rem;
            border-radius: 0.95rem;
            border: 1px solid color-mix(in srgb, var(--page-primary) 68%, #ffffff 10%);
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .cyber-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 32px color-mix(in srgb, var(--page-primary) 24%, transparent);
            background: color-mix(in srgb, var(--page-primary) 92%, white);
        }

        .cyber-outline-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0.95rem;
            border: 1px solid var(--page-border);
            background: color-mix(in srgb, var(--page-surface) 90%, transparent);
            color: var(--page-text);
            padding: 0.82rem 1rem;
            transition: all 0.2s ease;
        }

        .cyber-outline-button:hover {
            border-color: color-mix(in srgb, var(--page-primary) 30%, var(--page-border));
            color: var(--page-text);
        }

        .cyber-section {
            background: color-mix(in srgb, var(--page-surface) 75%, transparent);
            border-top: 1px solid color-mix(in srgb, var(--page-border) 70%, transparent);
            border-bottom: 1px solid color-mix(in srgb, var(--page-border) 70%, transparent);
        }

        .cyber-card,
        .dossier-card,
        .proof-card,
        .identity-card,
        .route-card {
            position: relative;
            overflow: hidden;
            background: var(--page-surface-elevated);
            border: 1px solid var(--page-border);
            box-shadow: 0 18px 40px rgba(2, 8, 23, 0.12);
            transition: transform 0.22s ease, border-color 0.22s ease, box-shadow 0.22s ease, background 0.22s ease;
        }

        .cyber-card::before,
        .dossier-card::before,
        .proof-card::before,
        .identity-card::before,
        .route-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(135deg, rgba(52, 182, 255, 0.08), transparent 32%),
                linear-gradient(315deg, rgba(25, 226, 143, 0.06), transparent 30%);
            pointer-events: none;
        }

        .cyber-card:hover,
        .dossier-card:hover,
        .proof-card:hover,
        .identity-card:hover,
        .route-card:hover {
            transform: translateY(-3px);
            border-color: color-mix(in srgb, var(--page-primary) 24%, var(--page-border));
            box-shadow: 0 24px 48px rgba(2, 8, 23, 0.16);
        }

        .hero-backdrop {
            position: relative;
            overflow: hidden;
            isolation: isolate;
        }

        .hero-backdrop::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg, var(--hero-overlay-left) 0%, color-mix(in srgb, var(--hero-overlay-left) 78%, transparent) 42%, var(--hero-overlay-right) 100%),
                linear-gradient(180deg, rgba(7, 16, 29, 0.08) 0%, rgba(7, 16, 29, 0.18) 36%, rgba(7, 16, 29, 0.56) 100%);
            z-index: 1;
        }

        html[data-theme='light'] .hero-backdrop::before {
            background:
                linear-gradient(90deg, var(--hero-overlay-left) 0%, color-mix(in srgb, var(--hero-overlay-left) 82%, transparent) 44%, var(--hero-overlay-right) 100%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.04) 0%, rgba(255, 255, 255, 0.12) 38%, rgba(238, 244, 251, 0.42) 100%);
        }

        .hero-backdrop::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(var(--grid-line) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-line) 1px, transparent 1px);
            background-size: 88px 88px;
            mask-image: linear-gradient(180deg, rgba(255, 255, 255, 0.92), transparent 85%);
            z-index: 1;
        }

        .hero-backdrop > * {
            position: relative;
            z-index: 2;
        }

        .hero-backdrop .orbital-ring {
            position: absolute;
            right: 7%;
            top: 18%;
            z-index: 1;
            height: 16rem;
            width: 16rem;
            border-radius: 9999px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            box-shadow:
                0 0 0 24px rgba(255, 255, 255, 0.02),
                0 0 0 56px rgba(255, 255, 255, 0.015);
            opacity: 0.7;
        }

        html[data-theme='light'] .hero-backdrop .orbital-ring {
            border-color: rgba(15, 23, 42, 0.12);
            box-shadow:
                0 0 0 24px rgba(15, 23, 42, 0.02),
                0 0 0 56px rgba(15, 23, 42, 0.016);
        }

        .spotlight-panel {
            background: color-mix(in srgb, var(--page-surface-elevated) 88%, transparent);
            border: 1px solid var(--page-border-strong);
            backdrop-filter: blur(12px);
            box-shadow: 0 18px 46px rgba(2, 8, 23, 0.18);
        }

        .page-hero-title {
            font-size: clamp(2.8rem, 5.4vw, 5.4rem);
            font-weight: 850;
            line-height: 0.95;
            letter-spacing: -0.05em;
            color: var(--page-text);
            text-wrap: balance;
        }

        .page-hero-copy {
            max-width: 44rem;
            font-size: 1.03rem;
            line-height: 1.95;
            color: var(--page-text-soft);
        }

        .evidence-grid {
            display: grid;
            gap: 1rem;
        }

        .evidence-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            border-radius: 9999px;
            border: 1px solid var(--page-border);
            background: color-mix(in srgb, var(--page-surface) 88%, transparent);
            padding: 0.62rem 0.95rem;
            font-size: 0.74rem;
            font-weight: 600;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--page-text-soft);
        }

        .evidence-chip::before {
            content: '';
            height: 0.45rem;
            width: 0.45rem;
            border-radius: 9999px;
            background: var(--page-primary);
        }

        .dossier-label {
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--page-text-muted);
        }

        .dossier-title {
            margin-top: 0.9rem;
            font-size: 1.55rem;
            font-weight: 700;
            line-height: 1.2;
            color: var(--page-text);
        }

        .dossier-copy {
            margin-top: 1rem;
            font-size: 0.96rem;
            line-height: 1.9;
            color: var(--page-text-soft);
        }

        .proof-strip {
            display: grid;
            gap: 1rem;
        }

        .proof-stat {
            border-radius: 1.35rem;
            border: 1px solid var(--page-border);
            background: color-mix(in srgb, var(--page-surface-strong) 96%, transparent);
            padding: 1.1rem 1rem;
        }

        .proof-stat-value {
            display: block;
            font-size: 1.55rem;
            font-weight: 750;
            color: var(--page-text);
        }

        .proof-stat-label {
            display: block;
            margin-top: 0.45rem;
            font-size: 0.73rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--page-text-muted);
        }

        .route-card {
            position: relative;
            overflow: hidden;
            background:
                linear-gradient(180deg, color-mix(in srgb, var(--page-surface-elevated) 96%, transparent) 0%, color-mix(in srgb, var(--page-surface) 98%, transparent) 100%);
        }

        .route-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(130deg, color-mix(in srgb, var(--page-secondary) 10%, transparent), transparent 45%),
                linear-gradient(320deg, color-mix(in srgb, var(--page-primary) 8%, transparent), transparent 42%);
            pointer-events: none;
        }

        .route-card > * {
            position: relative;
            z-index: 1;
        }

        .quote-panel {
            background: var(--quote-bg);
            border: 1px solid var(--page-border);
            box-shadow: 0 20px 48px rgba(2, 8, 23, 0.14);
        }

        .identity-block {
            border-radius: 2rem;
            border: 1px solid var(--page-border);
            background: color-mix(in srgb, var(--page-surface-strong) 95%, transparent);
            padding: 1.4rem;
        }

        .home-carousel {
            position: relative;
            overflow: hidden;
            isolation: isolate;
        }

        .home-carousel-slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transform: scale(1.03);
            transition:
                opacity 1200ms cubic-bezier(0.22, 1, 0.36, 1),
                transform 7000ms linear;
            z-index: 0;
        }

        .home-carousel-slide::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at center, rgba(7, 16, 29, 0.08) 10%, rgba(7, 16, 29, 0.42) 55%, rgba(7, 16, 29, 0.9) 100%),
                linear-gradient(180deg, rgba(7, 16, 29, 0.45) 0%, rgba(7, 16, 29, 0.82) 100%);
        }

        html[data-theme='light'] .home-carousel-slide::after {
            background:
                radial-gradient(circle at center, rgba(238, 244, 251, 0.1) 12%, rgba(238, 244, 251, 0.5) 58%, rgba(238, 244, 251, 0.92) 100%),
                linear-gradient(180deg, rgba(238, 244, 251, 0.12) 0%, rgba(238, 244, 251, 0.86) 100%);
        }

        .home-carousel-slide.is-active {
            opacity: 1;
            transform: scale(1);
            z-index: 1;
        }

        .home-carousel-grid {
            position: absolute;
            inset: 0;
            z-index: 1;
            background-image:
                linear-gradient(var(--grid-line) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-line) 1px, transparent 1px);
            background-size: 88px 88px;
            mask-image: linear-gradient(180deg, rgba(255, 255, 255, 0.82), transparent 78%);
        }

        .home-carousel-shade {
            position: absolute;
            inset: 0;
            z-index: 1;
            background:
                linear-gradient(90deg, var(--hero-overlay-left) 0%, color-mix(in srgb, var(--hero-overlay-left) 78%, transparent) 40%, var(--hero-overlay-right) 100%),
                linear-gradient(180deg, rgba(7, 16, 29, 0.08) 0%, rgba(7, 16, 29, 0.16) 28%, rgba(7, 16, 29, 0.76) 100%);
        }

        .home-carousel-content {
            position: relative;
            z-index: 2;
        }

        .home-carousel-control {
            display: inline-flex;
            height: 3rem;
            width: 3rem;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            border: 1px solid var(--page-border);
            background: color-mix(in srgb, var(--page-surface) 88%, transparent);
            color: var(--page-text);
            backdrop-filter: blur(10px);
            transition: background 0.25s ease, transform 0.25s ease;
        }

        .home-carousel-control:hover {
            background: color-mix(in srgb, var(--page-surface) 96%, transparent);
            transform: translateY(-1px);
        }

        .home-carousel-dot {
            height: 0.65rem;
            width: 0.65rem;
            border-radius: 9999px;
            background: color-mix(in srgb, var(--page-text) 28%, transparent);
            transition: all 0.28s ease;
        }

        .home-carousel-dot.is-active {
            width: 2.4rem;
            background: var(--page-text);
        }

        .hero-slide-card {
            width: 100%;
            border-radius: 1.35rem;
            border: 1px solid var(--page-border);
            background: color-mix(in srgb, var(--page-surface) 90%, transparent);
            padding: 1rem;
            text-align: left;
            transition: border-color 220ms ease, background 220ms ease, transform 220ms ease;
        }

        .hero-slide-card:hover,
        .hero-slide-card.is-active {
            border-color: color-mix(in srgb, var(--page-secondary) 30%, var(--page-border));
            background: color-mix(in srgb, var(--page-surface-strong) 96%, transparent);
        }

        .hero-slide-card:hover {
            transform: translateY(-2px);
        }

        .hero-slide-progress {
            position: relative;
            display: block;
            height: 0.26rem;
            width: 5.5rem;
            overflow: hidden;
            border-radius: 9999px;
            background: color-mix(in srgb, var(--page-text) 12%, transparent);
        }

        .hero-slide-progress > span {
            display: block;
            height: 100%;
            width: 0;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--page-primary) 0%, var(--page-secondary) 100%);
        }

        .hero-slide-card.is-active .hero-slide-progress > span {
            width: 100%;
            transition: width 5200ms linear;
        }

        .hero-metric {
            border: 1px solid var(--page-border);
            background: color-mix(in srgb, var(--page-surface) 88%, transparent);
            backdrop-filter: blur(10px);
            border-radius: 1.25rem;
            padding: 1rem 1.1rem;
        }

        .hero-metric-label {
            display: block;
            font-size: 0.72rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--page-text-muted);
        }

        .hero-metric-value {
            display: block;
            margin-top: 0.55rem;
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--page-text);
        }

        .hero-ticker {
            display: flex;
            width: max-content;
            gap: 2rem;
            padding: 1.1rem 0;
            white-space: nowrap;
            animation: ticker-scroll 28s linear infinite;
            color: var(--page-text-soft);
            font-size: 0.8rem;
            letter-spacing: 0.24em;
            text-transform: uppercase;
        }

        .hero-ticker span {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 2rem;
        }

        .hero-ticker span::after {
            content: '';
            display: inline-block;
            height: 0.35rem;
            width: 0.35rem;
            border-radius: 9999px;
            background: var(--page-primary);
        }

        .story-panel {
            border: 1px solid var(--page-border);
            background: var(--page-surface-elevated);
            box-shadow: 0 20px 48px rgba(2, 8, 23, 0.12);
        }

        .story-panel-image-wrap {
            position: relative;
            overflow: hidden;
            min-height: 22rem;
        }

        .story-panel-image-wrap::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(7, 16, 29, 0.08) 0%, rgba(7, 16, 29, 0.32) 100%);
        }

        html[data-theme='light'] .story-panel-image-wrap::after {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.04) 0%, rgba(238, 244, 251, 0.26) 100%);
        }

        .story-panel-image {
            height: 100%;
            width: 100%;
            object-fit: cover;
            transition: transform 1400ms cubic-bezier(0.22, 1, 0.36, 1);
        }

        .story-panel:hover .story-panel-image {
            transform: scale(1.04);
        }

        .reveal-fade {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 700ms ease, transform 700ms ease;
        }

        .reveal-fade.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        @keyframes ticker-scroll {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(-50%);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .home-carousel-slide,
            .reveal-fade,
            .story-panel-image,
            .hero-ticker {
                transition: none;
                transform: none;
                animation: none;
            }
        }
    </style>
</head>
<body class="page-shell">
    @include('frontend.components.navbar')

    <main class="pt-24">
        @yield('content')
    </main>

    @include('frontend.components.footer')
</body>
</html>
