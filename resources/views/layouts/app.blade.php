<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NovaBanka - @yield('title', 'Dijital Bankacılık')</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --navy:      #0A0E1A;
            --navy-2:    #111827;
            --navy-3:    #1C2535;
            --navy-card: #162032;
            --accent:    #00E5B4;
            --accent-2:  #00B8FF;
            --gold:      #F5C842;
            --danger:    #FF4D6A;
            --text:      #E8EDF5;
            --text-muted:#7A8BA3;
            --border:    rgba(255,255,255,0.06);
            --shadow:    0 8px 32px rgba(0,0,0,0.4);
            --radius:    16px;
            --font:      'Sora', sans-serif;
            --mono:      'Space Mono', monospace;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: var(--font);
            background: var(--navy);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: var(--navy-2);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            left: 0; top: 0;
            z-index: 100;
            transition: transform 0.3s;
        }

        .sidebar-logo {
            padding: 28px 24px 20px;
            border-bottom: 1px solid var(--border);
        }

        .logo-mark {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 700;
            color: var(--navy);
        }

        .logo-text { font-size: 20px; font-weight: 700; letter-spacing: -0.5px; }
        .logo-sub  { font-size: 10px; color: var(--text-muted); letter-spacing: 2px; text-transform: uppercase; margin-top: 1px; }

        .sidebar-nav {
            flex: 1;
            padding: 20px 12px;
            overflow-y: auto;
        }

        .nav-section-label {
            font-size: 10px;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 12px 12px 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 12px;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            margin-bottom: 2px;
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.05);
            color: var(--text);
        }

        .nav-item.active {
            background: rgba(0,229,180,0.12);
            color: var(--accent);
        }

        .nav-icon {
            width: 36px; height: 36px;
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .nav-item.active .nav-icon {
            background: rgba(0,229,180,0.15);
        }

        /* Sidebar User Info */
        .sidebar-user {
            padding: 16px;
            border-top: 1px solid var(--border);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--navy-3);
            border-radius: 12px;
        }

        .user-avatar {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            color: var(--navy);
            flex-shrink: 0;
        }

        .user-name  { font-size: 13px; font-weight: 600; }
        .user-email { font-size: 11px; color: var(--text-muted); }

        .logout-btn {
            background: none; border: none;
            color: var(--text-muted); cursor: pointer;
            font-size: 16px; margin-left: auto;
            padding: 4px; border-radius: 6px;
            transition: color 0.2s;
        }
        .logout-btn:hover { color: var(--danger); }

        /* ── Main Content ── */
        .main {
            flex: 1;
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Bar */
        .topbar {
            height: 70px;
            background: rgba(10,14,26,0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 32px;
            position: sticky;
            top: 0; z-index: 90;
            gap: 16px;
        }

        .topbar-title {
            flex: 1;
            font-size: 20px;
            font-weight: 600;
        }

        .topbar-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(0,229,180,0.1);
            border: 1px solid rgba(0,229,180,0.2);
            border-radius: 50px;
            font-size: 12px;
            color: var(--accent);
            font-weight: 500;
        }

        .status-dot {
            width: 8px; height: 8px;
            background: var(--accent);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%,100%  { opacity: 1; }
            50%      { opacity: 0.4; }
        }

        /* Page Content */
        .page-content {
            padding: 32px;
            flex: 1;
        }

        /* ── Cards / Boxes ── */
        .card {
            background: var(--navy-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
        }

        .card-sm { padding: 16px; }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }

        /* ── Stat Card ── */
        .stat-card {
            background: var(--navy-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px 24px;
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            font-family: var(--mono);
            margin: 8px 0 4px;
            letter-spacing: -1px;
        }

        .stat-change {
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .change-up   { color: var(--accent); }
        .change-down { color: var(--danger); }

        /* ── Buttons ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 12px;
            font-family: var(--font);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), #00c49a);
            color: var(--navy);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(0,229,180,0.3); }

        .btn-secondary {
            background: rgba(255,255,255,0.06);
            color: var(--text);
            border: 1px solid var(--border);
        }
        .btn-secondary:hover { background: rgba(255,255,255,0.1); }

        .btn-danger {
            background: rgba(255,77,106,0.15);
            color: var(--danger);
            border: 1px solid rgba(255,77,106,0.3);
        }
        .btn-danger:hover { background: rgba(255,77,106,0.25); }

        .btn-sm { padding: 8px 14px; font-size: 12px; border-radius: 8px; }
        .btn-full { width: 100%; justify-content: center; }

        /* ── Form Elements ── */
        .form-group { margin-bottom: 20px; }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            background: var(--navy-3);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px 16px;
            color: var(--text);
            font-family: var(--font);
            font-size: 14px;
            transition: border-color 0.2s;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(0,229,180,0.1);
        }

        .form-control::placeholder { color: var(--text-muted); }

        select.form-control option { background: var(--navy-2); }

        /* ── Alerts ── */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: rgba(0,229,180,0.1);
            border: 1px solid rgba(0,229,180,0.3);
            color: var(--accent);
        }

        .alert-error {
            background: rgba(255,77,106,0.1);
            border: 1px solid rgba(255,77,106,0.3);
            color: var(--danger);
        }

        /* ── Table ── */
        .table { width: 100%; border-collapse: collapse; }

        .table th {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .table td {
            padding: 14px 16px;
            font-size: 14px;
            border-bottom: 1px solid var(--border);
        }

        .table tr:last-child td { border-bottom: none; }
        .table tbody tr:hover { background: rgba(255,255,255,0.02); }

        /* ── Badge ── */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .badge-success { background: rgba(0,229,180,0.15); color: var(--accent); }
        .badge-danger  { background: rgba(255,77,106,0.15); color: var(--danger); }
        .badge-info    { background: rgba(0,184,255,0.15);  color: var(--accent-2); }

        /* ── Section Header ── */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
        }

        .section-sub {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* ── Mobile Burger ── */
        .burger {
            display: none;
            background: none; border: none;
            color: var(--text); cursor: pointer;
            font-size: 22px;
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main { margin-left: 0; }
            .burger { display: block; }
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
            .page-content { padding: 20px; }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- ===== SIDEBAR ===== --}}
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <a href="{{ route('dashboard') }}" class="logo-mark">
            <div class="logo-icon">N</div>
            <div>
                <div class="logo-text">NovaBanka</div>
                <div class="logo-sub">Dijital Bankacılık</div>
            </div>
        </a>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Ana Menü</div>

        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <div class="nav-icon">🏠</div>
            <span>Genel Bakış</span>
        </a>

        <div class="nav-section-label" style="margin-top:8px">İşlemler</div>

        <a href="{{ route('transfer.index') }}" class="nav-item {{ request()->routeIs('transfer.*') ? 'active' : '' }}">
            <div class="nav-icon">↗️</div>
            <span>Para Gönder / Al</span>
        </a>

        <a href="{{ route('cards.index') }}" class="nav-item {{ request()->routeIs('cards.*') ? 'active' : '' }}">
            <div class="nav-icon">💳</div>
            <span>Kartlarım</span>
        </a>

        <a href="{{ route('exchange.index') }}" class="nav-item {{ request()->routeIs('exchange.*') ? 'active' : '' }}">
            <div class="nav-icon">📈</div>
            <span>Borsa & Döviz</span>
        </a>

        <div class="nav-section-label" style="margin-top:8px">Hesap</div>

        <a href="#" class="nav-item">
            <div class="nav-icon">📊</div>
            <span>Hesap Hareketleri</span>
        </a>

        <a href="#" class="nav-item">
            <div class="nav-icon">⚙️</div>
            <span>Ayarlar</span>
        </a>
    </nav>

    <div class="sidebar-user">
        <div class="user-card">
            <div class="user-avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->surname, 0, 1)) }}
            </div>
            <div>
                <div class="user-name">{{ auth()->user()->full_name }}</div>
                <div class="user-email">{{ auth()->user()->email }}</div>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin-left:auto">
                @csrf
                <button type="submit" class="logout-btn" title="Çıkış Yap">⏻</button>
            </form>
        </div>
    </div>
</aside>

{{-- ===== ANA ALAN ===== --}}
<main class="main">
    {{-- Top Bar --}}
    <div class="topbar">
        <button class="burger" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
        <div class="topbar-title">@yield('title', 'Panel')</div>
        <div class="topbar-badge">
            <div class="status-dot"></div>
            Güvenli Bağlantı
        </div>
    </div>

    {{-- Flash Mesajları --}}
    @if($errors->any())
        <div style="padding: 0 32px; margin-top: 20px;">
            <div class="alert alert-error">✗ Lütfen kontrol edin: {{ $errors->first() }}</div>
        </div>
    @endif
    @if(session('success'))
        <div style="padding: 0 32px; margin-top: 20px;">
            <div class="alert alert-success">✓ {{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div style="padding: 0 32px; margin-top: 20px;">
            <div class="alert alert-error">✗ {{ session('error') }}</div>
        </div>
    @endif

    {{-- Sayfa içeriği buraya --}}
    <div class="page-content">
        @yield('content')
    </div>
</main>

{{-- Overlay (mobil sidebar) --}}
<div id="overlay" onclick="document.getElementById('sidebar').classList.remove('open')"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:99"></div>

<script>
// CSRF token tüm AJAX istekleri için
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// Sidebar overlay
const sidebar = document.getElementById('sidebar');
const overlay  = document.getElementById('overlay');
const observer = new MutationObserver(() => {
    overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
});
observer.observe(sidebar, { attributes: true, attributeFilter: ['class'] });
</script>

@stack('scripts')
</body>
</html>
