<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NovaBanka - @yield('title', 'Dijital Bankacılık')</title>

    <!-- Google Fonts: Instrument Serif + DM Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-base:      #080C14;
            --bg-panel:     #0D1220;
            --bg-card:      #101726;
            --bg-card-2:    #131C2E;
            --bg-hover:     rgba(255,255,255,0.03);
            --accent:       #00D9A3;
            --accent-dim:   rgba(0,217,163,0.12);
            --accent-glow:  rgba(0,217,163,0.25);
            --blue:         #3B82F6;
            --blue-dim:     rgba(59,130,246,0.12);
            --gold:         #F0B429;
            --gold-dim:     rgba(240,180,41,0.12);
            --danger:       #F43F5E;
            --danger-dim:   rgba(244,63,94,0.12);
            --text:         #EDF2F7;
            --text-sub:     #94A3B8;
            --text-muted:   #4A5568;
            --border:       rgba(255,255,255,0.055);
            --border-strong:rgba(255,255,255,0.10);
            --radius-sm:    8px;
            --radius:       14px;
            --radius-lg:    20px;
            --radius-xl:    28px;
            --font:         'DM Sans', sans-serif;
            --mono:         'DM Mono', monospace;
            --serif:        'Instrument Serif', serif;
            --shadow-card:  0 1px 3px rgba(0,0,0,0.4), 0 8px 32px rgba(0,0,0,0.25);
            --shadow-float: 0 20px 60px rgba(0,0,0,0.5);
            --shadow-glow:  0 0 40px rgba(0,217,163,0.15);
            --sidebar-w:    268px;
            --topbar-h:     64px;
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            font-family: var(--font);
            background: var(--bg-base);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ─── Noise texture overlay ─── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.035'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 9999;
            opacity: 0.5;
        }

        /* ═══════════════════════════════════════════
           SIDEBAR
        ═══════════════════════════════════════════ */
        .sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: var(--bg-panel);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            left: 0; top: 0;
            z-index: 200;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Top gradient accent line */
        .sidebar::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
        }

        /* ─ Logo ─ */
        .sidebar-logo {
            padding: 24px 20px 20px;
            border-bottom: 1px solid var(--border);
        }

        .logo-mark {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
        }

        .logo-icon {
            width: 40px; height: 40px;
            background: linear-gradient(145deg, var(--accent) 0%, #00A87E 100%);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 16px rgba(0,217,163,0.3);
        }

        .logo-icon i {
            font-size: 17px;
            color: #051A12;
        }

        .logo-text {
            font-size: 17px;
            font-weight: 700;
            letter-spacing: -0.4px;
            color: var(--text);
        }

        .logo-sub {
            font-size: 10px;
            color: var(--text-muted);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-top: 1px;
            font-weight: 500;
        }

        /* ─ Nav ─ */
        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }

        .sidebar-nav::-webkit-scrollbar { width: 0; }

        .nav-section-label {
            font-size: 10px;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 10px 10px 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: var(--radius-sm);
            text-decoration: none;
            color: var(--text-sub);
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.18s ease;
            margin-bottom: 1px;
            position: relative;
        }

        .nav-item:hover {
            background: var(--bg-hover);
            color: var(--text);
        }

        .nav-item.active {
            background: var(--accent-dim);
            color: var(--accent);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0; top: 6px; bottom: 6px;
            width: 2.5px;
            background: var(--accent);
            border-radius: 0 2px 2px 0;
            box-shadow: 0 0 10px var(--accent);
        }

        .nav-icon {
            width: 32px; height: 32px;
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
            color: var(--text-muted);
            transition: all 0.18s ease;
        }

        .nav-item:hover .nav-icon {
            color: var(--text-sub);
        }

        .nav-item.active .nav-icon {
            background: var(--accent-dim);
            color: var(--accent);
        }

        /* ─ Sidebar User ─ */
        .sidebar-user {
            padding: 14px;
            border-top: 1px solid var(--border);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 12px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
        }

        .user-avatar {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, var(--accent) 0%, #00A87E 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
            color: #051A12;
            flex-shrink: 0;
            letter-spacing: 0.5px;
        }

        .user-name  { font-size: 13px; font-weight: 600; color: var(--text); }
        .user-email { font-size: 11px; color: var(--text-muted); margin-top: 1px; }

        .logout-btn {
            background: none; border: none;
            color: var(--text-muted); cursor: pointer;
            font-size: 14px; margin-left: auto;
            padding: 6px;
            border-radius: 6px;
            transition: all 0.18s ease;
            display: flex;
            align-items: center;
        }

        .logout-btn:hover {
            color: var(--danger);
            background: var(--danger-dim);
        }

        /* ═══════════════════════════════════════════
           MAIN CONTENT
        ═══════════════════════════════════════════ */
        .main {
            flex: 1;
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ─ Topbar ─ */
        .topbar {
            height: var(--topbar-h);
            background: rgba(8,12,20,0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 28px;
            position: sticky;
            top: 0; z-index: 100;
            gap: 16px;
        }

        .topbar-title {
            flex: 1;
            font-size: 16px;
            font-weight: 600;
            color: var(--text);
        }

        .topbar-badge {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 6px 14px;
            background: var(--accent-dim);
            border: 1px solid rgba(0,217,163,0.18);
            border-radius: 50px;
            font-size: 11.5px;
            color: var(--accent);
            font-weight: 500;
        }

        .status-dot {
            width: 7px; height: 7px;
            background: var(--accent);
            border-radius: 50%;
            animation: blink 2.5s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.4; transform: scale(0.85); }
        }

        /* Burger */
        .burger {
            display: none;
            background: none; border: none;
            color: var(--text); cursor: pointer;
            font-size: 18px;
            width: 34px; height: 34px;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .burger:hover { background: var(--bg-hover); }

        /* ─ Flash messages ─ */
        .flash-wrapper {
            padding: 16px 28px 0;
        }

        .alert {
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        .alert-success {
            background: var(--accent-dim);
            border: 1px solid rgba(0,217,163,0.2);
            color: var(--accent);
        }

        .alert-error {
            background: var(--danger-dim);
            border: 1px solid rgba(244,63,94,0.2);
            color: var(--danger);
        }

        /* ─ Page Content ─ */
        .page-content {
            padding: 26px 28px 40px;
            flex: 1;
        }

        /* ═══════════════════════════════════════════
           COMPONENTS
        ═══════════════════════════════════════════ */

        /* Cards */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 22px;
        }

        .card-sm { padding: 14px 18px; }
        .card-flat { background: var(--bg-base); }

        /* Grid */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }

        /* Stat Card */
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 18px 22px;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.08), transparent);
        }

        .stat-label {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 600;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 26px;
            font-weight: 700;
            font-family: var(--mono);
            letter-spacing: -1px;
            line-height: 1;
        }

        .stat-change {
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 8px;
            font-weight: 500;
        }

        .change-up   { color: var(--accent); }
        .change-down { color: var(--danger); }
        .change-neutral { color: var(--text-muted); }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 18px;
            border-radius: var(--radius-sm);
            font-family: var(--font);
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.18s ease;
            text-decoration: none;
            letter-spacing: 0.1px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent) 0%, #00A87E 100%);
            color: #041610;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 28px var(--accent-glow);
        }

        .btn-primary:active { transform: translateY(0); }

        .btn-secondary {
            background: rgba(255,255,255,0.06);
            color: var(--text-sub);
            border: 1px solid var(--border-strong);
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.09);
            color: var(--text);
        }

        .btn-danger {
            background: var(--danger-dim);
            color: var(--danger);
            border: 1px solid rgba(244,63,94,0.25);
        }

        .btn-danger:hover { background: rgba(244,63,94,0.2); }

        .btn-ghost {
            background: transparent;
            color: var(--text-sub);
            border: 1px solid var(--border);
        }

        .btn-ghost:hover {
            background: var(--bg-hover);
            color: var(--text);
        }

        .btn-sm { padding: 7px 12px; font-size: 12px; border-radius: 7px; }
        .btn-full { width: 100%; justify-content: center; }

        /* Form */
        .form-group { margin-bottom: 18px; }

        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 7px;
        }

        .form-control {
            width: 100%;
            background: var(--bg-base);
            border: 1px solid var(--border-strong);
            border-radius: var(--radius-sm);
            padding: 11px 14px;
            color: var(--text);
            font-family: var(--font);
            font-size: 13.5px;
            transition: all 0.18s ease;
            outline: none;
            -webkit-appearance: none;
        }

        .form-control:focus {
            border-color: rgba(0,217,163,0.5);
            box-shadow: 0 0 0 3px rgba(0,217,163,0.08);
        }

        .form-control::placeholder { color: var(--text-muted); }

        select.form-control {
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%234A5568' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            padding-right: 36px;
        }

        select.form-control option { background: var(--bg-panel); }

        /* Table */
        .table { width: 100%; border-collapse: collapse; }

        .table th {
            font-size: 10.5px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.7px;
            padding: 0 14px 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .table td {
            padding: 13px 14px;
            font-size: 13.5px;
            border-bottom: 1px solid var(--border);
        }

        .table tr:last-child td { border-bottom: none; }

        .table tbody tr {
            transition: background 0.15s;
        }

        .table tbody tr:hover { background: var(--bg-hover); }

        /* Badge */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 5px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .badge-success { background: var(--accent-dim); color: var(--accent); }
        .badge-danger  { background: var(--danger-dim); color: var(--danger); }
        .badge-info    { background: var(--blue-dim);   color: var(--blue); }
        .badge-gold    { background: var(--gold-dim);   color: var(--gold); }

        /* Section header */
        .section-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.2px;
        }

        .section-sub {
            font-size: 12.5px;
            color: var(--text-muted);
            margin-top: 3px;
        }

        /* Divider */
        .divider {
            height: 1px;
            background: var(--border);
            margin: 18px 0;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }

        .empty-state-icon {
            width: 56px; height: 56px;
            background: var(--bg-card-2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: var(--text-muted);
            margin: 0 auto 14px;
        }

        .empty-state-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-sub);
            margin-bottom: 6px;
        }

        .empty-state-text {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        /* Progress bar */
        .progress-bar {
            height: 4px;
            background: rgba(255,255,255,0.07);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent), #00A87E);
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        /* Info row */
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
        }

        .info-row:last-child { border-bottom: none; padding-bottom: 0; }
        .info-row:first-child { padding-top: 0; }

        .info-row-label { color: var(--text-muted); }
        .info-row-value { font-weight: 600; color: var(--text); }

        /* Pill tab */
        .tab-group {
            display: flex;
            background: var(--bg-base);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 3px;
            gap: 2px;
        }

        .tab-btn {
            flex: 1;
            padding: 9px 14px;
            border: none;
            border-radius: 7px;
            font-family: var(--font);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.18s ease;
            background: transparent;
            color: var(--text-muted);
        }

        .tab-btn.active-buy  { background: var(--accent-dim); color: var(--accent); }
        .tab-btn.active-sell { background: var(--danger-dim); color: var(--danger); }

        /* Calc box */
        .calc-box {
            background: rgba(0,217,163,0.04);
            border: 1px solid rgba(0,217,163,0.1);
            border-radius: var(--radius-sm);
            padding: 14px;
            margin-bottom: 18px;
        }

        /* Warning box */
        .warning-box {
            background: var(--gold-dim);
            border: 1px solid rgba(240,180,41,0.2);
            border-radius: var(--radius-sm);
            padding: 12px 14px;
            font-size: 12.5px;
            color: var(--gold);
            margin-bottom: 18px;
            display: flex;
            gap: 9px;
            align-items: flex-start;
        }

        .warning-box i { margin-top: 1px; flex-shrink: 0; }

        /* Mono values */
        .mono { font-family: var(--mono); }

        /* Mobile */
        @media (max-width: 900px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open { transform: translateX(0); }
            .main { margin-left: 0; }
            .burger { display: flex; }
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
            .page-content { padding: 20px 16px 32px; }
            .topbar { padding: 0 16px; }
            .flash-wrapper { padding: 12px 16px 0; }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- ===== SIDEBAR ===== --}}
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <a href="{{ route('dashboard') }}" class="logo-mark">
            <div class="logo-icon">
                <i class="fa-solid fa-landmark"></i>
            </div>
            <div>
                <div class="logo-text">NovaBanka</div>
                <div class="logo-sub">Dijital Bankacılık</div>
            </div>
        </a>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Ana Menü</div>

        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <div class="nav-icon"><i class="fa-solid fa-chart-pie"></i></div>
            <span>Genel Bakış</span>
        </a>

        <div class="nav-section-label" style="margin-top:10px">İşlemler</div>

        <a href="{{ route('transfer.index') }}" class="nav-item {{ request()->routeIs('transfer.*') ? 'active' : '' }}">
            <div class="nav-icon"><i class="fa-solid fa-paper-plane"></i></div>
            <span>Para Gönder / Al</span>
        </a>

        <a href="{{ route('cards.index') }}" class="nav-item {{ request()->routeIs('cards.*') ? 'active' : '' }}">
            <div class="nav-icon"><i class="fa-solid fa-credit-card"></i></div>
            <span>Kartlarım</span>
        </a>

        <a href="{{ route('exchange.index') }}" class="nav-item {{ request()->routeIs('exchange.*') ? 'active' : '' }}">
            <div class="nav-icon"><i class="fa-solid fa-arrow-trend-up"></i></div>
            <span>Borsa & Döviz</span>
        </a>

        <div class="nav-section-label" style="margin-top:10px">Hesap</div>

<a href="{{ route('transactions.index') }}"
   class="nav-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
    <div class="nav-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
    <span>Hesap Hareketleri</span>
</a>

<a href="{{ route('settings.index') }}"
   class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
    <div class="nav-icon"><i class="fa-solid fa-sliders"></i></div>
    <span>Ayarlar</span>
</a>
    </nav>

    <div class="sidebar-user">
        <div class="user-card">
            <div class="user-avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->surname, 0, 1)) }}
            </div>
            <div style="flex:1; min-width:0;">
                <div class="user-name">{{ auth()->user()->full_name }}</div>
                <div class="user-email" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ auth()->user()->email }}</div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn" title="Çıkış Yap">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- ===== ANA ALAN ===== --}}
<main class="main">
    {{-- Top Bar --}}
    <div class="topbar">
        <button class="burger" onclick="toggleSidebar()" aria-label="Menü">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="topbar-title">@yield('title', 'Panel')</div>
        <div class="topbar-badge">
            <div class="status-dot"></div>
            <i class="fa-solid fa-shield-halved" style="font-size:11px;"></i>
            Güvenli Bağlantı
        </div>
    </div>

    {{-- Flash Mesajları --}}
    @if($errors->any())
        <div class="flash-wrapper">
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-xmark"></i>
                {{ $errors->first() }}
            </div>
        </div>
    @endif
    @if(session('success'))
        <div class="flash-wrapper">
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="flash-wrapper">
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-xmark"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Sayfa içeriği --}}
    <div class="page-content">
        @yield('content')
    </div>
</main>

{{-- Overlay --}}
<div id="overlay" onclick="toggleSidebar()"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); backdrop-filter:blur(2px); z-index:150; transition:opacity 0.3s;"></div>

<script>
const CSRF    = document.querySelector('meta[name="csrf-token"]').content;
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

function toggleSidebar() {
    const open = sidebar.classList.toggle('open');
    overlay.style.display = open ? 'block' : 'none';
}
</script>

@stack('scripts')
</body>
</html>
