{{--
    Nova Banka — Güvenli Ödeme Sayfası
    Dosya: novabanka_output/views/checkout/form.blade.php

    Değişkenler:
      $session — PaymentSession modeli (amount, currency, description, customer_name, order_id)
--}}
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Güvenli Ödeme — Nova Banka</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        /* ── Reset & Temel ──────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── CSS Değişkenleri ───────────────────────────────────────────── */
        :root {
            --nova-black:    #0a0a0f;
            --nova-deep:     #10101a;
            --nova-surface:  #161622;
            --nova-card:     #1c1c2e;
            --nova-border:   rgba(255,255,255,0.08);
            --nova-gold:     #c9a84c;
            --nova-gold-lt:  #e8c97a;
            --nova-gold-dim: rgba(201,168,76,0.15);
            --nova-green:    #2dbe7a;
            --nova-red:      #e05252;
            --nova-text:     #e8e8f0;
            --nova-muted:    #6b6b85;
            --nova-subtle:   rgba(255,255,255,0.04);
            --radius:        14px;
            --radius-sm:     8px;
            --shadow:        0 24px 64px rgba(0,0,0,0.6);
        }

        /* ── Layout ─────────────────────────────────────────────────────── */
        html, body {
            height: 100%;
            background: var(--nova-black);
            color: var(--nova-text);
            font-family: 'Syne', sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-image:
                radial-gradient(ellipse 80% 50% at 50% -10%, rgba(201,168,76,0.08) 0%, transparent 60%),
                radial-gradient(ellipse 40% 30% at 80% 80%, rgba(45,190,122,0.04) 0%, transparent 50%);
        }

        /* ── Topbar ──────────────────────────────────────────────────────── */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 40px;
            border-bottom: 1px solid var(--nova-border);
            background: rgba(10,10,15,0.8);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--nova-gold), var(--nova-gold-lt));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 0 20px rgba(201,168,76,0.3);
        }

        .logo-text {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.3px;
            color: var(--nova-text);
        }

        .logo-text span { color: var(--nova-gold); }

        .secure-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-family: 'DM Mono', monospace;
            color: var(--nova-green);
            background: rgba(45,190,122,0.08);
            border: 1px solid rgba(45,190,122,0.2);
            padding: 6px 12px;
            border-radius: 100px;
        }

        .secure-badge::before {
            content: '🔒';
            font-size: 11px;
        }

        /* ── Ana Konteyner ───────────────────────────────────────────────── */
        .page-wrap {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 24px;
            width: 100%;
            max-width: 860px;
            animation: fadeUp 0.5s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Sipariş Özeti (Sol Panel) ───────────────────────────────────── */
        .order-panel {
            background: var(--nova-surface);
            border: 1px solid var(--nova-border);
            border-radius: var(--radius);
            padding: 28px;
            height: fit-content;
            position: sticky;
            top: 90px;
        }

        .panel-label {
            font-size: 10px;
            font-family: 'DM Mono', monospace;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--nova-muted);
            margin-bottom: 20px;
        }

        .merchant-name {
            font-size: 15px;
            font-weight: 600;
            color: var(--nova-text);
            margin-bottom: 6px;
        }

        .order-desc {
            font-size: 13px;
            color: var(--nova-muted);
            margin-bottom: 24px;
            line-height: 1.5;
        }

        .amount-block {
            background: var(--nova-gold-dim);
            border: 1px solid rgba(201,168,76,0.25);
            border-radius: var(--radius-sm);
            padding: 16px 20px;
            margin-bottom: 24px;
        }

        .amount-label {
            font-size: 11px;
            font-family: 'DM Mono', monospace;
            color: var(--nova-gold);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .amount-value {
            font-size: 30px;
            font-weight: 800;
            color: var(--nova-gold-lt);
            letter-spacing: -1px;
        }

        .amount-value sup {
            font-size: 14px;
            font-weight: 600;
            vertical-align: super;
            margin-right: 2px;
        }

        .divider {
            height: 1px;
            background: var(--nova-border);
            margin: 20px 0;
        }

        .order-meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .meta-key {
            font-size: 12px;
            color: var(--nova-muted);
            font-family: 'DM Mono', monospace;
        }

        .meta-val {
            font-size: 12px;
            color: var(--nova-text);
            font-family: 'DM Mono', monospace;
            text-align: right;
        }

        .ssl-note {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 11px;
            color: var(--nova-muted);
            margin-top: 20px;
            line-height: 1.5;
        }

        .ssl-note-icon {
            font-size: 13px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* ── Ödeme Formu (Sağ Panel) ─────────────────────────────────────── */
        .payment-panel {
            background: var(--nova-surface);
            border: 1px solid var(--nova-border);
            border-radius: var(--radius);
            padding: 36px;
        }

        .form-title {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }

        .form-subtitle {
            font-size: 13px;
            color: var(--nova-muted);
            margin-bottom: 32px;
        }

        /* ── Kart Görseli ────────────────────────────────────────────────── */
        .card-preview {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 22px 26px;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
            height: 160px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.1);
        }

        .card-preview::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(201,168,76,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .card-chip {
            width: 38px;
            height: 28px;
            background: linear-gradient(135deg, var(--nova-gold) 0%, var(--nova-gold-lt) 100%);
            border-radius: 5px;
            margin-bottom: 14px;
            box-shadow: 0 2px 8px rgba(201,168,76,0.3);
        }

        .card-number-display {
            font-family: 'DM Mono', monospace;
            font-size: 18px;
            letter-spacing: 3px;
            color: rgba(255,255,255,0.9);
            margin-bottom: 14px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        }

        .card-bottom {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .card-holder-display, .card-expiry-display {
            font-family: 'DM Mono', monospace;
        }

        .card-field-label {
            font-size: 9px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.4);
            margin-bottom: 3px;
        }

        .card-field-value {
            font-size: 13px;
            color: rgba(255,255,255,0.9);
            text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        }

        .card-logo {
            font-size: 24px;
            position: absolute;
            bottom: 22px;
            right: 26px;
            opacity: 0.9;
        }

        /* ── Form Elemanları ─────────────────────────────────────────────── */
        .field-group {
            margin-bottom: 18px;
        }

        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
        }

        label {
            display: block;
            font-size: 11px;
            font-family: 'DM Mono', monospace;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--nova-muted);
            margin-bottom: 7px;
        }

        input {
            width: 100%;
            background: var(--nova-card);
            border: 1px solid var(--nova-border);
            border-radius: var(--radius-sm);
            color: var(--nova-text);
            font-family: 'DM Mono', monospace;
            font-size: 15px;
            padding: 13px 16px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            letter-spacing: 1px;
        }

        input::placeholder {
            color: var(--nova-muted);
            opacity: 0.5;
            letter-spacing: 0.5px;
        }

        input:focus {
            border-color: var(--nova-gold);
            box-shadow: 0 0 0 3px rgba(201,168,76,0.12);
        }

        input.is-error {
            border-color: var(--nova-red);
            box-shadow: 0 0 0 3px rgba(224,82,82,0.12);
        }

        .field-error {
            font-size: 11px;
            color: var(--nova-red);
            font-family: 'DM Mono', monospace;
            margin-top: 5px;
        }

        /* ── Submit Butonu ───────────────────────────────────────────────── */
        .pay-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--nova-gold) 0%, var(--nova-gold-lt) 100%);
            color: #0a0a0f;
            border: none;
            border-radius: var(--radius-sm);
            font-family: 'Syne', sans-serif;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.5px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 8px;
            box-shadow: 0 8px 24px rgba(201,168,76,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .pay-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 32px rgba(201,168,76,0.4);
        }

        .pay-btn:active {
            transform: translateY(0);
        }

        .pay-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .pay-btn-amount {
            background: rgba(0,0,0,0.15);
            padding: 3px 10px;
            border-radius: 100px;
            font-size: 13px;
        }

        /* ── Footer ──────────────────────────────────────────────────────── */
        footer {
            text-align: center;
            padding: 24px;
            font-size: 11px;
            font-family: 'DM Mono', monospace;
            color: var(--nova-muted);
            border-top: 1px solid var(--nova-border);
        }

        footer span { color: var(--nova-gold); }

        /* ── Hata Alert ──────────────────────────────────────────────────── */
        .alert-error {
            background: rgba(224,82,82,0.1);
            border: 1px solid rgba(224,82,82,0.3);
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            font-size: 13px;
            color: #f08080;
            margin-bottom: 20px;
        }

        /* ── Responsive ──────────────────────────────────────────────────── */
        @media (max-width: 720px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
            .order-panel {
                position: static;
            }
            .topbar {
                padding: 16px 20px;
            }
            .payment-panel {
                padding: 24px 20px;
            }
        }
    </style>
</head>
<body>

{{-- ── Üst Bar ───────────────────────────────────────────────────────────── --}}
<header class="topbar">
    <a href="#" class="logo">
        <div class="logo-icon">◆</div>
        <span class="logo-text">Nova<span>Banka</span></span>
    </a>
    <div class="secure-badge">256-bit SSL Korumalı Ödeme</div>
</header>

{{-- ── Ana İçerik ────────────────────────────────────────────────────────── --}}
<main class="page-wrap">
    <div class="checkout-grid">

        {{-- ── Sol: Sipariş Özeti ─────────────────────────────────────── --}}
        <aside class="order-panel">
            <p class="panel-label">Sipariş Özeti</p>

            <p class="merchant-name">{{ $session->posClient->name ?? 'Satıcı' }}</p>
            <p class="order-desc">{{ $session->description }}</p>

            <div class="amount-block">
                <p class="amount-label">Ödenecek Tutar</p>
                <p class="amount-value">
                    <sup>{{ $session->currency }}</sup>{{ number_format($session->amount, 2, ',', '.') }}
                </p>
            </div>

            <div class="divider"></div>

            <div class="order-meta-row">
                <span class="meta-key">Sipariş No</span>
                <span class="meta-val">#{{ $session->order_id }}</span>
            </div>
            <div class="order-meta-row">
                <span class="meta-key">Müşteri</span>
                <span class="meta-val">{{ $session->customer_name }}</span>
            </div>
            <div class="order-meta-row">
                <span class="meta-key">Son Geçerlilik</span>
                <span class="meta-val">{{ $session->expires_at->format('H:i') }}</span>
            </div>

            <div class="ssl-note">
                <span class="ssl-note-icon">🛡</span>
                <span>Kart bilgileriniz Nova Banka güvenlik altyapısıyla şifrelenir, üçüncü taraflarla paylaşılmaz.</span>
            </div>
        </aside>

        {{-- ── Sağ: Ödeme Formu ───────────────────────────────────────── --}}
        <section class="payment-panel">
            <h1 class="form-title">Ödeme Bilgileri</h1>
            <p class="form-subtitle">Kart bilgilerinizi girerek ödemeyi tamamlayın.</p>

            {{-- Genel hata mesajı --}}
            @if(session('error'))
                <div class="alert-error">⚠ {{ session('error') }}</div>
            @endif

            {{-- Kart Önizlemesi --}}
            <div class="card-preview">
                <div class="card-chip"></div>
                <div class="card-number-display" id="preview-number">•••• •••• •••• ••••</div>
                <div class="card-bottom">
                    <div class="card-holder-display">
                        <div class="card-field-label">Kart Sahibi</div>
                        <div class="card-field-value" id="preview-holder">AD SOYAD</div>
                    </div>
                    <div class="card-expiry-display">
                        <div class="card-field-label">Son Kullanma</div>
                        <div class="card-field-value" id="preview-expiry">AA/YY</div>
                    </div>
                </div>
                <div class="card-logo">💳</div>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('checkout.process', $session->token) }}" id="payment-form" novalidate>
                @csrf

                {{-- Kart Numarası --}}
                <div class="field-group">
                    <label for="card_number">Kart Numarası</label>
                    <input
                        type="text"
                        id="card_number"
                        name="card_number"
                        inputmode="numeric"
                        maxlength="19"
                        placeholder="0000 0000 0000 0000"
                        autocomplete="cc-number"
                        class="{{ $errors->has('card_number') ? 'is-error' : '' }}"
                    >
                    @error('card_number')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kart Sahibi --}}
                <div class="field-group">
                    <label for="card_holder">Kart Sahibinin Adı</label>
                    <input
                        type="text"
                        id="card_holder"
                        name="card_holder"
                        maxlength="60"
                        placeholder="AD SOYAD"
                        autocomplete="cc-name"
                        style="letter-spacing: 1.5px; text-transform: uppercase;"
                        class="{{ $errors->has('card_holder') ? 'is-error' : '' }}"
                        value="{{ old('card_holder') }}"
                    >
                    @error('card_holder')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- SKT ve CVV --}}
                <div class="field-group">
                    <div class="field-row">
                        <div>
                            <label for="expiry_month">Ay</label>
                            <input
                                type="text"
                                id="expiry_month"
                                name="expiry_month"
                                inputmode="numeric"
                                maxlength="2"
                                placeholder="AA"
                                autocomplete="cc-exp-month"
                                class="{{ $errors->has('expiry_month') ? 'is-error' : '' }}"
                                value="{{ old('expiry_month') }}"
                            >
                        </div>
                        <div>
                            <label for="expiry_year">Yıl</label>
                            <input
                                type="text"
                                id="expiry_year"
                                name="expiry_year"
                                inputmode="numeric"
                                maxlength="4"
                                placeholder="YYYY"
                                autocomplete="cc-exp-year"
                                class="{{ $errors->has('expiry_year') ? 'is-error' : '' }}"
                                value="{{ old('expiry_year') }}"
                            >
                        </div>
                        <div>
                            <label for="cvv">CVV</label>
                            <input
                                type="password"
                                id="cvv"
                                name="cvv"
                                inputmode="numeric"
                                maxlength="4"
                                placeholder="•••"
                                autocomplete="cc-csc"
                                class="{{ $errors->has('cvv') ? 'is-error' : '' }}"
                            >
                        </div>
                    </div>
                    @if($errors->has('expiry_month') || $errors->has('expiry_year') || $errors->has('cvv'))
                        <p class="field-error">{{ $errors->first('expiry_month') ?? $errors->first('expiry_year') ?? $errors->first('cvv') }}</p>
                    @endif
                </div>

                <button type="submit" class="pay-btn" id="pay-btn">
                    <span>Güvenli Öde</span>
                    <span class="pay-btn-amount">{{ $session->currency }} {{ number_format($session->amount, 2, ',', '.') }}</span>
                </button>

            </form>
        </section>

    </div>
</main>

<footer>
    <span>Nova Banka</span> Güvenli Ödeme Altyapısı &nbsp;·&nbsp;
    TLS 1.3 Şifreli Bağlantı &nbsp;·&nbsp;
    PCI DSS Uyumlu
</footer>

{{-- ── JavaScript: Kart Önizleme & Maskeleme ─────────────────────────────── --}}
<script>
(function () {
    const numInput    = document.getElementById('card_number');
    const holderInput = document.getElementById('card_holder');
    const monthInput  = document.getElementById('expiry_month');
    const yearInput   = document.getElementById('expiry_year');
    const form        = document.getElementById('payment-form');
    const btn         = document.getElementById('pay-btn');

    const previewNum    = document.getElementById('preview-number');
    const previewHolder = document.getElementById('preview-holder');
    const previewExpiry = document.getElementById('preview-expiry');

    // Kart numarası — 4'lü gruplama
    numInput.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 16);
        this.value = v.match(/.{1,4}/g)?.join(' ') ?? v;

        const padded = v.padEnd(16, '•');
        previewNum.textContent = padded.match(/.{1,4}/g).join(' ');
    });

    // Kart sahibi
    holderInput.addEventListener('input', function () {
        previewHolder.textContent = this.value.toUpperCase() || 'AD SOYAD';
    });

    // SKT güncelle
    function updateExpiry() {
        const m = monthInput.value || 'AA';
        const y = yearInput.value.slice(-2) || 'YY';
        previewExpiry.textContent = `${m}/${y}`;
    }
    monthInput.addEventListener('input', updateExpiry);
    yearInput.addEventListener('input', updateExpiry);

    // Form submit — çift tıklamayı önle
    form.addEventListener('submit', function () {
        btn.disabled = true;
        btn.innerHTML = '<span>İşlem yapılıyor...</span>';
    });

    // Sadece rakam girişi zorlama
    [monthInput, yearInput].forEach(el => {
        el.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
        });
    });
})();
</script>

</body>
</html>
