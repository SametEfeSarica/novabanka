{{-- ============================================================ --}}
{{-- DOSYA: resources/views/dashboard.blade.php                   --}}
{{-- Ana Panel - Genel Bakış                                      --}}
{{-- ============================================================ --}}

@extends('layouts.app')

@section('title', 'Genel Bakış')

@section('content')

{{-- ── Üst: Ana Kart + İstatistikler ── --}}
<div style="display:grid; grid-template-columns:1fr 320px; gap:18px; margin-bottom:22px;">

    {{-- Ana Banka Kartı --}}
    <div style="
        background: linear-gradient(145deg, #0B1E38 0%, #0E2540 45%, #091E32 100%);
        border: 1px solid rgba(0,217,163,0.13);
        border-radius: 22px;
        padding: 30px;
        position: relative;
        overflow: hidden;
        min-height: 220px;
    ">
        {{-- Dekoratif elementler --}}
        <div style="position:absolute; right:-60px; top:-60px; width:240px; height:240px; background:radial-gradient(circle, rgba(0,217,163,0.07) 0%, transparent 70%); border-radius:50%;"></div>
        <div style="position:absolute; right:80px; bottom:-40px; width:160px; height:160px; background:radial-gradient(circle, rgba(59,130,246,0.06) 0%, transparent 70%); border-radius:50%;"></div>
        <div style="position:absolute; top:0; left:0; right:0; height:1px; background:linear-gradient(90deg, transparent, rgba(0,217,163,0.3), transparent);"></div>

        <div style="position:relative; z-index:1; height:100%; display:flex; flex-direction:column; justify-content:space-between;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size:11px; color:#4A5568; font-weight:600; letter-spacing:1.2px; text-transform:uppercase; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                        <i class="fa-solid fa-wallet" style="font-size:10px;"></i>
                        Toplam Bakiye
                    </div>
                    <div style="font-size:40px; font-weight:700; font-family:'DM Mono',monospace; letter-spacing:-2px; color:#EDF2F7; line-height:1;">
                        ₺{{ number_format(auth()->user()->total_balance, 2) }}
                    </div>
                </div>
                <div style="
                    width:48px; height:48px;
                    background: linear-gradient(145deg, #00D9A3 0%, #00A87E 100%);
                    border-radius: 13px;
                    display:flex; align-items:center; justify-content:center;
                    box-shadow: 0 4px 20px rgba(0,217,163,0.35);
                ">
                    <i class="fa-solid fa-landmark" style="font-size:18px; color:#041610;"></i>
                </div>
            </div>

            @if($accounts->first())
            <div style="margin-top:22px;">
                <div style="font-size:10px; color:#4A5568; letter-spacing:1px; text-transform:uppercase; margin-bottom:5px; font-weight:600;">IBAN</div>
                <div style="font-family:'DM Mono',monospace; font-size:14px; letter-spacing:1.5px; color:#94A3B8;">
                    {{ $accounts->first()->formatted_iban }}
                </div>
            </div>
            @endif

            {{-- Hızlı İşlem Butonları --}}
            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:24px;">
                <a href="{{ route('transfer.index') }}" style="
                    display:inline-flex; align-items:center; gap:7px;
                    padding:9px 16px;
                    background:rgba(0,217,163,0.13);
                    border:1px solid rgba(0,217,163,0.25);
                    border-radius:8px;
                    color:#00D9A3; font-size:13px; font-weight:600;
                    text-decoration:none;
                    transition:all 0.18s;
                ">
                    <i class="fa-solid fa-paper-plane" style="font-size:12px;"></i>
                    Para Gönder
                </a>

                <a href="{{ route('cards.index') }}" style="
                    display:inline-flex; align-items:center; gap:7px;
                    padding:9px 16px;
                    background:rgba(255,255,255,0.06);
                    border:1px solid rgba(255,255,255,0.09);
                    border-radius:8px;
                    color:#94A3B8; font-size:13px; font-weight:600;
                    text-decoration:none;
                    transition:all 0.18s;
                ">
                    <i class="fa-solid fa-credit-card" style="font-size:12px;"></i>
                    Kartlarım
                </a>

                <a href="{{ route('exchange.index') }}" style="
                    display:inline-flex; align-items:center; gap:7px;
                    padding:9px 16px;
                    background:rgba(255,255,255,0.06);
                    border:1px solid rgba(255,255,255,0.09);
                    border-radius:8px;
                    color:#94A3B8; font-size:13px; font-weight:600;
                    text-decoration:none;
                    transition:all 0.18s;
                ">
                    <i class="fa-solid fa-arrow-trend-up" style="font-size:12px;"></i>
                    Borsa
                </a>
            </div>
        </div>
    </div>

    {{-- Sağ: İstatistik Kartları --}}
    <div style="display:flex; flex-direction:column; gap:14px;">

        <div class="stat-card">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                <div class="stat-label">Bu Ay Harcama</div>
                <div style="width:30px; height:30px; background:rgba(244,63,94,0.1); border-radius:7px; display:flex; align-items:center; justify-content:center;">
                    <i class="fa-solid fa-arrow-trend-down" style="font-size:12px; color:#F43F5E;"></i>
                </div>
            </div>
            <div class="stat-value" style="font-size:22px; color:#F43F5E;">-₺0.00</div>
            <div class="stat-change change-down">
                <i class="fa-solid fa-minus" style="font-size:9px;"></i>
                İşlem yok
            </div>
        </div>

        <div class="stat-card">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                <div class="stat-label">Aktif Kartlar</div>
                <div style="width:30px; height:30px; background:rgba(59,130,246,0.1); border-radius:7px; display:flex; align-items:center; justify-content:center;">
                    <i class="fa-solid fa-credit-card" style="font-size:12px; color:#3B82F6;"></i>
                </div>
            </div>
            <div class="stat-value" style="font-size:22px;">{{ auth()->user()->cards()->where('is_active',true)->count() }}</div>
            <div class="stat-change change-neutral">
                <i class="fa-solid fa-circle-dot" style="font-size:9px;"></i>
                Sanal kart
            </div>
        </div>

        <div class="stat-card">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                <div class="stat-label">Portföy Değeri</div>
                <div style="width:30px; height:30px; background:var(--accent-dim); border-radius:7px; display:flex; align-items:center; justify-content:center;">
                    <i class="fa-solid fa-chart-line" style="font-size:12px; color:var(--accent);"></i>
                </div>
            </div>
            <div class="stat-value" style="font-size:22px; color:var(--accent);">
                ₺{{ number_format($investments->sum('current_value'), 2) }}
            </div>
            <div class="stat-change change-up">
                <i class="fa-solid fa-layer-group" style="font-size:9px;"></i>
                {{ $investments->count() }} varlık
            </div>
        </div>

    </div>
</div>

{{-- ── Piyasa Fiyatları ── --}}
<div class="card" style="margin-bottom:22px;">
    <div class="section-header">
        <div>
            <div class="section-title">Piyasa Fiyatları</div>
            <div class="section-sub">Anlık güncellenir</div>
        </div>
        <a href="{{ route('exchange.index') }}" class="btn btn-secondary btn-sm">
            Tüm Piyasalar
            <i class="fa-solid fa-arrow-right" style="font-size:10px;"></i>
        </a>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(155px,1fr)); gap:10px;">
        @foreach($prices as $symbol => $data)
        @if($data['price'] > 0)
        <div style="
            background: rgba(255,255,255,0.025);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 12px;
            padding: 14px;
            transition: all 0.2s;
        ">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                <span style="font-size:13px; font-weight:700; color:var(--text);">{{ $symbol }}</span>
                <span style="
                    font-size:10px; padding:2px 7px; border-radius:4px; font-weight:700;
                    {{ ($data['change'] ?? 0) >= 0 ? 'background:rgba(0,217,163,0.1); color:#00D9A3;' : 'background:rgba(244,63,94,0.1); color:#F43F5E;' }}
                ">
                    <i class="fa-solid {{ ($data['change'] ?? 0) >= 0 ? 'fa-caret-up' : 'fa-caret-down' }}"></i>
                    {{ number_format(abs($data['change'] ?? 0), 2) }}%
                </span>
            </div>
            <div style="font-family:'DM Mono',monospace; font-size:14.5px; font-weight:600; color:var(--text);">
                ₺{{ number_format($data['price'], $symbol === 'BTC' ? 0 : 2) }}
            </div>
            <div style="font-size:11px; color:var(--text-muted); margin-top:3px;">{{ $data['name'] }}</div>
        </div>
        @endif
        @endforeach
    </div>
</div>

{{-- ── Alt: Son İşlemler & Portföy ── --}}
<div class="grid-2">

    {{-- Son İşlemler --}}
    <div class="card">
        <div class="section-header">
            <div>
                <div class="section-title">Son İşlemler</div>
            </div>
        </div>

        @if($recentTx->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fa-regular fa-envelope-open"></i>
                </div>
                <div class="empty-state-title">Henüz işlem yok</div>
                <div class="empty-state-text">İlk transferinizi gerçekleştirin</div>
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
                            <div style="font-weight:600; color:var(--text);">{{ $tx->getTypeLabel() }}</div>
                            <div style="font-size:11.5px; color:var(--text-muted); margin-top:2px;">{{ $tx->description }}</div>
                        </td>
                        <td>
                            @if($tx->sender_account_id === $accounts->first()?->id)
                                <span style="color:#F43F5E; font-family:'DM Mono',monospace; font-weight:600;">-₺{{ number_format($tx->amount, 2) }}</span>
                            @else
                                <span style="color:#00D9A3; font-family:'DM Mono',monospace; font-weight:600;">+₺{{ number_format($tx->amount, 2) }}</span>
                            @endif
                        </td>
                        <td style="color:var(--text-muted); font-size:12px;">{{ $tx->created_at->format('d.m.Y H:i') }}</td>
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
            <a href="{{ route('exchange.index') }}" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-plus" style="font-size:10px;"></i>
                Yatırım Yap
            </a>
        </div>

        @if($investments->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <div class="empty-state-title">Henüz yatırım yok</div>
                <div class="empty-state-text">Borsa sayfasından alım yapabilirsiniz</div>
            </div>
        @else
            @foreach($investments as $inv)
            <div style="
                display:flex; align-items:center; gap:14px;
                padding: 13px 0;
                border-bottom: 1px solid rgba(255,255,255,0.04);
            ">
                <div style="
                    width:42px; height:42px;
                    background: var(--accent-dim);
                    border: 1px solid rgba(0,217,163,0.12);
                    border-radius: 11px;
                    display:flex; align-items:center; justify-content:center;
                    font-size:13px; font-weight:800; color:var(--accent);
                    font-family:var(--mono);
                ">{{ substr($inv->symbol, 0, 2) }}</div>
                <div style="flex:1;">
                    <div style="font-weight:700; font-size:14px;">{{ $inv->symbol }}</div>
                    <div style="font-size:11.5px; color:var(--text-muted); margin-top:2px;">{{ number_format($inv->quantity, 6) }} adet</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-family:'DM Mono',monospace; font-weight:700; font-size:14px;">₺{{ number_format($inv->current_value, 2) }}</div>
                    <div style="font-size:12px; margin-top:2px; {{ $inv->profit_loss >= 0 ? 'color:#00D9A3' : 'color:#F43F5E' }}">
                        <i class="fa-solid {{ $inv->profit_loss >= 0 ? 'fa-caret-up' : 'fa-caret-down' }}" style="font-size:10px;"></i>
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
