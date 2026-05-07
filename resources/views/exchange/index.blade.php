{{-- ============================================================ --}}
{{-- DOSYA: resources/views/exchange/index.blade.php              --}}
{{-- Borsa & Döviz Sayfası                                        --}}
{{-- ============================================================ --}}

@extends('layouts.app')

@section('title', 'Borsa & Döviz')

@section('content')

{{-- Piyasa Fiyatları --}}
<div class="card" style="margin-bottom:22px;">
    <div class="section-header">
        <div>
            <div class="section-title">
                <i class="fa-solid fa-chart-line" style="font-size:14px; color:var(--accent); margin-right:8px;"></i>
                Anlık Piyasa
            </div>
            <div class="section-sub" id="lastUpdate">
                <i class="fa-solid fa-circle-notch fa-spin" style="font-size:10px; margin-right:4px;"></i>
                Güncelleniyor...
            </div>
        </div>
        <button onclick="refreshPrices()" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrows-rotate"></i>
            Yenile
        </button>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(196px,1fr)); gap:10px;" id="priceGrid">
        @foreach($prices as $symbol => $data)
        @if($data['price'] > 0)
        <div style="
            background: rgba(255,255,255,0.025);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 12px;
            padding: 18px;
            cursor: pointer;
            transition: all 0.18s;
        "
        onmouseenter="this.style.background='rgba(255,255,255,0.045)'; this.style.borderColor='rgba(0,217,163,0.2)'"
        onmouseleave="this.style.background='rgba(255,255,255,0.025)'; this.style.borderColor='rgba(255,255,255,0.06)'"
        onclick="selectAsset('{{ $symbol }}', {{ $data['price'] }})"
        >
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                <div style="
                    width:38px; height:38px;
                    background: var(--accent-dim);
                    border: 1px solid rgba(0,217,163,0.1);
                    border-radius: 10px;
                    display:flex; align-items:center; justify-content:center;
                    font-size:15px;
                ">{{ $data['icon'] }}</div>
                <div>
                    <div style="font-weight:700; font-size:13.5px; color:var(--text);">{{ $symbol }}</div>
                    <div style="font-size:11px; color:var(--text-muted);">{{ $data['name'] }}</div>
                </div>
            </div>
            <div style="font-family:'DM Mono',monospace; font-size:17px; font-weight:700; color:var(--text); margin-bottom:5px;">
                ₺{{ number_format($data['price'], $symbol === 'BTC' ? 0 : 2) }}
            </div>
            <div style="font-size:12px; font-weight:600; display:flex; align-items:center; gap:4px; {{ ($data['change'] ?? 0) >= 0 ? 'color:#00D9A3' : 'color:#F43F5E' }}">
                <i class="fa-solid {{ ($data['change'] ?? 0) >= 0 ? 'fa-caret-up' : 'fa-caret-down' }}"></i>
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
        <div style="font-size:15.5px; font-weight:700; margin-bottom:18px; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-arrows-rotate" style="color:var(--accent); font-size:13px;"></i>
            Alım / Satım
        </div>

        <div class="tab-group" style="margin-bottom:22px;">
            <button id="buyTab" onclick="switchTab('buy')" class="tab-btn active-buy">
                <i class="fa-solid fa-arrow-trend-up" style="margin-right:6px;"></i>AL
            </button>
            <button id="sellTab" onclick="switchTab('sell')" class="tab-btn">
                <i class="fa-solid fa-arrow-trend-down" style="margin-right:6px;"></i>SAT
            </button>
        </div>

        {{-- Alım Formu --}}
        <div id="buyForm">
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-building-columns" style="margin-right:4px;"></i>Hesap
                </label>
                <select id="buyAccountId" class="form-control">
                    @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->currency }} — ₺{{ number_format($acc->balance, 2) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-coins" style="margin-right:4px;"></i>Varlık
                </label>
                <select id="buySymbol" class="form-control" onchange="updateBuyCalc()">
                    @foreach($prices as $symbol => $data)
                    @if($data['price'] > 0)
                    <option value="{{ $symbol }}" data-price="{{ $data['price'] }}">{{ $symbol }} — {{ $data['name'] }}</option>
                    @endif
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">
                    <i class="fa-solid fa-turkish-lira-sign" style="margin-right:4px;"></i>TRY Miktarı
                </label>
                <div style="position:relative;">
                    <input type="number" id="buyAmount" class="form-control" placeholder="Örn: 1000" min="50" step="1" oninput="updateBuyCalc()"
                        style="padding-left:36px; font-family:'DM Mono',monospace;">
                    <div style="position:absolute; left:13px; top:50%; transform:translateY(-50%); color:var(--text-muted); pointer-events:none;">₺</div>
                </div>
            </div>
            {{-- Hesaplama --}}
            <div id="buyCalc" class="calc-box" style="display:none;">
                <div class="info-row" style="padding:6px 0; border-bottom:1px solid rgba(0,217,163,0.08);">
                    <span class="info-row-label" style="font-size:12px;">Alınacak Miktar</span>
                    <span id="calcQty" style="font-family:'DM Mono',monospace; font-size:12.5px; font-weight:600; color:var(--text);"></span>
                </div>
                <div class="info-row" style="padding:6px 0; border-bottom:none;">
                    <span class="info-row-label" style="font-size:12px;">Birim Fiyat</span>
                    <span id="calcPrice" style="font-family:'DM Mono',monospace; font-size:12.5px; font-weight:600; color:var(--text);"></span>
                </div>
            </div>
            <button onclick="executeBuy()" class="btn btn-primary btn-full" style="padding:13px;">
                <i class="fa-solid fa-arrow-trend-up"></i>
                Satın Al
            </button>
        </div>

        {{-- Satış Formu --}}
        <div id="sellForm" style="display:none;">
            @if($investments->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <div class="empty-state-title">Satılacak varlık yok</div>
                    <div class="empty-state-text">Önce alım yapın</div>
                </div>
            @else
                <div class="form-group">
                    <label class="form-label">
                        <i class="fa-solid fa-coins" style="margin-right:4px;"></i>Varlık Seç
                    </label>
                    <select id="sellInvestmentId" class="form-control" onchange="updateSellInfo()">
                        @foreach($investments as $inv)
                        <option value="{{ $inv->id }}" data-qty="{{ $inv->quantity }}" data-symbol="{{ $inv->symbol }}">
                            {{ $inv->symbol }} — {{ number_format($inv->quantity, 6) }} adet
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        <i class="fa-solid fa-scale-balanced" style="margin-right:4px;"></i>Satılacak Miktar
                    </label>
                    <input type="number" id="sellQty" class="form-control" placeholder="0.00000000" step="0.000001"
                        style="font-family:'DM Mono',monospace;">
                </div>
                <button onclick="executeSell()" class="btn btn-danger btn-full" style="padding:13px;">
                    <i class="fa-solid fa-arrow-trend-down"></i>
                    Sat
                </button>
            @endif
        </div>
    </div>

    {{-- Sağ: Portföy --}}
    <div class="card">
        <div style="font-size:15.5px; font-weight:700; margin-bottom:18px; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-briefcase" style="color:var(--accent); font-size:13px;"></i>
            Portföyüm
        </div>

        @if($investments->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <div class="empty-state-title">Henüz yatırım yapılmadı</div>
                <div class="empty-state-text">Soldaki panelden alım yapabilirsiniz</div>
            </div>
        @else
            @foreach($investments as $inv)
            <div style="
                display:flex; align-items:center; gap:14px;
                padding:13px 0;
                border-bottom:1px solid rgba(255,255,255,0.04);
            ">
                <div style="
                    width:44px; height:44px;
                    background: var(--accent-dim);
                    border: 1px solid rgba(0,217,163,0.1);
                    border-radius: 12px;
                    display:flex; align-items:center; justify-content:center;
                    font-size:12px; font-weight:800; color:var(--accent);
                    font-family:'DM Mono',monospace;
                    flex-shrink:0;
                ">{{ substr($inv->symbol, 0, 2) }}</div>
                <div style="flex:1;">
                    <div style="font-weight:700; font-size:14px; color:var(--text);">{{ $inv->symbol }}</div>
                    <div style="font-size:11.5px; color:var(--text-muted); margin-top:2px;">{{ number_format($inv->quantity, 6) }} adet</div>
                    <div style="font-size:11px; color:var(--text-muted);">
                        <i class="fa-solid fa-tag" style="font-size:9px; margin-right:2px;"></i>
                        Alış: ₺{{ number_format($inv->buy_price, 2) }}
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-family:'DM Mono',monospace; font-weight:700; font-size:14px; color:var(--text);">₺{{ number_format($inv->current_value, 2) }}</div>
                    <div style="font-size:11.5px; margin-top:3px; font-weight:600; display:flex; align-items:center; gap:3px; justify-content:flex-end; {{ $inv->profit_loss >= 0 ? 'color:#00D9A3' : 'color:#F43F5E' }}">
                        <i class="fa-solid {{ $inv->profit_loss >= 0 ? 'fa-caret-up' : 'fa-caret-down' }}" style="font-size:10px;"></i>
                        ₺{{ number_format(abs($inv->profit_loss), 2) }}
                    </div>
                    <div style="font-size:11px; {{ $inv->profit_loss >= 0 ? 'color:#00D9A3' : 'color:#F43F5E' }}">
                        ({{ $inv->profit_loss >= 0 ? '+' : '' }}{{ number_format($inv->profit_loss_percent, 2) }}%)
                    </div>
                </div>
            </div>
            @endforeach

            {{-- Toplam Özet --}}
            <div style="
                margin-top:16px;
                background: rgba(0,217,163,0.04);
                border: 1px solid rgba(0,217,163,0.1);
                border-radius: var(--radius-sm);
                padding: 14px 16px;
            ">
                <div style="display:flex; justify-content:space-between; font-size:12.5px; margin-bottom:8px;">
                    <span style="color:var(--text-muted);">
                        <i class="fa-solid fa-coins" style="font-size:10px; margin-right:4px;"></i>
                        Toplam Yatırım
                    </span>
                    <span style="font-family:'DM Mono',monospace; font-weight:600;">₺{{ number_format($investments->sum('total_invested'), 2) }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:14.5px; font-weight:700;">
                    <span style="color:var(--text);">
                        <i class="fa-solid fa-chart-line" style="font-size:11px; margin-right:4px; color:var(--accent);"></i>
                        Güncel Değer
                    </span>
                    <span style="color:var(--accent); font-family:'DM Mono',monospace;">₺{{ number_format($investments->sum('current_value'), 2) }}</span>
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

    document.getElementById('buyTab').className  = 'tab-btn' + (tab === 'buy' ? ' active-buy' : '');
    document.getElementById('sellTab').className = 'tab-btn' + (tab === 'sell' ? ' active-sell' : '');
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
    const el = document.getElementById('lastUpdate');
    el.innerHTML = '<i class="fa-solid fa-circle-check" style="font-size:10px; margin-right:4px; color:var(--accent);"></i>Son güncelleme: ' + new Date().toLocaleTimeString('tr-TR');
}

// İlk yükleme
refreshPrices();

// Her 30 saniyede güncelle
setInterval(refreshPrices, 30000);
</script>
@endpush
