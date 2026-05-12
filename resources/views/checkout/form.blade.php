{{--
    Nova Banka — Güvenli Ödeme Sayfası (Yeniden Tasarım)
    Dosya: resources/views/checkout/form.blade.php
--}}
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Güvenli Ödeme — Nova Banka</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:           #080b12;
            --surface:      rgba(255,255,255,0.035);
            --border:       rgba(255,255,255,0.07);
            --border-light: rgba(255,255,255,0.12);
            --gold:         #d4a843;
            --gold-lt:      #f0c96a;
            --gold-dim:     rgba(212,168,67,0.12);
            --green:        #34d399;
            --red:          #f87171;
            --text:         #f1f1f3;
            --text-muted:   #5a5f72;
            --text-sub:     #8890a4;
            --radius:       16px;
            --radius-sm:    10px;
            --mono:         'JetBrains Mono', monospace;
        }

        html, body {
            min-height: 100%;
            background: var(--bg);
            color: var(--text);
            font-family: 'Outfit', sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            background-image:
                radial-gradient(ellipse 100% 60% at 20% -5%, rgba(212,168,67,0.06) 0%, transparent 55%),
                radial-gradient(ellipse 60% 40% at 85% 90%, rgba(52,211,153,0.04) 0%, transparent 50%);
        }

        /* Topbar */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 48px;
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(20px);
            background: rgba(8,11,18,0.7);
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 11px;
            text-decoration: none;
        }

        .logo-text {
            font-size: 17px;
            font-weight: 700;
            color: var(--text);
        }

        .logo-text em { font-style: normal; color: var(--gold); }

        .ssl-pill {
            display: flex;
            align-items: center;
            gap: 7px;
            font-family: var(--mono);
            font-size: 11px;
            color: var(--green);
            background: rgba(52,211,153,0.06);
            border: 1px solid rgba(52,211,153,0.15);
            padding: 7px 14px;
            border-radius: 100px;
        }

        .ssl-dot {
            width: 6px; height: 6px;
            background: var(--green);
            border-radius: 50%;
            box-shadow: 0 0 6px var(--green);
            animation: pulse 2s ease infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.4; }
        }

        /* Layout */
        .page {
            flex: 1;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 48px 24px 64px;
        }

        .grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
            width: 100%;
            max-width: 820px;
            animation: rise 0.45s cubic-bezier(0.22,1,0.36,1) both;
        }

        @keyframes rise {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            backdrop-filter: blur(12px);
        }

        /* Order Panel */
        .order-panel {
            padding: 28px 26px;
            position: sticky;
            top: 88px;
            height: fit-content;
        }

        .step-label {
            font-family: var(--mono);
            font-size: 10px;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 18px;
        }

        .merchant-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--gold-dim);
            border: 1px solid rgba(212,168,67,0.2);
            border-radius: 100px;
            padding: 5px 10px 5px 8px;
            margin-bottom: 10px;
        }

        .merchant-dot {
            width: 7px; height: 7px;
            background: var(--gold);
            border-radius: 50%;
        }

        .merchant-badge span {
            font-size: 11px;
            font-weight: 600;
            color: var(--gold-lt);
        }

        .order-desc {
            font-size: 13px;
            color: var(--text-sub);
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .amount-card {
            background: linear-gradient(135deg, rgba(212,168,67,0.1) 0%, rgba(212,168,67,0.04) 100%);
            border: 1px solid rgba(212,168,67,0.18);
            border-radius: var(--radius-sm);
            padding: 18px 20px;
            margin-bottom: 22px;
            position: relative;
            overflow: hidden;
        }

        .amount-card::after {
            content: '';
            position: absolute;
            top: -20px; right: -20px;
            width: 80px; height: 80px;
            background: radial-gradient(circle, rgba(212,168,67,0.15) 0%, transparent 70%);
        }

        .amount-tag {
            font-family: var(--mono);
            font-size: 10px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 6px;
        }

        .amount-num {
            font-size: 26px;
            font-weight: 800;
            color: var(--gold-lt);
            letter-spacing: -0.5px;
            line-height: 1;
        }

        .amount-num sup {
            font-size: 13px;
            font-weight: 600;
            vertical-align: super;
            margin-right: 3px;
            opacity: 0.8;
        }

        .sep { height: 1px; background: var(--border); margin: 18px 0; }

        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 9px;
        }

        .meta-k {
            font-family: var(--mono);
            font-size: 11px;
            color: var(--text-muted);
        }

        .meta-v {
            font-family: var(--mono);
            font-size: 11px;
            color: var(--text-sub);
            text-align: right;
            max-width: 160px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .trust-row {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-top: 20px;
            padding: 12px 14px;
            background: rgba(52,211,153,0.04);
            border: 1px solid rgba(52,211,153,0.1);
            border-radius: var(--radius-sm);
        }

        .trust-text {
            font-size: 11px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* Payment Panel */
        .pay-panel { padding: 36px 36px 40px; }

        .pay-title {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.4px;
            margin-bottom: 4px;
        }

        .pay-sub {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 30px;
        }

        /* Card Visual */
        .card-vis {
            height: 170px;
            border-radius: 16px;
            position: relative;
            overflow: hidden;
            margin-bottom: 30px;
            transition: transform 0.3s ease;
        }

        .card-vis:hover { transform: translateY(-2px); }

        .card-bg {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, #0d1b33 0%, #1a2744 40%, #0d2040 70%, #162035 100%);
        }

        .card-glow {
            position: absolute;
            top: -60px; right: -60px;
            width: 220px; height: 220px;
            background: radial-gradient(circle, rgba(212,168,67,0.18) 0%, transparent 60%);
            pointer-events: none;
        }

        .card-glow2 {
            position: absolute;
            bottom: -40px; left: -20px;
            width: 150px; height: 150px;
            background: radial-gradient(circle, rgba(99,102,241,0.12) 0%, transparent 60%);
            pointer-events: none;
        }

        .card-inner {
            position: absolute;
            inset: 0;
            padding: 22px 26px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-top { display: flex; justify-content: space-between; align-items: flex-start; }

        .card-circles { display: flex; }

        .cc1, .cc2 {
            width: 28px; height: 28px;
            border-radius: 50%;
        }

        .cc1 { background: #eb001b; margin-right: -10px; }
        .cc2 { background: #f79e1b; }

        .card-num {
            font-family: var(--mono);
            font-size: 17px;
            letter-spacing: 3px;
            color: rgba(255,255,255,0.92);
            text-shadow: 0 1px 3px rgba(0,0,0,0.4);
        }

        .card-footer { display: flex; justify-content: space-between; align-items: flex-end; }

        .cfl { display: flex; flex-direction: column; gap: 2px; }

        .cfl-label {
            font-family: var(--mono);
            font-size: 8px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.35);
        }

        .cfl-val {
            font-family: var(--mono);
            font-size: 13px;
            color: rgba(255,255,255,0.88);
            text-shadow: 0 1px 2px rgba(0,0,0,0.4);
        }

        /* Form */
        .field { margin-bottom: 16px; }

        .field-cols {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
        }

        .field label {
            display: block;
            font-family: var(--mono);
            font-size: 10px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 7px;
        }

        .input-wrap input {
            width: 100%;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            color: var(--text);
            font-family: var(--mono);
            font-size: 14px;
            padding: 13px 16px;
            outline: none;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
            -webkit-appearance: none;
        }

        .input-wrap input::placeholder {
            color: var(--text-muted);
            opacity: 0.6;
        }

        .input-wrap input:focus {
            background: rgba(212,168,67,0.04);
            border-color: rgba(212,168,67,0.45);
            box-shadow: 0 0 0 3px rgba(212,168,67,0.08);
        }

        .input-wrap input.err {
            border-color: rgba(248,113,113,0.5);
            background: rgba(248,113,113,0.04);
        }

        .field-err {
            font-family: var(--mono);
            font-size: 10px;
            color: var(--red);
            margin-top: 5px;
        }

        .alert-err {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(248,113,113,0.07);
            border: 1px solid rgba(248,113,113,0.2);
            border-radius: var(--radius-sm);
            padding: 13px 16px;
            font-size: 13px;
            color: #fca5a5;
            margin-bottom: 22px;
        }

        /* Pay Button */
        .pay-btn {
            width: 100%;
            margin-top: 10px;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            background: linear-gradient(135deg, #c49633 0%, var(--gold) 50%, #e8b84b 100%);
            box-shadow: 0 8px 30px rgba(212,168,67,0.28), inset 0 1px 0 rgba(255,255,255,0.2);
            transition: all 0.25s;
        }

        .pay-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 40px rgba(212,168,67,0.38), inset 0 1px 0 rgba(255,255,255,0.2);
        }

        .pay-btn:active { transform: translateY(0); }

        .pay-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .pay-btn-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 22px;
        }

        .pay-btn-label {
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 700;
            color: #1a1000;
        }

        .pay-btn-amount {
            font-family: var(--mono);
            font-size: 12px;
            font-weight: 600;
            color: #1a1000;
            background: rgba(0,0,0,0.12);
            padding: 4px 10px;
            border-radius: 100px;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 22px;
            font-family: var(--mono);
            font-size: 10px;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            border-top: 1px solid var(--border);
        }

        footer strong { color: var(--gold); font-weight: 500; }

        @media (max-width: 700px) {
            .grid { grid-template-columns: 1fr; }
            .order-panel { position: static; }
            .topbar { padding: 14px 20px; }
            .pay-panel { padding: 24px 20px 28px; }
            .field-cols { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

<header class="topbar">
    <a href="#" class="logo">
        <svg width="28" height="28" viewBox="0 0 28 28" fill="none" style="filter:drop-shadow(0 0 6px rgba(212,168,67,0.4))">
            <polygon points="14,1 27,8 27,20 14,27 1,20 1,8" fill="none" stroke="#d4a843" stroke-width="1.2" opacity="0.5"/>
            <polygon points="14,5 23,10.5 23,17.5 14,23 5,17.5 5,10.5" fill="rgba(212,168,67,0.1)" stroke="#d4a843" stroke-width="1"/>
            <circle cx="14" cy="14" r="3.5" fill="#d4a843"/>
        </svg>
        <span class="logo-text">Nova<em>Banka</em></span>
    </a>
    <div class="ssl-pill">
        <span class="ssl-dot"></span>
        256-bit SSL Korumalı
    </div>
</header>

<main class="page">
    <div class="grid">

        <aside class="panel order-panel">
            <p class="step-label">Sipariş Özeti</p>

            <div class="merchant-badge">
                <span class="merchant-dot"></span>
                <span>{{ $session->posClient->name ?? 'Satıcı' }}</span>
            </div>

            <p class="order-desc">{{ $session->description }}</p>

            <div class="amount-card">
                <p class="amount-tag">Ödenecek Tutar</p>
                <p class="amount-num">
                    <sup>{{ $session->currency }}</sup>{{ number_format($session->amount, 2, ',', '.') }}
                </p>
            </div>

            <div class="sep"></div>

            <div class="meta-row">
                <span class="meta-k">Sipariş No</span>
                <span class="meta-v">#{{ $session->order_id }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-k">Müşteri</span>
                <span class="meta-v">{{ $session->customer_name }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-k">Son Geçerlilik</span>
                <span class="meta-v">{{ $session->expires_at->format('H:i') }}</span>
            </div>

            <div class="trust-row">
                <span style="font-size:14px;flex-shrink:0;margin-top:1px">🛡</span>
                <span class="trust-text">Kart bilgileriniz Nova Banka güvenlik altyapısıyla şifrelenir; üçüncü taraflarla paylaşılmaz.</span>
            </div>
        </aside>

        <section class="panel pay-panel">
            <h1 class="pay-title">Ödeme Bilgileri</h1>
            <p class="pay-sub">Kartınızın bilgilerini girerek ödemeyi tamamlayın.</p>

            @if(session('error'))
                <div class="alert-err">⚠&nbsp; {{ session('error') }}</div>
            @endif

            {{-- Kart Önizlemesi --}}
            <div class="card-vis">
                <div class="card-bg"></div>
                <div class="card-glow"></div>
                <div class="card-glow2"></div>
                <div class="card-inner">
                    <div class="card-top">
                        <svg width="40" height="30" viewBox="0 0 40 30" fill="none">
                            <rect x="1" y="1" width="38" height="28" rx="4" fill="url(#cg)" stroke="rgba(255,255,255,0.15)" stroke-width="0.5"/>
                            <line x1="14" y1="1" x2="14" y2="29" stroke="rgba(0,0,0,0.25)" stroke-width="0.5"/>
                            <line x1="26" y1="1" x2="26" y2="29" stroke="rgba(0,0,0,0.25)" stroke-width="0.5"/>
                            <line x1="1" y1="11" x2="39" y2="11" stroke="rgba(0,0,0,0.25)" stroke-width="0.5"/>
                            <line x1="1" y1="19" x2="39" y2="19" stroke="rgba(0,0,0,0.25)" stroke-width="0.5"/>
                            <defs>
                                <linearGradient id="cg" x1="0" y1="0" x2="40" y2="30" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#d4a843"/>
                                    <stop offset="1" stop-color="#a07830"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="card-circles">
                            <div class="cc1"></div>
                            <div class="cc2"></div>
                        </div>
                    </div>
                    <div class="card-num" id="vis-num">•••• &nbsp; •••• &nbsp; •••• &nbsp; ••••</div>
                    <div class="card-footer">
                        <div class="cfl">
                            <div class="cfl-label">Kart Sahibi</div>
                            <div class="cfl-val" id="vis-holder">AD SOYAD</div>
                        </div>
                        <div class="cfl">
                            <div class="cfl-label">Son Kullanma</div>
                            <div class="cfl-val" id="vis-exp">AA/YY</div>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('checkout.process', $session->token) }}" id="pay-form" novalidate>
                @csrf
                {{-- Sunucuya giden temiz (boşuksuz) kart numarası --}}
                <input type="hidden" id="card_number" name="card_number" value="{{ old('card_number') }}">

                {{-- Görsel kart numarası input (boşluklu, sadece UI için) --}}
                <div class="field">
                    <label for="card_number_display">Kart Numarası</label>
                    <div class="input-wrap">
                        <input
                            type="text"
                            id="card_number_display"
                            inputmode="numeric"
                            maxlength="19"
                            placeholder="0000  0000  0000  0000"
                            autocomplete="cc-number"
                            class="{{ $errors->has('card_number') ? 'err' : '' }}"
                        >
                    </div>
                    @error('card_number')
                        <p class="field-err">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="card_holder">Kart Sahibinin Adı</label>
                    <div class="input-wrap">
                        <input
                            type="text"
                            id="card_holder"
                            name="card_holder"
                            maxlength="60"
                            placeholder="AD SOYAD"
                            autocomplete="cc-name"
                            style="text-transform:uppercase;letter-spacing:1.5px;"
                            class="{{ $errors->has('card_holder') ? 'err' : '' }}"
                            value="{{ old('card_holder') }}"
                        >
                    </div>
                    @error('card_holder')
                        <p class="field-err">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <div class="field-cols">
                        <div>
                            <label for="expiry_month">Son Kul. Ay</label>
                            <div class="input-wrap">
                                <input
                                    type="text"
                                    id="expiry_month"
                                    name="expiry_month"
                                    inputmode="numeric"
                                    maxlength="2"
                                    placeholder="AA"
                                    autocomplete="cc-exp-month"
                                    class="{{ $errors->has('expiry_month') ? 'err' : '' }}"
                                    value="{{ old('expiry_month') }}"
                                >
                            </div>
                        </div>
                        <div>
                            <label for="expiry_year">Yıl</label>
                            <div class="input-wrap">
                                <input
                                    type="text"
                                    id="expiry_year"
                                    name="expiry_year"
                                    inputmode="numeric"
                                    maxlength="4"
                                    placeholder="YYYY"
                                    autocomplete="cc-exp-year"
                                    class="{{ $errors->has('expiry_year') ? 'err' : '' }}"
                                    value="{{ old('expiry_year') }}"
                                >
                            </div>
                        </div>
                        <div>
                            <label for="cvv">CVV</label>
                            <div class="input-wrap">
                                <input
                                    type="password"
                                    id="cvv"
                                    name="cvv"
                                    inputmode="numeric"
                                    maxlength="4"
                                    placeholder="•••"
                                    autocomplete="cc-csc"
                                    class="{{ $errors->has('cvv') ? 'err' : '' }}"
                                >
                            </div>
                        </div>
                    </div>
                    @if($errors->has('expiry_month') || $errors->has('expiry_year') || $errors->has('cvv'))
                        <p class="field-err">{{ $errors->first('expiry_month') ?? $errors->first('expiry_year') ?? $errors->first('cvv') }}</p>
                    @endif
                </div>

                <button type="submit" class="pay-btn" id="pay-btn">
                    <div class="pay-btn-inner">
                        <span class="pay-btn-label">🔐 &nbsp;Güvenli Öde</span>
                        <span class="pay-btn-amount">{{ $session->currency }} {{ number_format($session->amount, 2, ',', '.') }}</span>
                    </div>
                </button>
            </form>
        </section>

    </div>
</main>

<footer>
    <strong>Nova Banka</strong> Güvenli Ödeme &nbsp;·&nbsp; TLS 1.3 &nbsp;·&nbsp; PCI DSS Uyumlu
</footer>

<script>
(function () {
    const display  = document.getElementById('card_number_display');
    const hidden   = document.getElementById('card_number');
    const holder   = document.getElementById('card_holder');
    const monthEl  = document.getElementById('expiry_month');
    const yearEl   = document.getElementById('expiry_year');
    const form     = document.getElementById('pay-form');
    const btn      = document.getElementById('pay-btn');

    const visNum    = document.getElementById('vis-num');
    const visHolder = document.getElementById('vis-holder');
    const visExp    = document.getElementById('vis-exp');

    // Kart numarası: görsel boşluklu, hidden temiz (sadece rakam)
    display.addEventListener('input', function () {
        const raw = this.value.replace(/\D/g, '').slice(0, 16);
        this.value = raw.match(/.{1,4}/g)?.join('  ') ?? raw;

        // Sunucuya gidecek hidden field
        hidden.value = raw;

        // Görsel güncelleme
        const padded = raw.padEnd(16, '•');
        const groups = padded.match(/.{1,4}/g);
        visNum.innerHTML = groups.join('&nbsp; &nbsp;');
    });

    holder.addEventListener('input', function () {
        visHolder.textContent = (this.value || 'AD SOYAD').toUpperCase();
    });

    function updateExp() {
        const m = monthEl.value || 'AA';
        const y = yearEl.value.slice(-2) || 'YY';
        visExp.textContent = m + '/' + y;
    }

    monthEl.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
        updateExp();
    });

    yearEl.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
        updateExp();
    });

    form.addEventListener('submit', function (e) {
        const raw = display.value.replace(/\D/g, '');
        hidden.value = raw;

        if (raw.length !== 16) {
            e.preventDefault();
            display.classList.add('err');
            display.focus();
            return;
        }

        btn.disabled = true;
        document.querySelector('.pay-btn-inner').innerHTML =
            '<span class="pay-btn-label">⏳ &nbsp;İşlem yapılıyor...</span>';
    });
})();
</script>

</body>
</html>
