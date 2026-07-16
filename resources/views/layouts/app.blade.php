<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="theme-color" content="#066FDA" />
    <link rel="manifest" href="/manifest.json" />
    <title>@yield('title', 'TruSaba')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Design system components from trusaba.css */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        
        .stage {
            min-height: 100%;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 24px 16px 40px;
        }
        .phone {
            width: 100%;
            max-width: var(--phone-w);
            min-height: 780px;
            background: var(--bg);
            border-radius: 28px;
            box-shadow: 0 20px 50px oklch(0.22 0.05 255 / 0.15), 0 0 0 1px oklch(0.22 0 0 / 0.06);
            overflow: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        .status-bar {
            height: 44px;
            padding: 12px 22px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.02em;
            color: var(--fg);
            flex-shrink: 0;
        }
        .app-body {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding-bottom: calc(var(--nav-h) + 12px + var(--safe-b));
            -webkit-overflow-scrolling: touch;
        }
        .app-body.no-nav { padding-bottom: 24px; }
        .app-body.has-sticky { padding-bottom: 100px; }
        
        /* Type */
        h1 { font-size: 24px; font-weight: 600; letter-spacing: -0.02em; line-height: 1.2; }
        h2 { font-size: 18px; font-weight: 600; letter-spacing: -0.01em; line-height: 1.25; }
        h3 { font-size: 15px; font-weight: 600; line-height: 1.3; }
        .eyebrow { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); }
        .muted { color: var(--muted); }
        .small { font-size: 13px; letter-spacing: 0.01em; }
        .caption { font-size: 11px; letter-spacing: 0.02em; color: var(--muted); }
        .mono { font-family: var(--mono); font-variant-numeric: tabular-nums; }
        
        /* Header */
        .app-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 20px 12px;
            flex-shrink: 0;
        }
        .app-header .logo {
            width: 36px; height: 36px;
            object-fit: contain;
            border-radius: 8px;
        }
        .app-header .title-block { flex: 1; min-width: 0; }
        .app-header .title-block h1 { font-size: 18px; }
        .icon-btn {
            width: 40px; height: 40px;
            border-radius: var(--radius-full);
            border: 1px solid var(--border);
            background: var(--bg);
            display: grid;
            place-items: center;
            color: var(--fg);
            cursor: pointer;
            flex-shrink: 0;
            text-decoration: none;
        }
        .icon-btn:active { transform: scale(0.96); }
        .icon-btn svg { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }
        
        /* Content */
        .pad { padding: 0 20px; }
        .stack { display: flex; flex-direction: column; gap: 12px; }
        .stack-sm { display: flex; flex-direction: column; gap: 8px; }
        .row { display: flex; align-items: center; gap: 10px; }
        .row-between { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        
        /* Cards */
        .card {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            padding: 14px;
        }
        .card-soft {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 14px;
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 48px;
            padding: 0 18px;
            border-radius: 12px;
            border: none;
            font-family: var(--font);
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.02em;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.12s ease, box-shadow 0.12s ease, background 0.12s ease;
        }
        .btn:active { transform: translateY(1px) scale(0.99); }
        .btn-primary {
            background: var(--accent-hex);
            color: #fff;
            box-shadow: 0 6px 16px oklch(0.55 0.18 255 / 0.35);
        }
        .btn-primary:hover { filter: brightness(1.05); }
        .btn-secondary {
            background: var(--surface);
            color: var(--fg);
            border: 1px solid var(--border);
        }
        .btn-danger {
            background: var(--danger);
            color: #fff;
            box-shadow: 0 6px 16px oklch(0.58 0.22 27 / 0.35);
        }
        .btn-ghost {
            background: transparent;
            color: var(--accent-hex);
            min-height: 40px;
            padding: 0 12px;
        }
        .btn-block { width: 100%; }
        .btn-sm { min-height: 36px; padding: 0 12px; font-size: 13px; border-radius: 10px; }
        .btn svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
        
        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: var(--radius-full);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .badge-gold { background: oklch(0.85 0.17 87 / 0.25); color: oklch(0.45 0.12 80); }
        .badge-blue { background: oklch(0.55 0.18 255 / 0.12); color: var(--accent-hex); }
        .badge-success { background: oklch(0.63 0.17 149 / 0.15); color: oklch(0.45 0.14 149); }
        .badge-warn { background: oklch(0.8 0.16 86 / 0.2); color: oklch(0.45 0.12 70); }
        .badge-danger { background: oklch(0.58 0.22 27 / 0.12); color: var(--danger); }
        
        /* Bottom nav */
        .bottom-nav {
            position: absolute;
            left: 0; right: 0; bottom: 0;
            height: calc(var(--nav-h) + var(--safe-b));
            padding-bottom: var(--safe-b);
            background: oklch(1 0 0 / 0.92);
            backdrop-filter: blur(12px);
            border-top: 1px solid var(--border);
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            z-index: 20;
        }
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3px;
            text-decoration: none;
            color: var(--muted);
            font-size: 10px;
            font-weight: 550;
            letter-spacing: 0.02em;
            min-height: 44px;
        }
        .nav-item svg { width: 22px; height: 22px; stroke: currentColor; fill: none; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }
        .nav-item.active { color: var(--accent-hex); }
        .nav-item.active svg { stroke-width: 2; }
        
        /* FAB SOS */
        .fab-sos {
            position: absolute;
            right: 18px;
            bottom: calc(var(--nav-h) + 16px + var(--safe-b));
            width: 56px; height: 56px;
            border-radius: 50%;
            background: var(--danger);
            color: #fff;
            border: none;
            box-shadow: 0 8px 24px oklch(0.58 0.22 27 / 0.45);
            font-family: var(--font);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            cursor: pointer;
            z-index: 25;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1px;
        }
        .fab-sos svg { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .fab-sos:active { transform: scale(0.95); }
        
        /* Modal */
        .modal-backdrop {
            position: absolute;
            inset: 0;
            background: oklch(0.15 0.02 255 / 0.45);
            z-index: 40;
            display: none;
            align-items: flex-end;
            justify-content: center;
            padding: 16px;
        }
        .modal-backdrop.open { display: flex; }
        .modal {
            width: 100%;
            background: var(--bg);
            border-radius: 20px;
            padding: 24px 20px 20px;
            box-shadow: 0 -8px 40px oklch(0.15 0.02 255 / 0.2);
            animation: sheet-up 0.28s ease;
        }
        @keyframes sheet-up {
            from { transform: translateY(24px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .modal-icon {
            width: 56px; height: 56px;
            border-radius: 50%;
            background: oklch(0.58 0.22 27 / 0.12);
            color: var(--danger);
            display: grid;
            place-items: center;
            margin: 0 auto 14px;
        }
        .modal-icon svg { width: 28px; height: 28px; stroke: currentColor; fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
        .modal h2 { text-align: center; margin-bottom: 6px; }
        .modal p { text-align: center; color: var(--muted); font-size: 14px; margin-bottom: 20px; }
        .modal-actions { display: flex; flex-direction: column; gap: 10px; }
        
        /* Sticky CTA */
        .sticky-cta {
            position: absolute;
            left: 0; right: 0;
            bottom: 0;
            padding: 12px 20px calc(12px + var(--safe-b));
            background: linear-gradient(to top, var(--bg) 70%, transparent);
            z-index: 12;
        }
        
        /* ── Form ── */
        label.field-label {
            display: block;
            font-size: 13px;
            font-weight: 550;
            letter-spacing: 0.02em;
            margin-bottom: 6px;
            color: var(--fg);
        }
        label.field-label .req { color: var(--danger); margin-left: 2px; }
        .input, .select, .textarea {
            width: 100%;
            min-height: 48px;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--bg);
            font-family: var(--font);
            font-size: 15px;
            color: var(--fg);
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .input:focus, .select:focus, .textarea:focus {
            border-color: var(--accent-hex);
            box-shadow: 0 0 0 3px oklch(0.55 0.18 255 / 0.15);
        }
        .textarea { min-height: 88px; resize: vertical; line-height: 1.5; }
        .field { margin-bottom: 16px; }

        /* ── Chips ── */
        .chips { display: flex; flex-wrap: wrap; gap: 8px; }
        .chip {
            min-height: 36px;
            padding: 6px 14px;
            border-radius: var(--radius-full);
            border: 1px solid var(--border);
            background: var(--bg);
            font-family: var(--font);
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 0.01em;
            color: var(--fg);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .chip.active {
            background: oklch(0.55 0.18 255 / 0.1);
            border-color: var(--accent-hex);
            color: var(--accent-hex);
            font-weight: 600;
        }

        /* ── Progress Steps ── */
        .steps { display: flex; align-items: center; gap: 0; padding: 8px 20px 16px; }
        .step-dot {
            width: 28px; height: 28px;
            border-radius: 50%;
            border: 2px solid var(--border);
            background: var(--bg);
            display: grid;
            place-items: center;
            font-size: 11px;
            font-weight: 600;
            color: var(--muted);
            flex-shrink: 0;
        }
        .step-dot.done {
            background: var(--accent-hex);
            border-color: var(--accent-hex);
            color: #fff;
        }
        .step-dot.current {
            border-color: var(--accent-hex);
            color: var(--accent-hex);
            box-shadow: 0 0 0 3px oklch(0.55 0.18 255 / 0.15);
        }
        .step-line { flex: 1; height: 2px; background: var(--border); margin: 0 4px; }
        .step-line.done { background: var(--accent-hex); }

        /* ── Range Slider ── */
        .range-wrap { padding: 4px 0 8px; }
        input[type="range"] {
            -webkit-appearance: none;
            width: 100%;
            height: 6px;
            border-radius: 3px;
            background: var(--border);
            outline: none;
        }
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 22px; height: 22px;
            border-radius: 50%;
            background: var(--accent-hex);
            border: 3px solid #fff;
            box-shadow: var(--shadow);
            cursor: pointer;
        }

        /* ── Auth ── */
        .auth-hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 28px 24px 8px;
        }
        .auth-hero .logo-lg {
            width: 88px; height: 88px;
            object-fit: contain;
            margin-bottom: 14px;
        }
        .auth-hero h1 {
            font-size: 26px;
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }
        .auth-hero p {
            font-size: 14px;
            color: var(--muted);
            max-width: 280px;
            line-height: 1.45;
        }
        .auth-tabs {
            display: flex;
            gap: 4px;
            padding: 4px;
            margin: 20px 20px 8px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
        }
        .auth-tab {
            flex: 1;
            min-height: 42px;
            border: none;
            background: transparent;
            border-radius: 10px;
            font-family: var(--font);
            font-size: 14px;
            font-weight: 550;
            letter-spacing: 0.02em;
            color: var(--muted);
            cursor: pointer;
        }
        .auth-tab.active {
            background: var(--bg);
            color: var(--accent-hex);
            box-shadow: var(--shadow-sm);
            font-weight: 600;
        }
        .btn-google {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            min-height: 48px;
            padding: 0 16px;
            border-radius: 12px;
            border: 1.5px solid var(--border);
            background: var(--bg);
            font-family: var(--font);
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.01em;
            color: var(--fg);
            cursor: pointer;
            transition: background 0.12s, box-shadow 0.12s, transform 0.12s;
            text-decoration: none;
        }
        .btn-google:hover { background: var(--surface); box-shadow: var(--shadow-sm); }
        .btn-google:active { transform: translateY(1px) scale(0.99); }
        .btn-google svg { width: 20px; height: 20px; flex-shrink: 0; }
        .auth-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 18px 0;
            color: var(--muted);
            font-size: 12px;
            font-weight: 550;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .auth-divider::before, .auth-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--border);
        }
        .auth-footer {
            text-align: center;
            font-size: 13px;
            color: var(--muted);
            margin-top: 16px;
            padding-bottom: 8px;
        }
        .auth-footer a {
            color: var(--accent-hex);
            font-weight: 600;
            text-decoration: none;
        }
        .password-wrap { position: relative; }
        .password-wrap .input { padding-right: 48px; }
        .password-toggle {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            border: none;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            display: grid;
            place-items: center;
            border-radius: 10px;
        }
        .password-toggle svg {
            width: 18px; height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.7;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .field-hint {
            font-size: 12px;
            color: var(--muted);
            margin-top: 6px;
            letter-spacing: 0.01em;
        }
        .auth-toast {
            position: absolute;
            left: 16px;
            right: 16px;
            top: 56px;
            z-index: 30;
            padding: 12px 14px;
            border-radius: 12px;
            background: var(--fg);
            color: #fff;
            font-size: 13px;
            font-weight: 550;
            text-align: center;
            box-shadow: var(--shadow);
            opacity: 0;
            pointer-events: none;
            transform: translateY(-8px);
            transition: opacity 0.2s, transform 0.2s;
        }
        .auth-toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* ── Splash ── */
        .splash-bg {
            position: absolute;
            inset: 0;
            background: var(--accent-hex);
            overflow: hidden;
        }
        .splash-bg::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 70% 50% at 50% 40%, oklch(0.65 0.16 230 / 0.55), transparent 70%),
                        radial-gradient(ellipse 40% 30% at 30% 80%, oklch(0.85 0.17 87 / 0.18), transparent 60%);
        }
        .splash-body {
            position: relative;
            z-index: 2;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 28px;
            text-align: center;
        }
        .splash-aura {
            width: 180px; height: 180px;
            position: relative;
            margin-bottom: 28px;
            display: grid;
            place-items: center;
        }
        .splash-ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px solid oklch(1 0 0 / 0.18);
            border-top-color: oklch(1 0 0 / 0.45);
            animation: splash-spin 4s linear infinite;
        }
        .splash-ring-inner {
            position: absolute;
            inset: 18px;
            border-radius: 50%;
            border: 1.5px solid oklch(1 0 0 / 0.12);
            border-bottom-color: oklch(1 0 0 / 0.35);
            animation: splash-spin 6s linear infinite reverse;
        }
        @keyframes splash-spin { to { transform: rotate(360deg); } }
        .splash-logo {
            width: 88px; height: 88px;
            object-fit: contain;
            position: relative;
            z-index: 2;
            animation: splash-breathe 2.6s ease-in-out infinite;
        }
        @keyframes splash-breathe {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.06); }
        }
        .splash-title {
            font-size: 32px;
            font-weight: 600;
            letter-spacing: -0.025em;
            line-height: 1.1;
            color: #fff;
            margin-bottom: 10px;
            animation: splash-fade-up 0.7s 0.15s both;
        }
        .splash-tagline {
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 0.01em;
            color: oklch(1 0 0 / 0.72);
            max-width: 260px;
            line-height: 1.45;
            animation: splash-fade-up 0.7s 0.3s both;
        }
        .splash-accent-line {
            width: 48px;
            height: 3px;
            border-radius: 99px;
            background: var(--secondary-hex);
            margin: 18px 0 0;
            animation: splash-fade-up 0.7s 0.45s both;
        }
        @keyframes splash-fade-up {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .splash-version {
            position: absolute;
            bottom: 16px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.04em;
            color: oklch(1 0 0 / 0.35);
            z-index: 2;
        }

        /* ── Tabs (generic) ── */
        .tabs {
            display: flex;
            gap: 4px;
            padding: 4px;
            background: var(--surface);
            border-radius: 12px;
            border: 1px solid var(--border);
            margin: 0 20px 16px;
        }
        .tab {
            flex: 1;
            min-height: 40px;
            border: none;
            background: transparent;
            border-radius: 10px;
            font-family: var(--font);
            font-size: 13px;
            font-weight: 550;
            letter-spacing: 0.01em;
            color: var(--muted);
            cursor: pointer;
        }
        .tab.active {
            background: var(--bg);
            color: var(--accent-hex);
            box-shadow: var(--shadow-sm);
            font-weight: 600;
        }

        /* ── Activity Icons ── */
        .act-icon {
            width: 40px; height: 40px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }
        .act-icon svg { width: 20px; height: 20px; stroke: currentColor; fill: none; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }
        .act-hotel { background: oklch(0.55 0.18 255 / 0.12); color: var(--accent-hex); }
        .act-food { background: oklch(0.85 0.17 87 / 0.2); color: oklch(0.5 0.12 80); }
        .act-place { background: oklch(0.63 0.17 149 / 0.15); color: oklch(0.45 0.14 149); }
        .act-transport { background: oklch(0.55 0.05 255 / 0.12); color: var(--muted); }
        .act-shop { background: oklch(0.58 0.15 320 / 0.12); color: oklch(0.5 0.14 320); }

        /* ── Image Placeholders ── */
        .ph-img {
            background: linear-gradient(135deg, oklch(0.55 0.12 255 / 0.15), oklch(0.85 0.12 87 / 0.2)), var(--surface);
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: flex-end;
            justify-content: flex-start;
            color: var(--muted);
            font-size: 11px;
            font-weight: 550;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .hero-img { width: 100%; height: 180px; border-radius: 0; }
        .thumb { width: 64px; height: 64px; border-radius: 12px; flex-shrink: 0; object-fit: cover; }
        .thumb-lg { width: 100%; height: 140px; border-radius: 12px; object-fit: cover; }

        /* ── Room Option ── */
        .room-opt {
            display: flex; gap: 12px; padding: 12px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            cursor: pointer;
            transition: border-color 0.15s, background 0.15s;
        }
        .room-opt.selected { border-color: var(--accent-hex); background: oklch(0.55 0.18 255 / 0.06); }
        .room-opt input { accent-color: var(--accent-hex); margin-top: 4px; }

        @media (max-width: 480px) {
            .stage { padding: 0; background: var(--bg); }
            .phone {
                max-width: 100%;
                min-height: 100dvh;
                border-radius: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="stage">
        <div class="phone" data-od-id="phone-{{ $page ?? 'main' }}">
            {{-- Status Bar --}}
            <div class="status-bar">
                <span>9:41</span>
                <span class="icons" style="display:flex;gap:6px;align-items:center">●●● 100%</span>
            </div>

            {{-- Content --}}
            @yield('content')

            {{-- SOS FAB (shown on trip pages) --}}
            @if(($showSos ?? false))
            <button type="button" class="fab-sos" id="fabSos" aria-label="Tombol SOS" onclick="document.getElementById('sosModal').classList.add('open')">
                <svg viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01"/><path d="M10.3 4.3L2.8 18a2 2 0 001.7 3h15a2 2 0 001.7-3L13.7 4.3a2 2 0 00-3.4 0z"/></svg>
                SOS
            </button>
            
            {{-- SOS Modal --}}
            <div class="modal-backdrop" id="sosModal" onclick="if(event.target===this)this.classList.remove('open')">
                <div class="modal" role="dialog">
                    <div class="modal-icon">
                        <svg viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01"/><path d="M10.3 4.3L2.8 18a2 2 0 001.7 3h15a2 2 0 001.7-3L13.7 4.3a2 2 0 00-3.4 0z"/></svg>
                    </div>
                    <h2>Kirim sinyal darurat?</h2>
                    <p>Lokasi GPS-mu akan dikirim ke kontak darurat & tim TruSaba. Hanya gunakan saat benar-benar butuh bantuan.</p>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-danger btn-block">Kirim Sinyal Darurat</button>
                        <button type="button" class="btn btn-secondary btn-block" onclick="document.getElementById('sosModal').classList.remove('open')">Batal</button>
                    </div>
                </div>
            </div>
            @endif

            {{-- Bottom Navigation --}}
            @if(($showNav ?? true))
            <nav class="bottom-nav">
                <a class="nav-item {{ ($navActive ?? '') === 'home' ? 'active' : '' }}" href="{{ route('today') }}">
                    <svg viewBox="0 0 24 24"><path d="M4 10.5L12 4l8 6.5V20a1 1 0 01-1 1h-5v-6H10v6H5a1 1 0 01-1-1v-9.5z"/></svg>
                    Home
                </a>
                <a class="nav-item {{ ($navActive ?? '') === 'booking' ? 'active' : '' }}" href="{{ route('bookings.index') }}">
                    <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 5V3M16 5V3"/></svg>
                    Booking
                </a>
                <a class="nav-item {{ ($navActive ?? '') === 'chat' ? 'active' : '' }}" href="{{ route('chat') }}">
                    <svg viewBox="0 0 24 24"><path d="M5 18l-1 3 3-1h9a3 3 0 003-3V7a3 3 0 00-3-3H8a3 3 0 00-3 3v11z"/></svg>
                    Chat
                </a>
                <a class="nav-item {{ ($navActive ?? '') === 'profile' ? 'active' : '' }}" href="{{ route('history') }}">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0116 0"/></svg>
                    Profil
                </a>
            </nav>
            @endif
        </div>
    </div>
    @stack('scripts')
</body>
</html>
