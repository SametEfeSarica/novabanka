{{-- ============================================================ --}}
{{-- DOSYA: resources/views/cards/index.blade.php                 --}}
{{-- Sanal Kart Yönetimi                                          --}}
{{-- ============================================================ --}}

@extends('layouts.app')

@section('title', 'Kartlarım')

@section('content')

<div class="section-header">
    <div>
        <div class="section-title">
            <i class="fa-solid fa-credit-card" style="font-size:15px; color:var(--accent); margin-right:8px;"></i>
            Kartlarım
        </div>
        <div class="section-sub">Sanal ve fiziksel kartlarınızı yönetin</div>
    </div>
    <button onclick="showCreateCardModal()" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i>
        Sanal Kart Oluştur
    </button>
</div>

{{-- Kartlar Listesi --}}
@if($cards->isEmpty())
<div class="card" style="text-align:center; padding:70px 40px;">
    <div style="
        width:72px; height:72px;
        background: var(--bg-card-2);
        border: 1px solid var(--border);
        border-radius: 20px;
        display:flex; align-items:center; justify-content:center;
        margin: 0 auto 20px;
        font-size:28px; color:var(--text-muted);
    ">
        <i class="fa-solid fa-credit-card"></i>
    </div>
    <div style="font-size:18px; font-weight:700; margin-bottom:8px; color:var(--text);">Henüz kart yok</div>
    <div style="color:var(--text-muted); margin-bottom:28px; font-size:14px;">Ücretsiz sanal kart oluşturabilirsiniz</div>
    <button onclick="showCreateCardModal()" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i>
        Kart Oluştur
    </button>
</div>
@else
<div class="grid-2">
    @foreach($cards as $card)
    <div>
        {{-- Banka Kartı Tasarımı --}}
        <div style="
            background: linear-gradient(145deg,
                {{ $card->card_brand === 'visa' ? '#0F2A5C 0%, #1A4080 50%, #0C2850 100%' : '#1E0F4A 0%, #4A1F80 50%, #180B3C 100%' }});
            border-radius: 18px;
            padding: 26px;
            position: relative;
            overflow: hidden;
            margin-bottom: 14px;
            min-height: 188px;
            border: 1px solid {{ $card->card_brand === 'visa' ? 'rgba(59,130,246,0.18)' : 'rgba(168,85,247,0.18)' }};
            box-shadow: 0 16px 40px rgba(0,0,0,0.35);
        ">
            {{-- Background shimmer --}}
            <div style="position:absolute; inset:0; background:url('data:image/svg+xml,%3Csvg viewBox=\'0 0 400 200\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Ccircle cx=\'350\' cy=\'50\' r=\'120\' fill=\'rgba(255,255,255,0.03)\'/%3E%3Ccircle cx=\'300\' cy=\'180\' r=\'80\' fill=\'rgba(255,255,255,0.02)\'/%3E%3C/svg%3E'); background-size:cover;"></div>
            <div style="position:absolute; top:0; left:0; right:0; height:1px; background:linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);"></div>

            <div style="position:relative; z-index:1; height:100%; display:flex; flex-direction:column; justify-content:space-between;">
                {{-- Üst: Chip & Logo --}}
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div style="
                        width:44px; height:34px;
                        background: linear-gradient(135deg, #D4AF37, #F5E070, #B8860B);
                        border-radius: 6px;
                        position:relative;
                        overflow:hidden;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                    ">
                        <div style="position:absolute; inset:0; background:repeating-linear-gradient(0deg, transparent, transparent 10px, rgba(0,0,0,0.08) 10px, rgba(0,0,0,0.08) 11px);"></div>
                        <div style="position:absolute; top:50%; left:0; right:0; height:1px; background:rgba(0,0,0,0.15);"></div>
                    </div>
                    <div style="font-size:18px; font-weight:800; color:rgba(255,255,255,0.9); font-style:italic; letter-spacing:1px;">
                        {{ strtoupper($card->card_brand) }}
                    </div>
                </div>

                {{-- Kart Numarası --}}
                <div
                    style="font-family: 'DM Mono', monospace; font-size: 17px; letter-spacing: 3px; color: rgba(255,255,255,0.85); cursor: pointer; margin:16px 0;"
                    title="Tam numarayı görmek için tıkla"
                    onclick="toggleCardNumber(this, '{{ $card->card_number }}', '{{ $card->masked_number }}')"
                >
                {{ $card->masked_number }}
                </div>

                {{-- Alt Bilgiler --}}
                <div style="display:flex; justify-content:space-between; align-items:flex-end;">
                    <div>
                        <div style="font-size:9px; color:rgba(255,255,255,0.4); margin-bottom:3px; letter-spacing:1px; text-transform:uppercase;">Kart Sahibi</div>
                        <div style="font-size:13px; font-weight:600; letter-spacing:1px; color:rgba(255,255,255,0.9);">{{ $card->card_holder_name }}</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:9px; color:rgba(255,255,255,0.4); margin-bottom:3px; letter-spacing:1px; text-transform:uppercase;">Son Kullanma</div>
                        <div style="font-size:13px; font-weight:600; color:rgba(255,255,255,0.9);">{{ $card->expiry_date }}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:9px; color:rgba(255,255,255,0.4); margin-bottom:3px; letter-spacing:1px; text-transform:uppercase;">Tür</div>
                        <span style="
                            background:rgba(255,255,255,0.12);
                            padding:3px 8px; border-radius:4px;
                            font-size:11px; font-weight:700; letter-spacing:0.5px;
                            color:rgba(255,255,255,0.8);
                        ">SANAL</span>
                    </div>
                </div>
            </div>

            {{-- Dondurulmuş overlay --}}
            @if($card->is_frozen)
            <div style="
                position:absolute; inset:0;
                background:rgba(8,12,20,0.75);
                backdrop-filter:blur(3px);
                border-radius:18px;
                display:flex; flex-direction:column; align-items:center; justify-content:center;
                gap:8px;
            ">
                <i class="fa-solid fa-snowflake" style="font-size:28px; color:#3B82F6;"></i>
                <span style="font-size:13px; font-weight:700; color:#3B82F6; letter-spacing:1.5px; text-transform:uppercase;">Donduruldu</span>
            </div>
            @endif
        </div>

        {{-- Kart Kontrolleri --}}
        <div class="card card-sm">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <div>
                    <div style="font-size:11px; color:var(--text-muted); margin-bottom:3px;">Bağlı Hesap Bakiyesi</div>
                    <div style="font-size:15px; font-weight:700; font-family:'DM Mono',monospace;">₺{{ number_format($card->account->balance, 2) }}</div>
                </div>
                <div>
                    <span class="{{ $card->is_active ? 'badge badge-success' : 'badge badge-danger' }}">
                        <i class="fa-solid fa-circle" style="font-size:6px;"></i>
                        {{ $card->is_active ? 'AKTİF' : 'İPTAL' }}
                    </span>
                </div>
            </div>

            {{-- Limit Göstergesi --}}
            @if($card->spending_limit)
            <div style="margin-bottom:14px;">
                <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:6px;">
                    <span style="color:var(--text-muted);">
                        <i class="fa-solid fa-gauge" style="font-size:10px; margin-right:3px;"></i>
                        Harcama Limiti
                    </span>
                    <span style="font-weight:600; font-family:'DM Mono',monospace;">₺{{ number_format($card->spending_limit, 2) }}</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width:{{ min(100, ($card->spent_today / $card->spending_limit) * 100) }}%;"></div>
                </div>
            </div>
            @endif

            {{-- Butonlar --}}
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <button
                    onclick="toggleFreeze({{ $card->id }})"
                    class="btn btn-sm {{ $card->is_frozen ? 'btn-primary' : 'btn-ghost' }}">
                    <i class="fa-solid {{ $card->is_frozen ? 'fa-unlock' : 'fa-snowflake' }}"></i>
                    {{ $card->is_frozen ? 'Çöz' : 'Dondur' }}
                </button>

                @if($card->is_active)
                <button onclick="showCvv({{ $card->id }})" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-eye"></i>
                    CVV
                </button>
                <button onclick="cancelCard({{ $card->id }})" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-ban"></i>
                    İptal
                </button>
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
    background:rgba(0,0,0,0.75);
    backdrop-filter:blur(8px);
    z-index:1000;
    align-items:center; justify-content:center;
">
    <div class="card" style="width:440px; max-width:90%; box-shadow:var(--shadow-float);">
        <div class="section-header">
            <div>
                <div class="section-title">
                    <i class="fa-solid fa-plus" style="font-size:13px; color:var(--accent); margin-right:8px;"></i>
                    Sanal Kart Oluştur
                </div>
            </div>
            <button onclick="document.getElementById('createCardModal').style.display='none'"
                    style="background:rgba(255,255,255,0.06); border:1px solid var(--border); color:var(--text-muted); font-size:14px; cursor:pointer; width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; transition:all 0.2s;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="divider" style="margin-top:0;"></div>

        <div class="form-group">
            <label class="form-label">
                <i class="fa-solid fa-link" style="margin-right:4px;"></i>
                Hangi Hesaba Bağlanacak?
            </label>
            <select id="cardAccountId" class="form-control">
                @foreach($accounts as $acc)
                <option value="{{ $acc->id }}">{{ $acc->currency }} — ₺{{ number_format($acc->balance, 2) }}</option>
                @endforeach
            </select>
        </div>

        <div style="
            background:rgba(0,217,163,0.04);
            border:1px solid rgba(0,217,163,0.12);
            border-radius:var(--radius-sm);
            padding:13px 15px;
            font-size:12.5px;
            color:var(--text-muted);
            margin-bottom:20px;
            display:flex; gap:9px;
            line-height:1.5;
        ">
            <i class="fa-solid fa-circle-info" style="color:var(--accent); margin-top:1px; flex-shrink:0;"></i>
            <span>Sanal kart hemen oluşturulur. Online alışveriş için kullanabilirsiniz. Kart bilgileri (numara, CVV) tek seferlik gösterilir.</span>
        </div>

        <button onclick="createCard()" class="btn btn-primary btn-full" style="padding:13px;">
            <i class="fa-solid fa-credit-card"></i>
            Kart Oluştur
        </button>
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
