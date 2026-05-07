{{-- novabanka_output/views/checkout/expired.blade.php --}}
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geçersiz Bağlantı — Nova Banka</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Mono&display=swap" rel="stylesheet">
    <style>
        :root {
            --nova-black: #0a0a0f;
            --nova-surface: #161622;
            --nova-border: rgba(255,255,255,0.08);
            --nova-gold: #c9a84c;
            --nova-text: #e8e8f0;
            --nova-muted: #6b6b85;
            --nova-red: #e05252;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Syne', sans-serif;
            background: var(--nova-black);
            color: var(--nova-text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .box {
            background: var(--nova-surface);
            border: 1px solid var(--nova-border);
            border-radius: 16px;
            padding: 48px 40px;
            text-align: center;
            max-width: 420px;
            width: 100%;
        }
        .icon { font-size: 48px; margin-bottom: 16px; }
        h1 { font-size: 22px; font-weight: 800; margin-bottom: 10px; }
        p { font-size: 14px; color: var(--nova-muted); line-height: 1.6; }
        .back-link {
            display: inline-block;
            margin-top: 28px;
            padding: 12px 28px;
            background: rgba(201,168,76,0.1);
            border: 1px solid rgba(201,168,76,0.3);
            border-radius: 8px;
            color: var(--nova-gold);
            font-size: 13px;
            font-family: 'DM Mono', monospace;
            text-decoration: none;
            transition: background 0.2s;
        }
        .back-link:hover { background: rgba(201,168,76,0.18); }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">⏳</div>
        <h1>Bağlantı Geçersiz</h1>
        <p>{{ $reason ?? 'Bu ödeme bağlantısının süresi dolmuş veya daha önce kullanılmış.' }}</p>
        <p style="margin-top:8px;">Lütfen alışverişe geri dönün ve yeni bir ödeme başlatın.</p>
        <a href="javascript:history.back()" class="back-link">← Geri Dön</a>
    </div>
</body>
</html>
