{{-- ============================================================ --}}
{{-- DOSYA: resources/views/transfer/index.blade.php              --}}
{{-- IBAN ile Para Gönderme Sayfası                               --}}
{{-- ============================================================ --}}

@extends('layouts.app')

@section('title', 'Para Gönder')

@section('content')
<div class="grid-2">

    {{-- Sol: Transfer Formu --}}
    <div class="card">
        <div class="section-header">
            <div>
                <div class="section-title">
                    <i class="fa-solid fa-paper-plane" style="font-size:14px; color:var(--accent); margin-right:8px;"></i>
                    IBAN ile Para Gönder
                </div>
                <div class="section-sub">Anlık transfer · Ücretsiz</div>
            </div>
        </div>

        <div class="divider" style="margin-top:0; margin-bottom:22px;"></div>

        <form action="{{ route('transfer.send') }}" method="POST" id="transferForm">
            @csrf

            {{-- Hesap Seç --}}
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-building-columns" style="margin-right:4px;"></i>
                    Gönderen Hesap
                </label>
                <select name="account_id" class="form-control" required>
                    @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">
                        {{ $acc->currency }} — ₺{{ number_format($acc->balance, 2) }} bakiye
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Alıcı IBAN --}}
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-hashtag" style="margin-right:4px;"></i>
                    Alıcı IBAN
                </label>
                <input
                    type="text"
                    name="receiver_iban"
                    id="receiverIban"
                    class="form-control"
                    placeholder="TR00 0000 0000 0000 0000 0000"
                    maxlength="32"
                    required
                    style="font-family:'DM Mono',monospace; letter-spacing:1px;"
                    oninput="formatIban(this); lookupIban(this.value)"
                >
                {{-- IBAN doğrulama sonucu --}}
                <div id="ibanResult" style="margin-top:8px; font-size:12.5px; display:none;">
                    <div id="ibanFound" style="color:#00D9A3; display:none; align-items:center; gap:6px;">
                        <i class="fa-solid fa-circle-check"></i>
                        Alıcı: <strong id="ibanName"></strong> (NovaBanka)
                    </div>
                    <div id="ibanNotFound" style="color:#F43F5E; display:none; align-items:center; gap:6px;">
                        <i class="fa-solid fa-circle-xmark"></i>
                        Bu IBAN bulunamadı
                    </div>
                </div>
            </div>

            {{-- Tutar --}}
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-turkish-lira-sign" style="margin-right:4px;"></i>
                    Tutar (TRY)
                </label>
                <div style="position:relative;">
                    <input
                        type="number"
                        name="amount"
                        class="form-control"
                        placeholder="0.00"
                        min="1"
                        max="50000"
                        step="0.01"
                        required
                        style="padding-left:38px; font-family:'DM Mono',monospace; font-size:16px;"
                    >
                    <div style="position:absolute; left:13px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:14px; pointer-events:none;">₺</div>
                </div>
                <div style="margin-top:6px; font-size:11.5px; color:var(--text-muted); display:flex; gap:14px;">
                    <span><i class="fa-solid fa-arrow-down-short-wide" style="font-size:9px; margin-right:3px;"></i>Min: ₺1</span>
                    <span><i class="fa-solid fa-arrow-up-wide-short" style="font-size:9px; margin-right:3px;"></i>Maks: ₺50.000</span>
                </div>
            </div>

            {{-- Açıklama --}}
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-note-sticky" style="margin-right:4px;"></i>
                    Açıklama <span style="color:var(--text-muted); font-weight:400; text-transform:none; letter-spacing:0;">(İsteğe Bağlı)</span>
                </label>
                <input
                    type="text"
                    name="description"
                    class="form-control"
                    placeholder="Ör: Kira ödemesi"
                    maxlength="100"
                >
            </div>

            {{-- Güvenlik uyarısı --}}
            <div class="warning-box">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>Gönderdiğiniz IBAN'ı dikkatlice kontrol edin. Onaylanan işlemler geri alınamaz.</span>
            </div>

            <button type="submit" class="btn btn-primary btn-full" style="padding:14px; font-size:14px;">
                <i class="fa-solid fa-paper-plane"></i>
                Transferi Gönder
            </button>
        </form>
    </div>

    {{-- Sağ: Hesap Bilgileri + Limitler --}}
    <div style="display:flex; flex-direction:column; gap:18px;">

        {{-- Kendi IBAN bilgisi --}}
        @foreach($accounts as $acc)
        <div class="card">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
                <div style="width:34px; height:34px; background:var(--accent-dim); border-radius:9px; display:flex; align-items:center; justify-content:center;">
                    <i class="fa-solid fa-building-columns" style="font-size:13px; color:var(--accent);"></i>
                </div>
                <div>
                    <div style="font-size:11px; color:var(--text-muted); font-weight:600; letter-spacing:0.8px; text-transform:uppercase;">Benim Hesabım</div>
                    <div style="font-size:13px; font-weight:600; color:var(--text);">{{ $acc->currency }}</div>
                </div>
            </div>

            <div style="
                background: var(--bg-base);
                border: 1px solid var(--border);
                border-radius: var(--radius-sm);
                padding: 12px 14px;
                margin-bottom: 14px;
            ">
                <div style="font-size:10px; color:var(--text-muted); letter-spacing:1px; text-transform:uppercase; margin-bottom:6px;">IBAN</div>
                <div style="font-family:'DM Mono',monospace; font-size:13.5px; letter-spacing:1.5px; color:var(--text-sub); word-break:break-all;">
                    {{ $acc->formatted_iban }}
                </div>
            </div>

            <div style="display:flex; align-items:baseline; gap:4px; margin-bottom:14px;">
                <div style="font-size:28px; font-weight:700; font-family:'DM Mono',monospace; letter-spacing:-1px;">
                    ₺{{ number_format($acc->balance, 2) }}
                </div>
                <div style="font-size:12px; color:var(--text-muted);">kullanılabilir bakiye</div>
            </div>

            <button onclick="copyIban('{{ $acc->iban }}')" class="btn btn-secondary btn-sm">
                <i class="fa-regular fa-copy"></i>
                IBAN Kopyala
            </button>
        </div>
        @endforeach

        {{-- Transfer Limitleri --}}
        <div class="card">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:16px;">
                <i class="fa-solid fa-gauge-high" style="color:var(--accent); font-size:14px;"></i>
                <div class="section-title" style="font-size:14.5px;">Transfer Limitleri</div>
            </div>

            <div class="info-row">
                <span class="info-row-label"><i class="fa-solid fa-calendar-day" style="font-size:11px; margin-right:5px;"></i>Günlük Limit</span>
                <span class="info-row-value mono">₺50.000</span>
            </div>
            <div class="info-row">
                <span class="info-row-label"><i class="fa-solid fa-calendar" style="font-size:11px; margin-right:5px;"></i>Aylık Limit</span>
                <span class="info-row-value mono">₺500.000</span>
            </div>
            <div class="info-row">
                <span class="info-row-label"><i class="fa-solid fa-arrow-down" style="font-size:11px; margin-right:5px;"></i>Min. Transfer</span>
                <span class="info-row-value mono">₺1</span>
            </div>
            <div class="info-row">
                <span class="info-row-label"><i class="fa-solid fa-hand-holding-dollar" style="font-size:11px; margin-right:5px;"></i>İşlem Ücreti</span>
                <span style="color:var(--accent); font-weight:700; font-size:13px;">
                    <i class="fa-solid fa-check" style="font-size:10px; margin-right:3px;"></i>Ücretsiz
                </span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// IBAN formatla: TR12 3456 7890 1234 5678 9012
function formatIban(input) {
    let val = input.value.replace(/\s/g, '').toUpperCase();
    let formatted = val.match(/.{1,4}/g)?.join(' ') || val;
    input.value = formatted;
}

// IBAN sorgula (debounce ile - her harf girişinde değil, 500ms sonra)
let ibanTimer = null;
function lookupIban(value) {
    clearTimeout(ibanTimer);
    const clean = value.replace(/\s/g, '');

    if (clean.length < 26) {
        document.getElementById('ibanResult').style.display = 'none';
        return;
    }

    ibanTimer = setTimeout(async () => {
        try {
            const res  = await fetch(`{{ route('transfer.lookup') }}?iban=${clean}`);
            const data = await res.json();

            document.getElementById('ibanResult').style.display = 'block';
            if (data.found) {
                document.getElementById('ibanFound').style.display    = 'flex';
                document.getElementById('ibanNotFound').style.display = 'none';
                document.getElementById('ibanName').textContent        = data.name;
            } else {
                document.getElementById('ibanFound').style.display    = 'none';
                document.getElementById('ibanNotFound').style.display = 'flex';
            }
        } catch (e) {}
    }, 500);
}

function copyIban(iban) {
    navigator.clipboard.writeText(iban);
    alert('IBAN kopyalandı!');
}
</script>
@endpush
