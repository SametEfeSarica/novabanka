<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NovaBanka - Hesap Oluştur</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #0A0E1A; --navy-2: #111827; --navy-3: #1C2535;
            --accent: #00E5B4; --accent-2: #00B8FF;
            --text: #E8EDF5; --text-muted: #7A8BA3;
            --border: rgba(255,255,255,0.08);
            --font: 'Sora', sans-serif;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: var(--font); background: var(--navy); color: var(--text); min-height: 100vh; display: flex; }
        
        .auth-left { flex: 1; background: var(--navy-2); display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 60px; position: relative; overflow: hidden; }
        .auth-left::before { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, rgba(0,229,180,0.04) 0%, transparent 50%), radial-gradient(ellipse at 20% 50%, rgba(0,184,255,0.06) 0%, transparent 60%); }
        .auth-left::after { content: ''; position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 40px 40px; }
        .left-content { position: relative; z-index: 1; text-align: center; }
        .left-logo { width: 70px; height: 70px; background: linear-gradient(135deg, var(--accent), var(--accent-2)); border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 700; color: var(--navy); margin: 0 auto 24px; }
        .left-title { font-size: 36px; font-weight: 700; letter-spacing: -1px; margin-bottom: 12px; }
        .left-title span { color: var(--accent); }
        .left-desc { font-size: 16px; color: var(--text-muted); line-height: 1.6; max-width: 320px; }
        .features { margin-top: 48px; display: flex; flex-direction: column; gap: 16px; width: 100%; max-width: 360px; }
        .feature-item { display: flex; align-items: center; gap: 16px; padding: 16px 20px; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 14px; }
        .feature-icon { width: 44px; height: 44px; background: rgba(0,229,180,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
        .feature-text h4 { font-size: 14px; font-weight: 600; }
        .feature-text p { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
        
        .auth-right { width: 550px; display: flex; flex-direction: column; justify-content: center; padding: 40px 48px; overflow-y: auto; }
        .auth-form-title { font-size: 28px; font-weight: 700; margin-bottom: 8px; }
        .auth-form-sub { font-size: 14px; color: var(--text-muted); margin-bottom: 36px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 8px; }
        .input-wrap { position: relative; }
        .input-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 18px; pointer-events: none; }
        .form-control { width: 100%; background: var(--navy-3); border: 1px solid var(--border); border-radius: 14px; padding: 15px 16px 15px 48px; color: var(--text); font-family: var(--font); font-size: 14px; outline: none; transition: border-color 0.2s, box-shadow 0.2s; }
        .form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(0,229,180,0.1); }
        .form-control::placeholder { color: var(--text-muted); }
        .btn-login { width: 100%; padding: 16px; background: linear-gradient(135deg, var(--accent), #00c49a); color: var(--navy); font-family: var(--font); font-size: 16px; font-weight: 700; border: none; border-radius: 14px; cursor: pointer; transition: all 0.2s; margin-top: 10px; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(0,229,180,0.35); }
        .divider { display: flex; align-items: center; gap: 16px; margin: 28px 0; color: var(--text-muted); font-size: 12px; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }
        .register-link { text-align: center; font-size: 14px; color: var(--text-muted); }
        .register-link a { color: var(--accent); text-decoration: none; font-weight: 600; }
        .error-msg { background: rgba(255,77,106,0.1); border: 1px solid rgba(255,77,106,0.3); color: #FF4D6A; border-radius: 10px; padding: 12px 16px; font-size: 13px; margin-bottom: 20px; }
        
        @media (max-width: 900px) { .auth-left { display: none; } .auth-right { width: 100%; } }
    </style>
</head>
<body>
<div class="auth-left">
    <div class="left-content">
        <div class="left-logo">N</div>
        <h1 class="left-title">Nova<span>Banka</span></h1>
        <p class="left-desc">Güvenli, hızlı ve akıllı dijital bankacılık deneyiminize hoş geldiniz.</p>
        <div class="features">
            <div class="feature-item">
                <div class="feature-icon">🔒</div>
                <div class="feature-text">
                    <h4>256-bit Şifreleme</h4>
                    <p>Tüm verileriniz güvende</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">🚀</div>
                <div class="feature-text">
                    <h4>Ücretsiz Kayıt</h4>
                    <p>Hesap işletim ücreti yok</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="auth-right">
    <h2 class="auth-form-title">Aramıza Katıl 🚀</h2>
    <p class="auth-form-sub">Saniyeler içinde dijital banka hesabını aç</p>

    @if($errors->any())
        <div class="error-msg">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('register') }}" method="POST">
        @csrf

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px">
            <div class="form-group">
                <label class="form-label">Ad</label>
                <div class="input-wrap">
                    <span class="input-icon">👤</span>
                    <input type="text" name="name" class="form-control" placeholder="Ahmet" value="{{ old('name') }}" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Soyad</label>
                <div class="input-wrap">
                    <span class="input-icon">👤</span>
                    <input type="text" name="surname" class="form-control" placeholder="Yılmaz" value="{{ old('surname') }}" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">TC Kimlik No</label>
            <div class="input-wrap">
                <span class="input-icon">🪪</span>
                <input type="text" name="tc_no" class="form-control" placeholder="12345678901" maxlength="11" value="{{ old('tc_no') }}" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">E-posta</label>
            <div class="input-wrap">
                <span class="input-icon">✉️</span>
                <input type="email" name="email" class="form-control" placeholder="ornek@email.com" value="{{ old('email') }}" required>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px">
            <div class="form-group">
                <label class="form-label">Telefon</label>
                <div class="input-wrap">
                    <span class="input-icon">📱</span>
                    <input type="text" name="phone" class="form-control" placeholder="05551234567" maxlength="11" value="{{ old('phone') }}" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Doğum Tarihi</label>
                <div class="input-wrap">
                    <span class="input-icon">📅</span>
                    <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date') }}" required>
                </div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px">
            <div class="form-group">
                <label class="form-label">Şifre</label>
                <div class="input-wrap">
                    <span class="input-icon">🔑</span>
                    <input type="password" name="password" class="form-control" placeholder="Min. 8 karakter" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Şifre Tekrar</label>
                <div class="input-wrap">
                    <span class="input-icon">🔑</span>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Şifreyi tekrar girin" required>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-login">Hesap Oluştur →</button>
    </form>

    <div class="divider">veya</div>

    <div class="register-link">
        Zaten hesabın var mı? <a href="{{ route('login') }}">Giriş Yap</a>
    </div>
</div>
</body>
</html>