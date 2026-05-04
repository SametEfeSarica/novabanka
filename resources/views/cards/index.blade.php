{{-- ============================================================ --}}
{{-- DOSYA: resources/views/cards/index.blade.php                 --}}
{{-- Sanal Kart Yönetimi                                          --}}
{{-- ============================================================ --}}

@extends('layouts.app')

@section('title', 'Kartlarım')

@section('content')

<div class="section-header">
    <div>
        <div class="section-title">Kartlarım</div>
        <div class="section-sub">Sanal ve fiziksel kartlarınızı yönetin</div>
    </div>
    <button onclick="showCreateCardModal()" class="btn btn-primary">+ Sanal Kart Oluştur</button>
</div>

{{-- Kartlar Listesi --}}
@if($cards->isEmpty())
<div class="card" style="text-align:center; padding:60px;">
    <div style="font-size:48px; margin-bottom:16px;">💳</div>
    <div style="font-size:18px; font-weight:600; margin-bottom:8px;">Henüz kart yok</div>
    <div style="color:#7A8BA3; margin-bottom:24px;">Ücretsiz sanal kart oluşturabilirsiniz</div>
    <button onclick="showCreateCardModal()" class="btn btn-primary">Kart Oluştur</button>
</div>
@else
<div class="grid-2">
    @foreach($cards as $card)
    <div style="position:relative;">
        {{-- Banka Kartı Tasarımı --}}
        <div style="
            background: linear-gradient(135deg,
                {{ $card->card_brand === 'visa' ? '#1A3B6B, #2D6B8A' : '#2D1B6B, #6B2D5A' }});
            border-radius: 20px;
            padding: 28px;
            position: relative;
            overflow: hidden;
            margin-bottom: 16px;
            min-height: 180px;
        ">
            {{-- Chip & Logo --}}
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <div style="
                    width:44px; height:32px;
                    background: linear-gradient(135deg, #FFD700, #FFA500);
                    border-radius: 6px;
                "></div>
                <div style="font-size:22px; font-weight:700; color:rgba(255,255,255,0.9);">
                    {{ strtoupper($card->card_brand) }}
                </div>
            </div>

            {{-- Kart Numarası --}}
            <div 
            style="font-family: 'Space Mono', monospace; font-size: 18px; letter-spacing: 3px; color: rgba(255,255,255,0.9); margin-bottom: 20px; cursor: pointer;"
            title="Tam numarayı görmek için tıkla"
            onclick="toggleCardNumber(this, '{{ $card->card_number }}', '{{ $card->masked_number }}')"
            >
            {{ $card->masked_number }}
            </div>

            {{-- Alt Bilgiler --}}
            <div style="display:flex; justify-content:space-between; align-items:end;">
                <div>
                    <div style="font-size:10px; color:rgba(255,255,255,0.5); margin-bottom:2px;">KART SAHİBİ</div>
                    <div style="font-size:14px; font-weight:600; letter-spacing:1px;">{{ $card->card_holder_name }}</div>
                </div>
                <div>
                    <div style="font-size:10px; color:rgba(255,255,255,0.5); margin-bottom:2px;">SON KULLANMA</div>
                    <div style="font-size:14px; font-weight:600;">{{ $card->expiry_date }}</div>
                </div>
                <div>
                    <div style="font-size:10px; color:rgba(255,255,255,0.5); margin-bottom:2px;">TÜR</div>
                    <div style="font-size:12px;">
                        <span style="
                            background:rgba(255,255,255,0.15);
                            padding:3px 8px; border-radius:5px;
                        ">SANAL</span>
                    </div>
                </div>
            </div>

            {{-- Dondurulmuş overlay --}}
            @if($card->is_frozen)
            <div style="
                position:absolute; inset:0;
                background:rgba(0,0,0,0.6);
                border-radius:20px;
                display:flex; align-items:center; justify-content:center;
                font-size:16px; font-weight:700; color:#00B8FF;
            ">🔒 DONDURULDU</div>
            @endif
        </div>

        {{-- Kart Kontrolleri --}}
        <div class="card card-sm">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <div>
                    <div style="font-size:12px; color:#7A8BA3;">Bağlı Hesap</div>
                    <div style="font-size:13px;">₺{{ number_format($card->account->balance, 2) }} bakiye</div>
                </div>
                <div>
                    <span class="{{ $card->is_active ? 'badge badge-success' : 'badge badge-danger' }}">
                        {{ $card->is_active ? 'AKTİF' : 'İPTAL' }}
                    </span>
                </div>
            </div>

            {{-- Limit Göstergesi --}}
            @if($card->spending_limit)
            <div style="margin-bottom:12px;">
                <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px;">
                    <span style="color:#7A8BA3;">Harcama Limiti</span>
                    <span>₺{{ number_format($card->spending_limit, 2) }}</span>
                </div>
                <div style="height:4px; background:rgba(255,255,255,0.1); border-radius:2px;">
                    <div style="width:{{ min(100, ($card->spent_today / $card->spending_limit) * 100) }}%; height:100%; background:#00E5B4; border-radius:2px;"></div>
                </div>
            </div>
            @endif

            {{-- Ayarlar --}}
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <button
                    onclick="toggleFreeze({{ $card->id }})"
                    class="btn btn-sm {{ $card->is_frozen ? 'btn-primary' : 'btn-secondary' }}">
                    {{ $card->is_frozen ? '🔓 Çöz' : '🔒 Dondur' }}
                </button>

                @if($card->is_active)
                <button onclick="showCvv({{ $card->id }})" class="btn btn-secondary btn-sm">👁 CVV Göster</button>
                <button onclick="cancelCard({{ $card->id }})" class="btn btn-danger btn-sm">İptal Et</button>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Modal: Kart Oluştur --}}
<div id="createCardModal" style="
    display:none;
    position:fixed; inset:0;
    background:rgba(0,0,0,0.7);
    z-index:1000;
    align-items:center; justify-content:center;
">
    <div class="card" style="width:420px; max-width:90%;">
        <div class="section-header">
            <div class="section-title">Sanal Kart Oluştur</div>
            <button onclick="document.getElementById('createCardModal').style.display='none'"
                    style="background:none;border:none;color:#7A8BA3;font-size:20px;cursor:pointer;">✕</button>
        </div>

        <div class="form-group">
            <label class="form-label">Hangi Hesaba Bağlanacak?</label>
            <select id="cardAccountId" class="form-control">
                @foreach($accounts as $acc)
                <option value="{{ $acc->id }}">{{ $acc->currency }} - ₺{{ number_format($acc->balance, 2) }}</option>
                @endforeach
            </select>
        </div>

        <div style="
            background:rgba(0,229,180,0.05);
            border:1px solid rgba(0,229,180,0.15);
            border-radius:12px;
            padding:14px;
            font-size:12px;
            color:#7A8BA3;
            margin-bottom:20px;
        ">
            ℹ️ Sanal kart hemen oluşturulur. Online alışveriş için kullanabilirsiniz. Kart bilgileri (numara, CVV) tek seferlik gösterilir.
        </div>

        <button onclick="createCard()" class="btn btn-primary btn-full">Kart Oluştur</button>
    </div>
</div>

@endsection

@push('scripts')
<script>
function showCreateCardModal() {
    document.getElementById('createCardModal').style.display = 'flex';
}

async function createCard() {
    const accountId = document.getElementById('cardAccountId').value;
    const res = await fetch('{{ route("cards.create") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ account_id: accountId })
    });
    const data = await res.json();

    if (data.success) {
        alert(`✅ Kart oluşturuldu!\n\nKart No: ${data.card.card_number}\nSon Kullanma: ${data.card.expiry_month}/${data.card.expiry_year}\nCVV: ${data.card.cvv}\n\n⚠️ Bu bilgileri güvenli bir yerde saklayın!`);
        location.reload();
    } else {
        alert('❌ ' + data.message);
    }
}

async function toggleFreeze(cardId) {
    const res  = await fetch(`/kartlar/${cardId}/dondur`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF }
    });
    const data = await res.json();
    alert(data.message);
    location.reload();
}

async function cancelCard(cardId) {
    if (!confirm('Kartı iptal etmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) return;
    const res  = await fetch(`/kartlar/${cardId}/iptal`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF }
    });
    const data = await res.json();
    alert(data.message);
    location.reload();
}

async function showCvv(cardId) {
    const cards = @json($cards);
    const card = cards.find(c => c.id === cardId);
    
    if (card) {
        alert(`🔒 Güvenlik Kodu (CVV): ${card.cvv}`);
    } else {
        alert('❌ Kart bilgisi alınamadı.');
    }
}

function toggleCardNumber(element, fullNumber, maskedNumber) {
    if (element.textContent.includes('*')) {
        element.textContent = fullNumber.match(/.{1,4}/g).join(' ');
    } else {
        element.textContent = maskedNumber;
    }
}
</script>
@endpush