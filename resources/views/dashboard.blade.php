{{-- ============================================================ --}}
{{-- DOSYA: resources/views/dashboard.blade.php                   --}}
{{-- Ana Panel - Genel Bakış                                      --}}
{{-- ============================================================ --}}

@extends('layouts.app')

@section('title', 'Genel Bakış')

@section('content')

{{-- ── Üst: Bakiye ve Hızlı İşlemler ── --}}
<div style="display:grid; grid-template-columns: 1fr 340px; gap:24px; margin-bottom:24px;">

    {{-- Ana Banka Kartı --}}
    <div style="
        background: linear-gradient(135deg, #0F2A4A 0%, #1A3B5C 40%, #0D3545 100%);
        border: 1px solid rgba(0,229,180,0.15);
        border-radius: 24px;
        padding: 32px;
        position: relative;
        overflow: hidden;
    ">
        {{-- Dekoratif daireler --}}
        <div style="position:absolute; right:-40px; top:-40px; width:200px; height:200px; background:rgba(0,229,180,0.05); border-radius:50%;"></div>
        <div style="position:absolute; right:40px; top:-80px; width:150px; height:150px; background:rgba(0,184,255,0.05); border-radius:50%;"></div>

        <div style="position:relative; z-index:1;">
            <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:32px;">
                <div>
                    <div style="font-size:12px; color:#7A8BA3; font-weight:600; letter-spacing:1px; text-transform:uppercase; margin-bottom:4px;">Toplam Bakiye</div>
                    <div style="font-size:42px; font-weight:700; font-family:'Space Mono',monospace; letter-spacing:-2px; color:#E8EDF5;">
                        ₺{{ number_format(auth()->user()->total_balance, 2) }}
                    </div>
                </div>
                <div style="
                    width:52px; height:52px;
                    background: linear-gradient(135deg, #00E5B4, #00B8FF);
                    border-radius:14px;
                    display:flex; align-items:center; justify-content:center;
                    font-size:24px; font-weight:700; color:#0A0E1A;
                ">N</div>
            </div>

            @if($accounts->first())
            <div style="margin-bottom:24px;">
                <div style="font-size:11px; color:#7A8BA3; margin-bottom:4px;">IBAN</div>
                <div style="font-family:'Space Mono',monospace; font-size:15px; letter-spacing:1px; color:#B0C4D8;">
                    {{ $accounts->first()->formatted_iban }}
                </div>
            </div>
            @endif

            {{-- Hızlı İşlem Butonları --}}
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a href="{{ route('transfer.index') }}" style="
                    display:flex; align-items:center; gap:8px;
                    padding:10px 18px;
                    background:rgba(0,229,180,0.15);
                    border:1px solid rgba(0,229,180,0.3);
                    border-radius:10px;
                    color:#00E5B4; font-size:13px; font-weight:600;
                    text-decoration:none;
                    transition:all 0.2s;
                ">↗ Para Gönder</a>

                <a href="{{ route('cards.index') }}" style="
                    display:flex; align-items:center; gap:8px;
                    padding:10px 18px;
                    background:rgba(255,255,255,0.06);
                    border:1px solid rgba(255,255,255,0.1);
                    border-radius:10px;
                    color:#E8EDF5; font-size:13px; font-weight:600;
                    text-decoration:none;
                ">💳 Kartlarım</a>

                <a href="{{ route('exchange.index') }}" style="
                    display:flex; align-items:center; gap:8px;
                    padding:10px 18px;
                    background:rgba(255,255,255,0.06);
                    border:1px solid rgba(255,255,255,0.1);
                    border-radius:10px;
                    color:#E8EDF5; font-size:13px; font-weight:600;
                    text-decoration:none;
                ">📈 Borsa</a>
            </div>
        </div>
    </div>

    {{-- Sağ Panel: Hızlı İstatistikler --}}
    <div style="display:flex; flex-direction:column; gap:16px;">
        <div class="stat-card">
            <div class="stat-label">Bu Ay Harcama</div>
            <div class="stat-value" style="font-size:22px; color:#FF4D6A;">-₺0.00</div>
            <div class="stat-change change-down">📉 İşlem yok</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Aktif Kartlar</div>
            <div class="stat-value" style="font-size:22px;">{{ auth()->user()->cards()->where('is_active',true)->count() }}</div>
            <div class="stat-change" style="color:#7A8BA3;">💳 Sanal kart</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Portföy Değeri</div>
            <div class="stat-value" style="font-size:22px; color:#00E5B4;">
                ₺{{ number_format($investments->sum('current_value'), 2) }}
            </div>
            <div class="stat-change change-up">📈 {{ $investments->count() }} varlık</div>
        </div>
    </div>
</div>

{{-- ── Orta: Döviz/Kripto Fiyatları ── --}}
<div class="card" style="margin-bottom:24px;">
    <div class="section-header">
        <div>
            <div class="section-title">Piyasa Fiyatları</div>
            <div class="section-sub">Anlık güncellenir</div>
        </div>
        <a href="{{ route('exchange.index') }}" class="btn btn-secondary btn-sm">Tüm Piyasalar →</a>
    </div>

    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap:12px;">
        @foreach($prices as $symbol => $data)
        @if($data['price'] > 0)
        <div style="
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 14px;
            padding: 16px;
        ">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                <span style="font-size:13px; font-weight:600;">{{ $symbol }}</span>
                <span style="font-size:10px; padding:3px 8px; border-radius:5px;
                    {{ ($data['change'] ?? 0) >= 0 ? 'background:rgba(0,229,180,0.1); color:#00E5B4;' : 'background:rgba(255,77,106,0.1); color:#FF4D6A;' }}
                ">
                    {{ ($data['change'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($data['change'] ?? 0, 2) }}%
                </span>
            </div>
            <div style="font-family:'Space Mono',monospace; font-size:15px; font-weight:700;">
                ₺{{ number_format($data['price'], $symbol === 'BTC' ? 0 : 2) }}
            </div>
            <div style="font-size:11px; color:#7A8BA3; margin-top:2px;">{{ $data['name'] }}</div>
        </div>
        @endif
        @endforeach
    </div>
</div>

{{-- ── Alt: Son İşlemler ve Yatırımlar ── --}}
<div class="grid-2">

    {{-- Son İşlemler --}}
    <div class="card">
        <div class="section-header">
            <div class="section-title">Son İşlemler</div>
        </div>

        @if($recentTx->isEmpty())
            <div style="text-align:center; padding:32px; color:#7A8BA3;">
                <div style="font-size:32px; margin-bottom:8px;">📭</div>
                <div>Henüz işlem yok</div>
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>İşlem</th>
                        <th>Tutar</th>
                        <th>Tarih</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTx as $tx)
                    <tr>
                        <td>
                            <div style="font-weight:500;">{{ $tx->getTypeLabel() }}</div>
                            <div style="font-size:12px; color:#7A8BA3;">{{ $tx->description }}</div>
                        </td>
                        <td>
                            @if($tx->sender_account_id === $accounts->first()?->id)
                                <span style="color:#FF4D6A; font-family:'Space Mono',monospace;">-₺{{ number_format($tx->amount, 2) }}</span>
                            @else
                                <span style="color:#00E5B4; font-family:'Space Mono',monospace;">+₺{{ number_format($tx->amount, 2) }}</span>
                            @endif
                        </td>
                        <td style="color:#7A8BA3; font-size:12px;">{{ $tx->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Portföy --}}
    <div class="card">
        <div class="section-header">
            <div class="section-title">Portföyüm</div>
            <a href="{{ route('exchange.index') }}" class="btn btn-secondary btn-sm">Yatırım Yap</a>
        </div>

        @if($investments->isEmpty())
            <div style="text-align:center; padding:32px; color:#7A8BA3;">
                <div style="font-size:32px; margin-bottom:8px;">📊</div>
                <div>Henüz yatırım yok</div>
                <div style="font-size:12px; margin-top:4px;">Borsa sayfasından alım yapabilirsiniz</div>
            </div>
        @else
            @foreach($investments as $inv)
            <div style="
                display:flex; align-items:center; gap:16px;
                padding: 14px 0;
                border-bottom: 1px solid rgba(255,255,255,0.04);
            ">
                <div style="
                    width:44px; height:44px;
                    background: rgba(0,229,180,0.1);
                    border-radius:12px;
                    display:flex; align-items:center; justify-content:center;
                    font-size:18px; font-weight:700;
                ">{{ substr($inv->symbol, 0, 1) }}</div>
                <div style="flex:1;">
                    <div style="font-weight:600;">{{ $inv->symbol }}</div>
                    <div style="font-size:12px; color:#7A8BA3;">{{ number_format($inv->quantity, 6) }} adet</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-family:'Space Mono',monospace; font-weight:600;">₺{{ number_format($inv->current_value, 2) }}</div>
                    <div style="font-size:12px; {{ $inv->profit_loss >= 0 ? 'color:#00E5B4' : 'color:#FF4D6A' }}">
                        {{ $inv->profit_loss >= 0 ? '+' : '' }}%{{ number_format($inv->profit_loss_percent, 2) }}
                    </div>
                </div>
            </div>
            @endforeach
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
// Döviz fiyatlarını 60 saniyede bir güncelle
setInterval(async () => {
    try {
        const res  = await fetch('{{ route("exchange.prices") }}');
        const data = await res.json();
        // Gerçek projede DOM'u burada güncelleyebilirsin
        console.log('Fiyatlar güncellendi:', new Date().toLocaleTimeString());
    } catch (e) {}
}, 60000);
</script>
@endpush
