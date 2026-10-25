@php
    $sell = (float)($priceVal ?? 0);
    $cost = (float)($costVal ?? 0);
    $hasProfit = ($sell > 0 && $cost > 0);
    $profit = $hasProfit ? ($sell - $cost) : null;
    $profitPercent = ($hasProfit && $cost > 0) ? round(($profit / $cost) * 100) : null;
    
    $cleanSellVal = ($sell > 0) ? (($sell == (int)$sell) ? (int)$sell : number_format($sell, 2, '.', '')) : '';
    $cleanCostVal = ($cost > 0) ? (($cost == (int)$cost) ? (int)$cost : number_format($cost, 2, '.', '')) : '';

    $isQueue = $isQueue ?? false;
    $isHighlight = $isHighlight ?? false;
@endphp

<div class="plan-box" style="{{ $isHighlight ? 'background: #fdf2f8; border-color: #fbcfe8;' : ($isQueue ? 'background: #faf5ff; border-color: #e9d5ff;' : '') }}">
    <div class="plan-box-label" style="{{ $isHighlight ? 'color: #be185d;' : ($isQueue ? 'color: #6b21a8;' : '') }}">
        <span>{{ $label }}</span>
        @if($profit !== null)
            <span class="profit-badge" style="font-size: 10px; font-weight: 800; color: {{ $profit >= 0 ? '#059669' : '#dc2626' }};" title="Profit per key">
                {{ $profit >= 0 ? '+₹' . number_format($profit, 0) : '-₹' . number_format(abs($profit), 0) }}
                @if($profitPercent !== null) ({{ $profitPercent }}%) @endif
            </span>
        @endif
    </div>

    <div class="plan-box-grid">
        <!-- Selling Price -->
        <div style="flex: 1;">
            <div class="sell-col-label" style="font-size: 9px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 2px;">Sell ₹</div>
            <div class="plan-input-wrap">
                <span class="plan-currency default-currency-symbol" style="color: {{ $isHighlight ? '#db2777' : ($isQueue ? '#7c3aed' : '#059669') }};">₹</span>
                <input type="number" step="any" name="{{ $priceKey }}" value="{{ $cleanSellVal }}" class="plan-input" placeholder="0" style="font-weight: 800; font-size: 15px; color: {{ $isHighlight ? '#db2777' : ($isQueue ? '#7c3aed' : '#0f172a') }};">
            </div>
        </div>

        <!-- Purchase Cost Price (Shown only when Cost Mode is active) -->
        <div class="cost-col" style="border-left: 1px dashed #cbd5e1; padding-left: 8px;">
            <div style="font-size: 9px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 2px;">Cost ₹</div>
            <div class="plan-input-wrap">
                <input type="number" step="any" name="{{ $costKey }}" value="{{ $cleanCostVal }}" class="plan-input" placeholder="0" style="font-weight: 700; font-size: 13px; color: #64748b;">
            </div>
        </div>
    </div>
</div>
