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
                <div class="section-title">IBAN ile Para Gönder</div>
                <div class="section-sub">Anlık transfer, ücretsiz</div>
            </div>
        </div>

        <form action="{{ route('transfer.send') }}" method="POST" id="transferForm">
            @csrf

            {{-- Hesap Seç --}}
            <div class="form-group">
                <label class="form-label">Gönderen Hesap</label>
                <select name="account_id" class="form-control" required>
                    @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">
                        {{ $acc->currency }} - ₺{{ number_format($acc->balance, 2) }} bakiye
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Alıcı IBAN --}}
            <div class="form-group">
                <label class="form-label">Alıcı IBAN</label>
                <input
                    type="text"
                    name="receiver_iban"
                    id="receiverIban"
                    class="form-control"
                    placeholder="TR00 0000 0000 0000 0000 0000"
                    maxlength="32"
                    required
                    oninput="formatIban(this); lookupIban(this.value)"
                >
                {{-- IBAN doğrulama sonucu --}}
                <div id="ibanResult" style="margin-top:8px; font-size:13px; display:none;">
                    <div id="ibanFound" style="color:#00E5B4; display:none;">
                        ✓ Alıcı: <strong id="ibanName"></strong> (NovaBanka)
                    </div>
                    <div id="ibanNotFound" style="color:#FF4D6A; display:none;">
                        ✗ Bu IBAN bulunamadı
                    </div>
                </div>
            </div>

            {{-- Tutar --}}
            <div class="form-group">
                <label class="form-label">Tutar (TRY)</label>
                <input
                    type="number"
                    name="amount"
                    class="form-control"
                    placeholder="0.00"
                    min="1"
                    max="50000"
                    step="0.01"
                    required
                >
                <div style="margin-top:6px; font-size:11px; color:#7A8BA3;">Min: ₺1 — Maks: ₺50.000</div>
            </div>

            {{-- Açıklama --}}
            <div class="form-group">
                <label class="form-label">Açıklama (İsteğe Bağlı)</label>
                <input
                    type="text"
                    name="description"
                    class="form-control"
                    placeholder="Ör: Kira ödemesi"
                    maxlength="100"
                >
            </div>

            {{-- Güvenlik uyarısı --}}
            <div style="
                background: rgba(245,200,66,0.08);
                border: 1px solid rgba(245,200,66,0.2);
                border-radius: 12px;
                padding: 14px 16px;
                font-size: 12px;
                color: #F5C842;
                margin-bottom: 20px;
            ">
                ⚠️ Gönderdiğiniz IBAN'ı dikkatlice kontrol edin. İşlemler geri alınamaz.
            </div>

            <button type="submit" class="btn btn-primary btn-full" style="padding:16px;">
                ↗ Gönder
            </button>
        </form>
    </div>

    {{-- Sağ: Hesap Bilgileri & Son Transferler --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- Kendi IBAN bilgisi --}}
        @foreach($accounts as $acc)
        <div class="card">
            <div style="font-size:12px; color:#7A8BA3; margin-bottom:4px;">BENIM HESABIM ({{ $acc->currency }})</div>
            <div style="font-family:'Space Mono',monospace; font-size:14px; letter-spacing:1px; margin-bottom:8px;">
                {{ $acc->formatted_iban }}
            </div>
            <div style="font-size:24px; font-weight:700; font-family:'Space Mono',monospace;">
                ₺{{ number_format($acc->balance, 2) }}
            </div>
            <button onclick="copyIban('{{ $acc->iban }}')" class="btn btn-secondary btn-sm" style="margin-top:12px;">
                📋 IBAN Kopyala
            </button>
        </div>
        @endforeach

        {{-- Hızlı Transfer Limitleri --}}
        <div class="card">
            <div class="section-title" style="font-size:15px; margin-bottom:16px;">Transfer Limitleri</div>
            <div style="display:flex; flex-direction:column; gap:10px;">
                <div style="display:flex; justify-content:space-between; font-size:13px;">
                    <span style="color:#7A8BA3;">Günlük Limit</span>
                    <span>₺50.000</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:13px;">
                    <span style="color:#7A8BA3;">Aylık Limit</span>
                    <span>₺500.000</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:13px;">
                    <span style="color:#7A8BA3;">Min. Transfer</span>
                    <span>₺1</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:13px;">
                    <span style="color:#7A8BA3;">İşlem Ücreti</span>
                    <span style="color:#00E5B4;">Ücretsiz</span>
                </div>
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
                document.getElementById('ibanFound').style.display    = 'block';
                document.getElementById('ibanNotFound').style.display = 'none';
                document.getElementById('ibanName').textContent        = data.name;
            } else {
                document.getElementById('ibanFound').style.display    = 'none';
                document.getElementById('ibanNotFound').style.display = 'block';
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