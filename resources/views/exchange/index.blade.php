{{-- ============================================================ --}}
{{-- DOSYA: resources/views/exchange/index.blade.php              --}}
{{-- Borsa & Döviz Sayfası                                        --}}
{{-- ============================================================ --}}

@extends('layouts.app')

@section('title', 'Borsa & Döviz')

@section('content')

{{-- Piyasa Fiyatları --}}
<div class="card" style="margin-bottom:24px;">
    <div class="section-header">
        <div>
            <div class="section-title">Anlık Piyasa</div>
            <div class="section-sub" id="lastUpdate">Güncelleniyor...</div>
        </div>
        <button onclick="refreshPrices()" class="btn btn-secondary btn-sm">🔄 Yenile</button>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px,1fr)); gap:12px;" id="priceGrid">
        @foreach($prices as $symbol => $data)
        @if($data['price'] > 0)
        <div style="
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 14px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.2s;
        " onclick="selectAsset('{{ $symbol }}', {{ $data['price'] }})">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                <div style="
                    width:40px; height:40px;
                    background: rgba(0,229,180,0.1);
                    border-radius:10px;
                    display:flex; align-items:center; justify-content:center;
                    font-size:18px;
                ">{{ $data['icon'] }}</div>
                <div>
                    <div style="font-weight:600;">{{ $symbol }}</div>
                    <div style="font-size:11px; color:#7A8BA3;">{{ $data['name'] }}</div>
                </div>
            </div>
            <div style="font-family:'Space Mono',monospace; font-size:18px; font-weight:700; margin-bottom:4px;">
                ₺{{ number_format($data['price'], $symbol === 'BTC' ? 0 : 2) }}
            </div>
            <div style="font-size:12px; {{ ($data['change'] ?? 0) >= 0 ? 'color:#00E5B4' : 'color:#FF4D6A' }}">
                {{ ($data['change'] ?? 0) >= 0 ? '▲' : '▼' }}
                {{ number_format(abs($data['change'] ?? 0), 2) }}%
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>

{{-- Alım/Satım Paneli + Portföy --}}
<div class="grid-2">

    {{-- Sol: Alım Satım --}}
    <div class="card">
        <div class="section-title" style="margin-bottom:20px;">Alım / Satım</div>

        <div style="display:flex; gap:0; margin-bottom:24px; border:1px solid rgba(255,255,255,0.08); border-radius:12px; overflow:hidden;">
            <button id="buyTab" onclick="switchTab('buy')" style="
                flex:1; padding:12px;
                background: rgba(0,229,180,0.15);
                color: #00E5B4;
                border:none; cursor:pointer;
                font-family:'Sora',sans-serif; font-size:14px; font-weight:600;
            ">📈 AL</button>
            <button id="sellTab" onclick="switchTab('sell')" style="
                flex:1; padding:12px;
                background: transparent;
                color: #7A8BA3;
                border:none; cursor:pointer;
                font-family:'Sora',sans-serif; font-size:14px; font-weight:600;
            ">📉 SAT</button>
        </div>

        {{-- Alım Formu --}}
        <div id="buyForm">
            <div class="form-group">
                <label class="form-label">Hesap</label>
                <select id="buyAccountId" class="form-control">
                    @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->currency }} - ₺{{ number_format($acc->balance, 2) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Varlık</label>
                <select id="buySymbol" class="form-control" onchange="updateBuyCalc()">
                    @foreach($prices as $symbol => $data)
                    @if($data['price'] > 0)
                    <option value="{{ $symbol }}" data-price="{{ $data['price'] }}">{{ $symbol }} - {{ $data['name'] }}</option>
                    @endif
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">TRY Miktarı</label>
                <input type="number" id="buyAmount" class="form-control" placeholder="Örn: 1000" min="50" step="1" oninput="updateBuyCalc()">
            </div>
            {{-- Hesaplama --}}
            <div id="buyCalc" style="
                background:rgba(0,229,180,0.05);
                border:1px solid rgba(0,229,180,0.1);
                border-radius:12px;
                padding:14px;
                font-size:13px;
                margin-bottom:20px;
                display:none;
            ">
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <span style="color:#7A8BA3;">Alınacak Miktar</span>
                    <span id="calcQty" style="font-family:'Space Mono',monospace;"></span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:#7A8BA3;">Birim Fiyat</span>
                    <span id="calcPrice" style="font-family:'Space Mono',monospace;"></span>
                </div>
            </div>
            <button onclick="executeBuy()" class="btn btn-primary btn-full" style="padding:14px;">Satın Al →</button>
        </div>

        {{-- Satış Formu --}}
        <div id="sellForm" style="display:none;">
            @if($investments->isEmpty())
                <div style="text-align:center; padding:32px; color:#7A8BA3;">
                    Satılacak varlık bulunamadı.<br>Önce alım yapın.
                </div>
            @else
                <div class="form-group">
                    <label class="form-label">Varlık Seç</label>
                    <select id="sellInvestmentId" class="form-control" onchange="updateSellInfo()">
                        @foreach($investments as $inv)
                        <option value="{{ $inv->id }}" data-qty="{{ $inv->quantity }}" data-symbol="{{ $inv->symbol }}">
                            {{ $inv->symbol }} - {{ number_format($inv->quantity, 6) }} adet
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Satılacak Miktar</label>
                    <input type="number" id="sellQty" class="form-control" placeholder="0.00000000" step="0.000001">
                </div>
                <button onclick="executeSell()" class="btn btn-danger btn-full" style="padding:14px;">Sat →</button>
            @endif
        </div>
    </div>

    {{-- Sağ: Portföy --}}
    <div class="card">
        <div class="section-title" style="margin-bottom:20px;">Portföyüm</div>

        @if($investments->isEmpty())
            <div style="text-align:center; padding:32px; color:#7A8BA3;">
                <div style="font-size:40px; margin-bottom:12px;">📊</div>
                Henüz yatırım yapılmadı
            </div>
        @else
            @foreach($investments as $inv)
            <div style="
                display:flex; align-items:center; gap:16px;
                padding:16px 0;
                border-bottom:1px solid rgba(255,255,255,0.04);
            ">
                <div style="
                    width:48px; height:48px;
                    background:rgba(0,229,180,0.1);
                    border-radius:14px;
                    display:flex; align-items:center; justify-content:center;
                    font-size:20px; font-weight:700; color:#00E5B4;
                ">{{ substr($inv->symbol, 0, 2) }}</div>
                <div style="flex:1;">
                    <div style="font-weight:600; margin-bottom:2px;">{{ $inv->symbol }}</div>
                    <div style="font-size:12px; color:#7A8BA3;">{{ number_format($inv->quantity, 6) }} adet</div>
                    <div style="font-size:11px; color:#7A8BA3;">Alış: ₺{{ number_format($inv->buy_price, 2) }}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-family:'Space Mono',monospace; font-weight:600;">₺{{ number_format($inv->current_value, 2) }}</div>
                    <div style="font-size:12px; {{ $inv->profit_loss >= 0 ? 'color:#00E5B4' : 'color:#FF4D6A' }}">
                        {{ $inv->profit_loss >= 0 ? '+' : '' }}₺{{ number_format($inv->profit_loss, 2) }}
                        ({{ $inv->profit_loss >= 0 ? '+' : '' }}{{ number_format($inv->profit_loss_percent, 2) }}%)
                    </div>
                </div>
            </div>
            @endforeach

            {{-- Toplam --}}
            <div style="
                margin-top:16px;
                padding:16px;
                background:rgba(0,229,180,0.05);
                border-radius:12px;
            ">
                <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:4px;">
                    <span style="color:#7A8BA3;">Toplam Yatırım</span>
                    <span>₺{{ number_format($investments->sum('total_invested'), 2) }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:15px; font-weight:700;">
                    <span>Güncel Değer</span>
                    <span style="color:#00E5B4;">₺{{ number_format($investments->sum('current_value'), 2) }}</span>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
const prices = @json($prices);

function switchTab(tab) {
    document.getElementById('buyForm').style.display  = tab === 'buy' ? 'block' : 'none';
    document.getElementById('sellForm').style.display = tab === 'sell' ? 'block' : 'none';
    document.getElementById('buyTab').style.background  = tab === 'buy' ? 'rgba(0,229,180,0.15)' : 'transparent';
    document.getElementById('buyTab').style.color       = tab === 'buy' ? '#00E5B4' : '#7A8BA3';
    document.getElementById('sellTab').style.background = tab === 'sell' ? 'rgba(255,77,106,0.15)' : 'transparent';
    document.getElementById('sellTab').style.color      = tab === 'sell' ? '#FF4D6A' : '#7A8BA3';
}

function selectAsset(symbol, price) {
    const select = document.getElementById('buySymbol');
    for (let i = 0; i < select.options.length; i++) {
        if (select.options[i].value === symbol) { select.selectedIndex = i; break; }
    }
    updateBuyCalc();
}

function updateBuyCalc() {
    const symbol = document.getElementById('buySymbol').value;
    const amount = parseFloat(document.getElementById('buyAmount').value) || 0;
    const price  = prices[symbol]?.price || 0;

    if (amount > 0 && price > 0) {
        const qty = amount / price;
        document.getElementById('calcQty').textContent   = qty.toFixed(8) + ' ' + symbol;
        document.getElementById('calcPrice').textContent = '₺' + price.toLocaleString('tr-TR');
        document.getElementById('buyCalc').style.display = 'block';
    } else {
        document.getElementById('buyCalc').style.display = 'none';
    }
}

async function executeBuy() {
    const accountId = document.getElementById('buyAccountId').value;
    const symbol    = document.getElementById('buySymbol').value;
    const amount    = parseFloat(document.getElementById('buyAmount').value);

    if (!amount || amount < 50) { alert('Minimum alım tutarı 50 TRY\'dir.'); return; }

    const res  = await fetch('{{ route("exchange.buy") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ account_id: accountId, symbol, amount })
    });
    const data = await res.json();

    alert(data.success ? '✅ ' + data.message : '❌ ' + data.message);
    if (data.success) location.reload();
}

async function executeSell() {
    const investId = document.getElementById('sellInvestmentId').value;
    const qty      = document.getElementById('sellQty').value;

    if (!qty || qty <= 0) { alert('Geçerli bir miktar girin.'); return; }

    const res  = await fetch(`/borsa/sat/${investId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ quantity: qty })
    });
    const data = await res.json();

    alert(data.success ? '✅ ' + data.message : '❌ ' + data.message);
    if (data.success) location.reload();
}

async function refreshPrices() {
    const res  = await fetch('{{ route("exchange.prices") }}');
    const data = await res.json();
    document.getElementById('lastUpdate').textContent = 'Son güncelleme: ' + new Date().toLocaleTimeString('tr-TR');
}

// İlk yükleme
refreshPrices();

// Her 30 saniyede güncelle
setInterval(refreshPrices, 30000);
</script>
@endpush
