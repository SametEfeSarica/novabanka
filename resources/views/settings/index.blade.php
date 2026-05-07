@extends('layouts.app')

@section('title', 'Ayarlar')

@push('styles')
<style>
    /* ── Settings Layout ── */
    .settings-layout {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 20px;
        align-items: flex-start;
    }

    @media (max-width: 768px) {
        .settings-layout { grid-template-columns: 1fr; }
        .settings-nav { display: flex; flex-wrap: wrap; gap: 6px; }
        .settings-nav-item { flex: 1; min-width: 120px; text-align: center; justify-content: center; }
    }

    /* ── Settings Sidebar Nav ── */
    .settings-nav {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 10px;
        position: sticky;
        top: 82px;
    }

    .settings-nav-item {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 11px 14px;
        border-radius: var(--radius-sm);
        cursor: pointer;
        font-size: 13.5px;
        font-weight: 500;
        color: var(--text-sub);
        transition: all 0.18s;
        text-decoration: none;
        border: 1px solid transparent;
    }

    .settings-nav-item:hover {
        background: var(--bg-hover);
        color: var(--text);
    }

    .settings-nav-item.active {
        background: var(--accent-dim);
        color: var(--accent);
        border-color: rgba(0,217,163,0.15);
    }

    .settings-nav-item .nav-ico {
        width: 30px; height: 30px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px;
        background: var(--bg-card-2);
        flex-shrink: 0;
    }

    .settings-nav-item.active .nav-ico {
        background: rgba(0,217,163,0.15);
        color: var(--accent);
    }

    /* ── Settings Panels ── */
    .settings-panel { display: none; }
    .settings-panel.active { display: block; }

    .panel-header {
        margin-bottom: 22px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }

    .panel-title {
        font-size: 17px;
        font-weight: 700;
        letter-spacing: -0.3px;
    }

    .panel-subtitle {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    /* ── Form Fields ── */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .form-grid.single { grid-template-columns: 1fr; }

    @media (max-width: 600px) {
        .form-grid { grid-template-columns: 1fr; }
    }

    .form-group { display: flex; flex-direction: column; gap: 7px; }
    .form-group.full { grid-column: 1 / -1; }

    .form-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .form-control {
        background: var(--bg-card-2);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        color: var(--text);
        font-family: var(--font);
        font-size: 14px;
        padding: 11px 14px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        width: 100%;
    }

    .form-control:focus {
        border-color: rgba(0,217,163,0.5);
        box-shadow: 0 0 0 3px rgba(0,217,163,0.08);
    }

    .form-control:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .form-control::placeholder { color: var(--text-muted); }

    .form-hint {
        font-size: 11.5px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    /* ── Avatar Section ── */
    .avatar-section {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 26px;
        padding-bottom: 22px;
        border-bottom: 1px solid var(--border);
    }

    .avatar-big {
        width: 72px; height: 72px;
        background: linear-gradient(135deg, var(--accent) 0%, #0090A8 100%);
        border-radius: 20px;
        display: flex; align-items: center; justify-content: center;
        font-size: 26px;
        font-weight: 700;
        color: #051A12;
        flex-shrink: 0;
        box-shadow: 0 4px 20px rgba(0,217,163,0.25);
    }

    /* ── Password Strength ── */
    .strength-bar {
        height: 4px;
        border-radius: 2px;
        background: var(--bg-card-2);
        margin-top: 8px;
        overflow: hidden;
    }

    .strength-fill {
        height: 100%;
        border-radius: 2px;
        transition: width 0.3s, background 0.3s;
    }

    .strength-label {
        font-size: 11.5px;
        margin-top: 5px;
        font-weight: 500;
    }

    /* ── Card Settings ── */
    .card-item {
        background: var(--bg-card-2);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 18px;
        margin-bottom: 14px;
        transition: border-color 0.2s;
    }

    .card-item:hover { border-color: var(--border-strong); }

    .card-item-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .card-visual {
        width: 100%;
        height: 88px;
        background: linear-gradient(135deg, #1a2a4a 0%, #0d1a33 100%);
        border-radius: var(--radius-sm);
        padding: 14px 16px;
        margin-bottom: 16px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .card-visual::before {
        content: '';
        position: absolute;
        top: -30px; right: -30px;
        width: 120px; height: 120px;
        background: rgba(0,217,163,0.06);
        border-radius: 50%;
    }

    .card-number-display {
        font-family: var(--mono);
        font-size: 15px;
        letter-spacing: 2px;
        color: rgba(255,255,255,0.85);
    }

    .card-bottom-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }

    .card-holder-display { font-size: 12px; color: rgba(255,255,255,0.6); }
    .card-expiry-display { font-family: var(--mono); font-size: 13px; color: rgba(255,255,255,0.8); }

    /* ── Toggle Switch ── */
    .toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid var(--border);
    }
    .toggle-row:last-child { border-bottom: none; }

    .toggle-info h4 { font-size: 14px; font-weight: 600; margin-bottom: 2px; }
    .toggle-info p  { font-size: 12px; color: var(--text-muted); }

    .toggle {
        position: relative;
        width: 44px; height: 24px;
        flex-shrink: 0;
    }

    .toggle input { opacity: 0; width: 0; height: 0; }

    .toggle-slider {
        position: absolute;
        inset: 0;
        background: var(--bg-card-2);
        border: 1px solid var(--border-strong);
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.25s;
    }

    .toggle-slider::before {
        content: '';
        position: absolute;
        width: 18px; height: 18px;
        left: 2px; top: 2px;
        background: var(--text-muted);
        border-radius: 50%;
        transition: all 0.25s;
    }

    .toggle input:checked + .toggle-slider {
        background: var(--accent-dim);
        border-color: var(--accent);
    }

    .toggle input:checked + .toggle-slider::before {
        transform: translateX(20px);
        background: var(--accent);
    }

    /* ── Limit Slider ── */
    .limit-slider {
        width: 100%;
        height: 4px;
        border-radius: 2px;
        background: var(--bg-card-2);
        outline: none;
        -webkit-appearance: none;
        cursor: pointer;
        margin-top: 4px;
    }

    .limit-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 20px; height: 20px;
        border-radius: 50%;
        background: var(--accent);
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0,217,163,0.4);
        border: 2px solid var(--bg-card);
    }

    /* ── Alert ── */
    .alert {
        border-radius: var(--radius-sm);
        padding: 12px 16px;
        font-size: 13.5px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 18px;
    }
    .alert-success { background: var(--accent-dim); border: 1px solid rgba(0,217,163,0.2); color: var(--accent); }
    .alert-danger  { background: var(--danger-dim);  border: 1px solid rgba(244,63,94,0.2);  color: var(--danger); }
    .alert-info    { background: var(--blue-dim);    border: 1px solid rgba(59,130,246,0.2);  color: var(--blue); }

    /* ── Security badges ── */
    .security-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 0;
        border-bottom: 1px solid var(--border);
    }
    .security-item:last-child { border-bottom: none; }

    .security-icon {
        width: 40px; height: 40px;
        border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')

{{-- ── Page Header ── --}}
<div style="margin-bottom:24px;">
    <h1 style="font-size:22px; font-weight:700; letter-spacing:-0.5px;">Ayarlar</h1>
    <p style="font-size:13.5px; color:var(--text-muted); margin-top:4px;">Hesap ve kart tercihlerinizi yönetin</p>
</div>

@if(session('success'))
<div class="alert alert-success">
    <i class="fa-solid fa-circle-check"></i>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
    <i class="fa-solid fa-circle-xmark"></i>
    {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="alert alert-danger">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
</div>
@endif

<div class="settings-layout">

    {{-- ── Left Nav ── --}}
    <nav class="settings-nav">
        <a href="#profil" onclick="switchTab('profil',this)" class="settings-nav-item active">
            <div class="nav-ico"><i class="fa-solid fa-user"></i></div>
            Kişisel Bilgiler
        </a>
        <a href="#sifre" onclick="switchTab('sifre',this)" class="settings-nav-item">
            <div class="nav-ico"><i class="fa-solid fa-lock"></i></div>
            Şifre Değiştir
        </a>
        <a href="#kartlar" onclick="switchTab('kartlar',this)" class="settings-nav-item">
            <div class="nav-ico"><i class="fa-solid fa-credit-card"></i></div>
            Kart Ayarları
        </a>
        <a href="#guvenlik" onclick="switchTab('guvenlik',this)" class="settings-nav-item">
            <div class="nav-ico"><i class="fa-solid fa-shield-halved"></i></div>
            Güvenlik
        </a>
        <a href="#bildirimler" onclick="switchTab('bildirimler',this)" class="settings-nav-item">
            <div class="nav-ico"><i class="fa-solid fa-bell"></i></div>
            Bildirimler
        </a>
    </nav>

    {{-- ── Right Content ── --}}
    <div>

        {{-- ════════════════════════════════════════
             PANEL 1 — KİŞİSEL BİLGİLER
        ════════════════════════════════════════ --}}
        <div class="card settings-panel active" id="panel-profil">
            <div class="panel-header">
                <div class="panel-title">Kişisel Bilgiler</div>
                <div class="panel-subtitle">Ad, soyad, iletişim ve demografik bilgilerinizi güncelleyin</div>
            </div>

            {{-- Avatar --}}
            <div class="avatar-section">
                <div class="avatar-big">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->surname, 0, 1)) }}
                </div>
                <div>
                    <div style="font-weight:600; font-size:16px;">{{ auth()->user()->full_name }}</div>
                    <div style="font-size:13px; color:var(--text-muted); margin-top:3px;">{{ auth()->user()->email }}</div>
                    <div style="font-size:12px; color:var(--text-muted); margin-top:2px; font-family:var(--mono);">
                        {{ auth()->user()->primaryAccount()?->iban ?? 'IBAN mevcut değil' }}
                    </div>
                </div>
            </div>

            <form action="{{ route('settings.updateProfile') }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="form-grid" style="margin-bottom:20px;">
                    <div class="form-group">
                        <label class="form-label">Ad</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', auth()->user()->name) }}"
                               placeholder="Adınız" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Soyad</label>
                        <input type="text" name="surname" class="form-control"
                               value="{{ old('surname', auth()->user()->surname) }}"
                               placeholder="Soyadınız" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">E-posta Adresi</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email', auth()->user()->email) }}"
                               placeholder="mail@ornek.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Telefon Numarası</label>
                        <input type="tel" name="phone" class="form-control"
                               value="{{ old('phone', auth()->user()->phone) }}"
                               placeholder="+90 5XX XXX XX XX">
                    </div>
                    <div class="form-group">
                        <label class="form-label">TC Kimlik No</label>
                        <input type="text" name="tc_no" class="form-control"
                               value="{{ auth()->user()->tc_no }}"
                               disabled>
                        <span class="form-hint"><i class="fa-solid fa-lock" style="font-size:10px;"></i> TC Kimlik değiştirilemez</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Doğum Tarihi</label>
                        <input type="date" name="birth_date" class="form-control"
                               value="{{ auth()->user()->birth_date?->format('Y-m-d') }}">
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk" style="font-size:12px;"></i>
                        Değişiklikleri Kaydet
                    </button>
                </div>
            </form>
        </div>

        {{-- ════════════════════════════════════════
             PANEL 2 — ŞİFRE DEĞİŞTİR
        ════════════════════════════════════════ --}}
        <div class="card settings-panel" id="panel-sifre">
            <div class="panel-header">
                <div class="panel-title">Şifre Değiştir</div>
                <div class="panel-subtitle">Hesap güvenliğiniz için güçlü bir şifre belirleyin</div>
            </div>

            <div class="alert alert-info">
                <i class="fa-solid fa-circle-info"></i>
                Şifrenizi değiştirdikten sonra tüm cihazlardan çıkış yapılacaktır.
            </div>

            <form action="{{ route('settings.updatePassword') }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="form-grid single" style="max-width:480px; margin-bottom:24px;">
                    <div class="form-group">
                        <label class="form-label">Mevcut Şifre</label>
                        <div style="position:relative;">
                            <input type="password" name="current_password" id="currentPwd" class="form-control"
                                   placeholder="Mevcut şifrenizi girin" required style="padding-right:42px;">
                            <button type="button" onclick="togglePwd('currentPwd',this)"
                                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:14px;">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Yeni Şifre</label>
                        <div style="position:relative;">
                            <input type="password" name="password" id="newPwd" class="form-control"
                                   placeholder="En az 8 karakter" required style="padding-right:42px;"
                                   oninput="checkStrength(this.value)">
                            <button type="button" onclick="togglePwd('newPwd',this)"
                                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:14px;">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill" style="width:0%; background:var(--danger);"></div>
                        </div>
                        <div class="strength-label" id="strengthLabel" style="color:var(--text-muted);">
                            Şifre gücü gösterilecek
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Yeni Şifre (Tekrar)</label>
                        <div style="position:relative;">
                            <input type="password" name="password_confirmation" id="confirmPwd" class="form-control"
                                   placeholder="Şifrenizi tekrar girin" required style="padding-right:42px;"
                                   oninput="checkMatch()">
                            <button type="button" onclick="togglePwd('confirmPwd',this)"
                                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:14px;">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-hint" id="matchHint"></div>
                    </div>
                </div>

                {{-- Şifre Gereksinimleri --}}
                <div style="background:var(--bg-card-2); border:1px solid var(--border); border-radius:var(--radius-sm); padding:14px 16px; margin-bottom:22px; max-width:480px;">
                    <div style="font-size:12px; font-weight:600; color:var(--text-muted); margin-bottom:10px; text-transform:uppercase; letter-spacing:0.5px;">Şifre Gereksinimleri</div>
                    <div style="display:flex; flex-direction:column; gap:6px;">
                        <div id="req-len"   class="req-item"><i class="fa-solid fa-circle req-dot" style="font-size:6px;"></i> En az 8 karakter</div>
                        <div id="req-upper" class="req-item"><i class="fa-solid fa-circle req-dot" style="font-size:6px;"></i> En az 1 büyük harf</div>
                        <div id="req-lower" class="req-item"><i class="fa-solid fa-circle req-dot" style="font-size:6px;"></i> En az 1 küçük harf</div>
                        <div id="req-num"   class="req-item"><i class="fa-solid fa-circle req-dot" style="font-size:6px;"></i> En az 1 rakam</div>
                        <div id="req-sym"   class="req-item"><i class="fa-solid fa-circle req-dot" style="font-size:6px;"></i> En az 1 özel karakter (!@#$)</div>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; max-width:480px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-key" style="font-size:12px;"></i>
                        Şifreyi Güncelle
                    </button>
                </div>
            </form>
        </div>

        {{-- ════════════════════════════════════════
             PANEL 3 — KART AYARLARI
        ════════════════════════════════════════ --}}
        <div class="card settings-panel" id="panel-kartlar">
            <div class="panel-header">
                <div class="panel-title">Kart Ayarları</div>
                <div class="panel-subtitle">Kartlarınızın limitlerini ve özelliklerini yönetin</div>
            </div>

            @forelse(auth()->user()->cards as $card)
            <div class="card-item">
                {{-- Kart Görseli --}}
                <div class="card-visual">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <span style="font-size:11px; color:rgba(255,255,255,0.5); text-transform:uppercase; letter-spacing:1px;">
                            {{ ucfirst($card->card_type ?? 'Sanal') }} Kart
                        </span>
                        <span style="font-size:13px; font-weight:700; color:rgba(255,255,255,0.8);">
                            {{ strtoupper($card->card_brand ?? 'VISA') }}
                        </span>
                    </div>
                    <div>
                        <div class="card-number-display">{{ $card->masked_number }}</div>
                        <div class="card-bottom-row" style="margin-top:4px;">
                            <div class="card-holder-display">{{ strtoupper(auth()->user()->full_name) }}</div>
                            <div class="card-expiry-display">{{ $card->expiry_date }}</div>
                        </div>
                    </div>
                </div>

                {{-- Kart Durumu --}}
                <div class="card-item-header">
                    <div>
                        <div style="font-size:14px; font-weight:600;">{{ $card->masked_number }}</div>
                        <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">
                            @if($card->is_frozen)
                                <span style="color:var(--blue);"><i class="fa-solid fa-snowflake" style="font-size:10px;"></i> Dondurulmuş</span>
                            @elseif($card->is_active)
                                <span style="color:var(--accent);"><i class="fa-solid fa-circle-check" style="font-size:10px;"></i> Aktif</span>
                            @else
                                <span style="color:var(--danger);"><i class="fa-solid fa-circle-xmark" style="font-size:10px;"></i> Pasif</span>
                            @endif
                        </div>
                    </div>
                    <form action="{{ route('cards.freeze', $card) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn {{ $card->is_frozen ? 'btn-secondary' : 'btn-secondary' }}" style="font-size:12px; padding:7px 14px;">
                            <i class="fa-solid {{ $card->is_frozen ? 'fa-lock-open' : 'fa-snowflake' }}" style="font-size:11px;"></i>
                            {{ $card->is_frozen ? 'Dondurma Kaldır' : 'Kartı Dondur' }}
                        </button>
                    </form>
                </div>

                {{-- Harcama Limiti --}}
                <form action="{{ route('cards.limit', $card) }}" method="POST" style="margin-bottom:16px;">
                    @csrf
                    <div style="margin-bottom:10px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                            <label class="form-label" style="margin:0;">Günlük Harcama Limiti</label>
                            <span style="font-family:var(--mono); font-size:14px; font-weight:700; color:var(--accent);" id="limitVal-{{ $card->id }}">
                                ₺{{ number_format($card->spending_limit, 0, ',', '.') }}
                            </span>
                        </div>
                        <input type="range" name="spending_limit" class="limit-slider"
                               min="500" max="100000" step="500"
                               value="{{ $card->spending_limit }}"
                               oninput="updateLimitDisplay({{ $card->id }}, this.value)"
                               id="limitSlider-{{ $card->id }}">
                        <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--text-muted); margin-top:5px;">
                            <span>₺500</span>
                            <span>₺100.000</span>
                        </div>
                    </div>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        @foreach([1000, 5000, 10000, 25000, 50000] as $preset)
                        <button type="button" onclick="setLimit({{ $card->id }}, {{ $preset }})"
                                class="btn btn-secondary" style="font-size:11px; padding:5px 12px;">
                            ₺{{ number_format($preset, 0, ',', '.') }}
                        </button>
                        @endforeach
                    </div>
                    <div style="margin-top:14px; display:flex; justify-content:flex-end;">
                        <button type="submit" class="btn btn-primary" style="font-size:13px; padding:9px 18px;">
                            <i class="fa-solid fa-floppy-disk" style="font-size:11px;"></i>
                            Limiti Kaydet
                        </button>
                    </div>
                </form>

                {{-- Kart Özellikleri --}}
                <div style="background:var(--bg-base); border:1px solid var(--border); border-radius:var(--radius-sm); padding:6px 16px;">
                    <form action="{{ route('settings.updateCardFeatures', $card) }}" method="POST" id="featForm-{{ $card->id }}">
                        @csrf
                        @method('PATCH')

                        <div class="toggle-row">
                            <div class="toggle-info">
                                <h4>Online Alışveriş</h4>
                                <p>İnternetten yapılan ödemelere izin ver</p>
                            </div>
                            <label class="toggle">
                                <input type="checkbox" name="online_shopping" value="1"
                                       {{ $card->online_shopping ? 'checked' : '' }}
                                       onchange="document.getElementById('featForm-{{ $card->id }}').submit()">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="toggle-row">
                            <div class="toggle-info">
                                <h4>Temassız Ödeme</h4>
                                <p>NFC ile hızlı ödeme özelliği</p>
                            </div>
                            <label class="toggle">
                                <input type="checkbox" name="contactless" value="1"
                                       {{ $card->contactless ? 'checked' : '' }}
                                       onchange="document.getElementById('featForm-{{ $card->id }}').submit()">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </form>
                </div>
            </div>
            @empty
            <div style="text-align:center; padding:50px 20px;">
                <div style="width:64px;height:64px;background:var(--bg-card-2);border:1px solid var(--border);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:26px;color:var(--text-muted);margin:0 auto 16px;">
                    <i class="fa-solid fa-credit-card"></i>
                </div>
                <div style="font-size:15px; font-weight:600; margin-bottom:6px;">Henüz kartınız yok</div>
                <div style="font-size:13px; color:var(--text-muted); margin-bottom:18px;">Kartlar sayfasından yeni kart oluşturabilirsiniz</div>
                <a href="{{ route('cards.index') }}" class="btn btn-primary" style="font-size:13px;">
                    <i class="fa-solid fa-plus" style="font-size:11px;"></i>
                    Kart Oluştur
                </a>
            </div>
            @endforelse
        </div>

        {{-- ════════════════════════════════════════
             PANEL 4 — GÜVENLİK
        ════════════════════════════════════════ --}}
        <div class="card settings-panel" id="panel-guvenlik">
            <div class="panel-header">
                <div class="panel-title">Güvenlik</div>
                <div class="panel-subtitle">Hesabınızı koruyun ve güvenlik ayarlarını yönetin</div>
            </div>

            <div class="security-item">
                <div class="security-icon" style="background:var(--accent-dim); color:var(--accent);">
                    <i class="fa-solid fa-shield-check"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-size:14px; font-weight:600; margin-bottom:3px;">İki Faktörlü Doğrulama (2FA)</div>
                    <div style="font-size:12.5px; color:var(--text-muted);">Giriş yaparken ek doğrulama kodu gerektirir</div>
                </div>
                <div>
                    @if(auth()->user()->two_factor_enabled ?? false)
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <button class="btn btn-secondary" style="font-size:12px; padding:7px 14px;" onclick="alert('Yakında aktif olacak')">
                            Etkinleştir
                        </button>
                    @endif
                </div>
            </div>

            <div class="security-item">
                <div class="security-icon" style="background:var(--blue-dim); color:var(--blue);">
                    <i class="fa-solid fa-mobile-screen"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-size:14px; font-weight:600; margin-bottom:3px;">SMS Bildirimi</div>
                    <div style="font-size:12.5px; color:var(--text-muted);">Her işlemde SMS ile bilgilendir</div>
                </div>
                <form action="{{ route('settings.updateSecurity') }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="feature" value="sms_notification">
                    <label class="toggle">
                        <input type="checkbox" name="enabled" value="1" onchange="this.form.submit()">
                        <span class="toggle-slider"></span>
                    </label>
                </form>
            </div>

            <div class="security-item">
                <div class="security-icon" style="background:var(--gold-dim); color:var(--gold);">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-size:14px; font-weight:600; margin-bottom:3px;">Oturum Geçmişi</div>
                    <div style="font-size:12.5px; color:var(--text-muted);">
                        Son giriş: {{ auth()->user()->last_login_at ? \Carbon\Carbon::parse(auth()->user()->last_login_at)->format('d.m.Y H:i') : 'Bilinmiyor' }}
                    </div>
                </div>
                <button class="btn btn-secondary" style="font-size:12px; padding:7px 14px;" onclick="alert('Yakında aktif olacak')">
                    Görüntüle
                </button>
            </div>

            <div class="security-item">
                <div class="security-icon" style="background:var(--danger-dim); color:var(--danger);">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-size:14px; font-weight:600; margin-bottom:3px;">Tüm Cihazlardan Çıkış</div>
                    <div style="font-size:12.5px; color:var(--text-muted);">Tüm aktif oturumları sonlandır</div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="font-size:12px; padding:7px 14px; color:var(--danger); border-color:rgba(244,63,94,0.3);">
                        <i class="fa-solid fa-right-from-bracket" style="font-size:11px;"></i>
                        Çıkış Yap
                    </button>
                </form>
            </div>

            {{-- Hesap Silme --}}
            <div style="margin-top:24px; padding:18px; background:rgba(244,63,94,0.05); border:1px solid rgba(244,63,94,0.15); border-radius:var(--radius);">
                <div style="font-size:14px; font-weight:700; color:var(--danger); margin-bottom:6px;">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size:12px;"></i>
                    Tehlikeli Bölge
                </div>
                <div style="font-size:13px; color:var(--text-muted); margin-bottom:14px;">
                    Hesabınızı silmek geri alınamaz. Tüm verileriniz kalıcı olarak silinir.
                </div>
                <button class="btn" style="background:rgba(244,63,94,0.1); color:var(--danger); border:1px solid rgba(244,63,94,0.2); font-size:13px; padding:9px 18px;"
                        onclick="confirmDelete()">
                    <i class="fa-solid fa-trash" style="font-size:11px;"></i>
                    Hesabımı Sil
                </button>
            </div>
        </div>

        {{-- ════════════════════════════════════════
             PANEL 5 — BİLDİRİMLER
        ════════════════════════════════════════ --}}
        <div class="card settings-panel" id="panel-bildirimler">
            <div class="panel-header">
                <div class="panel-title">Bildirim Tercihleri</div>
                <div class="panel-subtitle">Hangi durumlarda bildirim almak istediğinizi seçin</div>
            </div>

            <form action="{{ route('settings.updateNotifications') }}" method="POST">
                @csrf
                @method('PATCH')

                @php
                $notifGroups = [
                    'İşlem Bildirimleri' => [
                        ['key' => 'notif_transfer',   'label' => 'Para Transferi',         'desc' => 'Gönderilen veya alınan havale/EFT işlemleri'],
                        ['key' => 'notif_payment',    'label' => 'Alışveriş Ödemesi',      'desc' => 'Kart ile yapılan alışveriş bildirimleri'],
                        ['key' => 'notif_deposit',    'label' => 'Para Yatırma/Çekme',     'desc' => 'Hesaba para girişi veya çıkışı'],
                    ],
                    'Güvenlik Bildirimleri' => [
                        ['key' => 'notif_login',      'label' => 'Yeni Giriş',             'desc' => 'Hesabınıza yeni bir cihazdan giriş yapılması'],
                        ['key' => 'notif_fail',       'label' => 'Başarısız Giriş',        'desc' => 'Hatalı şifre denemeleri'],
                        ['key' => 'notif_password',   'label' => 'Şifre Değişikliği',      'desc' => 'Şifreniz değiştirildiğinde bildirim al'],
                    ],
                    'Hesap Bildirimleri' => [
                        ['key' => 'notif_low_balance','label' => 'Düşük Bakiye Uyarısı',   'desc' => 'Bakiye 100 TL altına düştüğünde uyar'],
                        ['key' => 'notif_campaign',   'label' => 'Kampanya & Haberler',     'desc' => 'Nova Banka\'dan fırsatlar ve duyurular'],
                    ],
                ];
                @endphp

                @foreach($notifGroups as $groupName => $items)
                <div style="margin-bottom:24px;">
                    <div style="font-size:11.5px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.8px; margin-bottom:10px;">
                        {{ $groupName }}
                    </div>
                    <div style="background:var(--bg-card-2); border:1px solid var(--border); border-radius:var(--radius-sm); padding:4px 16px;">
                        @foreach($items as $item)
                        <div class="toggle-row">
                            <div class="toggle-info">
                                <h4>{{ $item['label'] }}</h4>
                                <p>{{ $item['desc'] }}</p>
                            </div>
                            <label class="toggle">
                                <input type="checkbox" name="{{ $item['key'] }}" value="1" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                <div style="display:flex; justify-content:flex-end;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk" style="font-size:12px;"></i>
                        Bildirimleri Kaydet
                    </button>
                </div>
            </form>
        </div>

    </div>{{-- /right --}}
</div>

@endsection

@push('scripts')
<style>
.req-item { font-size: 12.5px; color: var(--text-muted); display: flex; align-items: center; gap: 8px; transition: color 0.2s; }
.req-item.ok { color: var(--accent); }
.req-item.ok .req-dot { color: var(--accent); }
</style>
<script>
// ── Tab switching
function switchTab(id, el) {
    document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.settings-nav-item').forEach(a => a.classList.remove('active'));
    document.getElementById('panel-' + id).classList.add('active');
    el.classList.add('active');
    history.replaceState(null, '', '#' + id);
    return false;
}

// On load: restore tab from hash
window.addEventListener('load', () => {
    const hash = location.hash.replace('#', '');
    if (hash) {
        const el = document.querySelector(`[href="#${hash}"]`);
        if (el) switchTab(hash, el);
    }
});

// ── Password toggle
function togglePwd(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa-regular fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa-regular fa-eye';
    }
}

// ── Password strength
function checkStrength(val) {
    const reqs = {
        len:   val.length >= 8,
        upper: /[A-Z]/.test(val),
        lower: /[a-z]/.test(val),
        num:   /[0-9]/.test(val),
        sym:   /[^A-Za-z0-9]/.test(val),
    };

    ['len','upper','lower','num','sym'].forEach(k => {
        document.getElementById('req-' + k).classList.toggle('ok', reqs[k]);
    });

    const score = Object.values(reqs).filter(Boolean).length;
    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');

    const levels = [
        { w:'0%',   c:'var(--danger)',  t:'Çok Zayıf' },
        { w:'20%',  c:'var(--danger)',  t:'Zayıf' },
        { w:'40%',  c:'var(--gold)',    t:'Orta' },
        { w:'70%',  c:'#7EC8E3',        t:'İyi' },
        { w:'100%', c:'var(--accent)',  t:'Güçlü 🔐' },
    ];

    fill.style.width      = levels[score].w;
    fill.style.background = levels[score].c;
    label.style.color     = levels[score].c;
    label.textContent     = val.length ? levels[score].t : 'Şifre gücü gösterilecek';
}

// ── Password match check
function checkMatch() {
    const np = document.getElementById('newPwd').value;
    const cp = document.getElementById('confirmPwd').value;
    const hint = document.getElementById('matchHint');
    if (!cp) { hint.textContent = ''; return; }
    if (np === cp) {
        hint.style.color   = 'var(--accent)';
        hint.innerHTML     = '<i class="fa-solid fa-check" style="font-size:10px;"></i> Şifreler eşleşiyor';
    } else {
        hint.style.color   = 'var(--danger)';
        hint.innerHTML     = '<i class="fa-solid fa-xmark" style="font-size:10px;"></i> Şifreler eşleşmiyor';
    }
}

// ── Card limit slider
function updateLimitDisplay(cardId, val) {
    const formatted = Number(val).toLocaleString('tr-TR', { minimumFractionDigits: 0 });
    document.getElementById('limitVal-' + cardId).textContent = '₺' + formatted;
}

function setLimit(cardId, val) {
    const slider = document.getElementById('limitSlider-' + cardId);
    slider.value = val;
    updateLimitDisplay(cardId, val);
}

// ── Confirm delete
function confirmDelete() {
    if (confirm('Hesabınızı silmek istediğinize emin misiniz?\n\nBu işlem geri alınamaz ve tüm verileriniz silinir.')) {
        if (confirm('Son onay: Tüm işlem geçmişiniz, kartlarınız ve bakiyeniz silinecek. Devam edilsin mi?')) {
            // Gerçek projede: form submit ile delete endpoint'ine yönlendir
            alert('Bu özellik henüz aktif değil. Müşteri hizmetlerimizi arayın.');
        }
    }
}
</script>
@endpush
