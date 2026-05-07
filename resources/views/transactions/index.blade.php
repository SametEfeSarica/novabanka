@extends('layouts.app')

@section('title', 'Hesap Hareketleri')

@push('styles')
<style>
    /* ── Filter Bar ── */
    .filter-bar {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .filter-select, .filter-input {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        color: var(--text);
        font-family: var(--font);
        font-size: 13px;
        padding: 9px 14px;
        outline: none;
        transition: border-color 0.2s;
        cursor: pointer;
    }

    .filter-select:focus, .filter-input:focus {
        border-color: rgba(0,217,163,0.4);
    }

    .filter-input::placeholder { color: var(--text-muted); }

    /* ── Summary Cards ── */
    .summary-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 22px;
    }

    @media (max-width: 900px) {
        .summary-row { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 540px) {
        .summary-row { grid-template-columns: 1fr; }
        .filter-bar { flex-direction: column; align-items: stretch; }
        .filter-select, .filter-input { width: 100%; }
    }

    /* ── Transaction Row ── */
    .tx-row {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 0;
        border-bottom: 1px solid var(--border);
        transition: background 0.15s;
        border-radius: var(--radius-sm);
        cursor: pointer;
    }

    .tx-row:last-child { border-bottom: none; }
    .tx-row:hover { background: var(--bg-hover); padding-left: 10px; padding-right: 10px; margin: 0 -10px; }

    .tx-icon {
        width: 44px; height: 44px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .tx-icon.type-transfer  { background: var(--blue-dim); color: var(--blue); }
    .tx-icon.type-deposit   { background: var(--accent-dim); color: var(--accent); }
    .tx-icon.type-withdrawal{ background: var(--danger-dim); color: var(--danger); }
    .tx-icon.type-payment   { background: var(--gold-dim); color: var(--gold); }
    .tx-icon.type-exchange  { background: rgba(139,92,246,0.12); color: #A78BFA; }

    .tx-meta { flex: 1; min-width: 0; }
    .tx-title { font-weight: 600; font-size: 14px; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .tx-desc  { font-size: 12px; color: var(--text-muted); margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .tx-ref   { font-size: 11px; color: var(--text-muted); margin-top: 2px; font-family: var(--mono); }

    .tx-right { text-align: right; flex-shrink: 0; }
    .tx-amount { font-family: var(--mono); font-weight: 700; font-size: 15px; }
    .tx-amount.out { color: var(--danger); }
    .tx-amount.in  { color: var(--accent); }
    .tx-date  { font-size: 11.5px; color: var(--text-muted); margin-top: 4px; }

    /* ── Detail Modal ── */
    .modal-overlay {
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.65);
        backdrop-filter: blur(6px);
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-overlay.open { display: flex; }

    .modal-box {
        background: var(--bg-card);
        border: 1px solid var(--border-strong);
        border-radius: var(--radius-xl);
        padding: 28px;
        width: 100%;
        max-width: 480px;
        box-shadow: var(--shadow-float);
        position: relative;
        animation: modal-in 0.25s ease;
    }

    @keyframes modal-in {
        from { opacity: 0; transform: translateY(16px) scale(0.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .modal-close {
        position: absolute; top: 18px; right: 18px;
        background: var(--bg-card-2); border: 1px solid var(--border);
        color: var(--text-sub); border-radius: 8px;
        width: 32px; height: 32px; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; transition: all 0.2s;
    }
    .modal-close:hover { background: var(--danger-dim); color: var(--danger); border-color: var(--danger); }

    .modal-icon {
        width: 60px; height: 60px;
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px;
        margin: 0 auto 18px;
    }

    .modal-amount {
        text-align: center;
        font-family: var(--mono);
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .modal-type-label {
        text-align: center;
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 24px;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 11px 0;
        border-bottom: 1px solid var(--border);
        font-size: 13.5px;
        gap: 12px;
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-key  { color: var(--text-muted); flex-shrink: 0; }
    .detail-val  { color: var(--text); font-weight: 500; text-align: right; font-family: var(--mono); font-size: 13px; word-break: break-all; }

    /* ── Pagination ── */
    .pagination-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .pagination-info { font-size: 13px; color: var(--text-muted); }

    .pagination-btns { display: flex; gap: 6px; }

    .page-btn {
        width: 36px; height: 36px;
        background: var(--bg-card-2);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        color: var(--text-sub);
        font-size: 13px;
        font-family: var(--font);
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: all 0.2s;
        text-decoration: none;
    }
    .page-btn:hover  { border-color: var(--accent); color: var(--accent); }
    .page-btn.active { background: var(--accent-dim); border-color: var(--accent); color: var(--accent); font-weight: 700; }
    .page-btn:disabled, .page-btn.disabled { opacity: 0.35; pointer-events: none; }

    /* ── Empty State ── */
    .empty-tx {
        text-align: center;
        padding: 60px 20px;
    }
    .empty-tx-icon {
        width: 72px; height: 72px;
        background: var(--bg-card-2);
        border: 1px solid var(--border);
        border-radius: 20px;
        display: flex; align-items: center; justify-content: center;
        font-size: 28px; color: var(--text-muted);
        margin: 0 auto 20px;
    }
    .empty-tx h3 { font-size: 16px; margin-bottom: 6px; }
    .empty-tx p  { font-size: 13.5px; color: var(--text-muted); }

    /* ── Export Button ── */
    .export-btn {
        display: inline-flex; align-items: center; gap: 7px;
        background: var(--bg-card-2);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        color: var(--text-sub);
        font-size: 13px; font-family: var(--font);
        padding: 9px 16px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }
    .export-btn:hover { border-color: var(--accent); color: var(--accent); }
</style>
@endpush

@section('content')

{{-- ── Page Header ── --}}
<div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:14px;">
    <div>
        <h1 style="font-size:22px; font-weight:700; letter-spacing:-0.5px;">Hesap Hareketleri</h1>
        <p style="font-size:13.5px; color:var(--text-muted); margin-top:4px;">
            {{ $account->iban ?? '' }} · Tüm işlem geçmişiniz
        </p>
    </div>
    <a href="#" onclick="exportCSV()" class="export-btn">
        <i class="fa-solid fa-download" style="font-size:11px;"></i>
        CSV İndir
    </a>
</div>

{{-- ── Summary Cards ── --}}
<div class="summary-row">
    <div class="stat-card">
        <div class="stat-label">Toplam Gelen</div>
        <div class="stat-value" style="font-size:20px; color:var(--accent);">
            +₺{{ number_format($summary['total_in'], 2) }}
        </div>
        <div class="stat-change change-up">
            <i class="fa-solid fa-arrow-down" style="font-size:9px;"></i>
            {{ $summary['count_in'] }} işlem
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Toplam Giden</div>
        <div class="stat-value" style="font-size:20px; color:var(--danger);">
            -₺{{ number_format($summary['total_out'], 2) }}
        </div>
        <div class="stat-change change-down">
            <i class="fa-solid fa-arrow-up" style="font-size:9px;"></i>
            {{ $summary['count_out'] }} işlem
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Bu Ay Net</div>
        @php $net = $summary['total_in'] - $summary['total_out']; @endphp
        <div class="stat-value" style="font-size:20px; color:{{ $net >= 0 ? 'var(--accent)' : 'var(--danger)' }};">
            {{ $net >= 0 ? '+' : '' }}₺{{ number_format($net, 2) }}
        </div>
        <div class="stat-change {{ $net >= 0 ? 'change-up' : 'change-down' }}">
            <i class="fa-solid fa-{{ $net >= 0 ? 'trending-up' : 'trending-down' }}" style="font-size:9px;"></i>
            Aylık denge
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Toplam İşlem</div>
        <div class="stat-value" style="font-size:20px;">{{ $summary['total_count'] }}</div>
        <div class="stat-change change-neutral">
            <i class="fa-solid fa-list" style="font-size:9px;"></i>
            {{ $summary['date_range'] }}
        </div>
    </div>
</div>

{{-- ── Filters ── --}}
<div class="card" style="margin-bottom:18px; padding:16px 22px;">
    <form method="GET" action="{{ route('transactions.index') }}" id="filterForm">
        <div class="filter-bar">
            {{-- Tür Filtresi --}}
            <select name="type" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                <option value="">Tüm İşlemler</option>
                <option value="transfer"   {{ request('type') == 'transfer'   ? 'selected' : '' }}>Havale / EFT</option>
                <option value="deposit"    {{ request('type') == 'deposit'    ? 'selected' : '' }}>Para Yatırma</option>
                <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>Para Çekme</option>
                <option value="payment"    {{ request('type') == 'payment'    ? 'selected' : '' }}>Alışveriş</option>
                <option value="exchange"   {{ request('type') == 'exchange'   ? 'selected' : '' }}>Döviz / Kripto</option>
            </select>

            {{-- Yön Filtresi --}}
            <select name="direction" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                <option value="">Gelir & Gider</option>
                <option value="in"  {{ request('direction') == 'in'  ? 'selected' : '' }}>Yalnızca Gelen</option>
                <option value="out" {{ request('direction') == 'out' ? 'selected' : '' }}>Yalnızca Giden</option>
            </select>

            {{-- Tarih Aralığı --}}
            <input type="date" name="date_from" class="filter-input" value="{{ request('date_from') }}"
                   placeholder="Başlangıç" onchange="document.getElementById('filterForm').submit()">
            <input type="date" name="date_to"   class="filter-input" value="{{ request('date_to') }}"
                   placeholder="Bitiş" onchange="document.getElementById('filterForm').submit()">

            {{-- Arama --}}
            <input type="text" name="search" class="filter-input" style="flex:1; min-width:160px;"
                   value="{{ request('search') }}" placeholder="Açıklama veya referans ara..."
                   oninput="debounceSearch(this)">

            @if(request()->hasAny(['type','direction','date_from','date_to','search']))
            <a href="{{ route('transactions.index') }}"
               style="font-size:12px; color:var(--danger); display:flex; align-items:center; gap:5px; text-decoration:none; white-space:nowrap;">
                <i class="fa-solid fa-xmark"></i> Temizle
            </a>
            @endif
        </div>
    </form>
</div>

{{-- ── Transaction List ── --}}
<div class="card">
    <div class="section-header" style="margin-bottom:8px;">
        <div>
            <div class="section-title">İşlemler</div>
            <div class="section-sub" style="font-size:12px; color:var(--text-muted); margin-top:2px;">
                {{ $transactions->total() }} sonuç bulundu
            </div>
        </div>
    </div>

    @if($transactions->isEmpty())
        <div class="empty-tx">
            <div class="empty-tx-icon">
                <i class="fa-regular fa-clock"></i>
            </div>
            <h3>İşlem bulunamadı</h3>
            <p>Seçili filtrelere uygun işlem geçmişi yok.</p>
        </div>
    @else
        @foreach($transactions as $tx)
        @php
            $isOut = $tx->sender_account_id === $account->id;
            $typeIcons = [
                'transfer'   => ['fa-arrow-right-arrow-left', 'type-transfer'],
                'deposit'    => ['fa-arrow-down-to-line',     'type-deposit'],
                'withdrawal' => ['fa-arrow-up-from-line',     'type-withdrawal'],
                'payment'    => ['fa-bag-shopping',           'type-payment'],
                'exchange'   => ['fa-rotate',                 'type-exchange'],
            ];
            [$icon, $iconClass] = $typeIcons[$tx->type] ?? ['fa-circle', 'type-transfer'];

            $counterparty = $isOut
                ? ($tx->receiverAccount?->user?->full_name ?? 'Harici Hesap')
                : ($tx->senderAccount?->user?->full_name  ?? 'Harici Hesap');
        $counterparty = $isOut
                ? ($tx->receiverAccount?->user?->full_name ?? 'Harici Hesap')
                : ($tx->senderAccount?->user?->full_name  ?? 'Harici Hesap');

            // Veriyi burada bir dizi değişkenine ata
            $modalData = [
                "id"          => $tx->id,
                "type"        => $tx->getTypeLabel(),
                "type_raw"    => $tx->type,
                "amount"      => number_format($tx->amount, 2),
                "direction"   => $isOut ? "out" : "in",
                "description" => $tx->description,
                "reference"   => $tx->reference_no,
                "date"        => $tx->created_at->format("d.m.Y H:i:s"),
                "status"      => $tx->status,
                "currency"    => $tx->currency,
                "fee"         => number_format($tx->fee ?? 0, 2),
                "counterparty"=> $counterparty,
            ];
        @endphp
        
        <div class="tx-row" onclick='openModal(@json($modalData))'>
            <div class="tx-icon {{ $iconClass }}">
                <i class="fa-solid {{ $icon }}"></i>
            </div>
            <div class="tx-meta">
                <div class="tx-title">{{ $tx->getTypeLabel() }}</div>
                <div class="tx-desc">
                    {{ $isOut ? 'Alıcı: ' : 'Gönderen: ' }}{{ $counterparty }}
                    @if($tx->description) · {{ Str::limit($tx->description, 35) }} @endif
                </div>
                <div class="tx-ref">{{ $tx->reference_no }}</div>
            </div>
            <div class="tx-right">
                <div class="tx-amount {{ $isOut ? 'out' : 'in' }}">
                    {{ $isOut ? '-' : '+' }}₺{{ number_format($tx->amount, 2) }}
                </div>
                <div class="tx-date">{{ $tx->created_at->format('d.m.Y · H:i') }}</div>
                <div style="margin-top:4px;">
                    <span class="badge {{ $tx->status === 'completed' ? 'badge-success' : ($tx->status === 'pending' ? 'badge-info' : 'badge-danger') }}"
                          style="font-size:10px; padding:2px 8px;">
                        {{ $tx->status === 'completed' ? 'Tamamlandı' : ($tx->status === 'pending' ? 'Bekliyor' : 'Başarısız') }}
                    </span>
                </div>
            </div>
        </div>
        @endforeach

        {{-- Pagination --}}
        <div class="pagination-wrap">
            <div class="pagination-info">
                {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} / {{ $transactions->total() }} işlem
            </div>
            <div class="pagination-btns">
                <a href="{{ $transactions->previousPageUrl() ?? '#' }}"
                   class="page-btn {{ !$transactions->onFirstPage() ?: 'disabled' }}">
                    <i class="fa-solid fa-chevron-left" style="font-size:11px;"></i>
                </a>
                @foreach($transactions->getUrlRange(max(1,$transactions->currentPage()-2), min($transactions->lastPage(),$transactions->currentPage()+2)) as $page => $url)
                    <a href="{{ $url }}" class="page-btn {{ $page == $transactions->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                @endforeach
                <a href="{{ $transactions->nextPageUrl() ?? '#' }}"
                   class="page-btn {{ $transactions->hasMorePages() ?: 'disabled' }}">
                    <i class="fa-solid fa-chevron-right" style="font-size:11px;"></i>
                </a>
            </div>
        </div>
    @endif
</div>

{{-- ── Detail Modal ── --}}
<div class="modal-overlay" id="txModal" onclick="closeModalOutside(event)">
    <div class="modal-box" id="txModalBox">
        <button class="modal-close" onclick="closeModal()">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="modal-icon" id="mIcon"></div>
        <div class="modal-amount" id="mAmount"></div>
        <div class="modal-type-label" id="mLabel"></div>

        <div id="mDetails"></div>

        <button onclick="closeModal()" class="btn btn-secondary" style="width:100%; margin-top:20px; justify-content:center;">
            Kapat
        </button>
    </div>
</div>

@endsection

@push('scripts')
<script>
const typeIconMap = {
    transfer:   ['fa-arrow-right-arrow-left', 'type-transfer'],
    deposit:    ['fa-arrow-down-to-line',     'type-deposit'],
    withdrawal: ['fa-arrow-up-from-line',     'type-withdrawal'],
    payment:    ['fa-bag-shopping',           'type-payment'],
    exchange:   ['fa-rotate',                 'type-exchange'],
};

function openModal(data) {
    const modal = document.getElementById('txModal');
    const [iconClass, colorClass] = typeIconMap[data.type_raw] ?? ['fa-circle', 'type-transfer'];

    document.getElementById('mIcon').className = `modal-icon ${colorClass}`;
    document.getElementById('mIcon').innerHTML = `<i class="fa-solid ${iconClass}"></i>`;

    const sign = data.direction === 'out' ? '-' : '+';
    const color = data.direction === 'out' ? 'var(--danger)' : 'var(--accent)';
    document.getElementById('mAmount').style.color = color;
    document.getElementById('mAmount').textContent = `${sign}₺${data.amount}`;
    document.getElementById('mLabel').textContent = data.type;

    const rows = [
        ['İşlem No',      data.id],
        ['Referans',      data.reference],
        [data.direction === 'out' ? 'Alıcı' : 'Gönderen', data.counterparty],
        ['Açıklama',      data.description || '—'],
        ['Tarih',         data.date],
        ['Para Birimi',   data.currency],
        ['Komisyon',      `₺${data.fee}`],
        ['Durum',         data.status === 'completed' ? '✓ Tamamlandı' : data.status === 'pending' ? '⏳ Bekliyor' : '✗ Başarısız'],
    ];

    document.getElementById('mDetails').innerHTML = rows.map(([k,v]) =>
        `<div class="detail-row"><span class="detail-key">${k}</span><span class="detail-val">${v}</span></div>`
    ).join('');

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('txModal').classList.remove('open');
    document.body.style.overflow = '';
}

function closeModalOutside(e) {
    if (e.target === document.getElementById('txModal')) closeModal();
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

// Debounced search
let searchTimer;
function debounceSearch(input) {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        document.getElementById('filterForm').submit();
    }, 500);
}

// CSV Export
function exportCSV() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.location.href = '{{ route("transactions.index") }}?' + params.toString();
}
</script>
@endpush
