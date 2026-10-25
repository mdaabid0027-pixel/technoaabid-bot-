@php
    $pName = strtolower($p['name']);
    $isBitAim = str_contains($pName, 'bitaim') || str_contains($pName, 'bit');
    $isCarrom = str_contains($pName, 'carrom');
    $isSoccer = str_contains($pName, 'soccer');
    $isSnake = str_contains($pName, 'snake') && !$isSoccer;
    $isKos = str_contains($pName, 'kos') || str_contains($pName, 'ks');
    $isAimAi = str_contains($pName, 'ai') || str_contains($pName, 'aim ai');
@endphp

<div class="stock-folder-card">
    <div class="stock-folder-header">
        <div style="display: flex; align-items: center; gap: 8px;">
            @if($isSoccer)
                <i class="fa-solid fa-futbol" style="color: #059669; font-size: 16px;"></i>
            @elseif($isBitAim)
                <i class="fa-solid fa-rocket" style="color: #ec4899; font-size: 16px;"></i>
            @elseif($isCarrom)
                <i class="fa-solid fa-bullseye" style="color: #ef4444; font-size: 16px;"></i>
            @elseif($isSnake)
                <i class="fa-solid fa-staff-snake" style="color: #f59e0b; font-size: 16px;"></i>
            @elseif($isKos)
                <i class="fa-solid fa-crosshairs" style="color: #3b82f6; font-size: 16px;"></i>
            @elseif($isAimAi)
                <i class="fa-solid fa-robot" style="color: #8b5cf6; font-size: 16px;"></i>
            @else
                <i class="fa-solid fa-gamepad" style="color: #2563eb; font-size: 16px;"></i>
            @endif
            <span style="font-size: 14px; font-weight: 800; color: #0f172a;">{{ $p['name'] }}</span>
        </div>
        
        <span style="font-size: 11px; font-weight: 800; background: {{ $p['total_available'] > 0 ? '#ecfdf5' : '#fef2f2' }}; color: {{ $p['total_available'] > 0 ? '#059669' : '#dc2626' }}; border: 1px solid {{ $p['total_available'] > 0 ? '#a7f3d0' : '#fecaca' }}; padding: 3px 8px; border-radius: 9999px;">
            {{ $p['total_available'] }} Available
        </span>
    </div>

    <div class="stock-folder-body">
        @foreach($p['plans'] as $plan)
            <div class="plan-stock-row">
                <div style="display: flex; flex-direction: column; gap: 2px;">
                    <span style="font-size: 12px; font-weight: 700; color: #1e293b;">
                        {{ $plan['label'] }}
                    </span>
                    <div>
                        @if($plan['count'] > 5)
                            <span class="stock-badge-healthy"><i class="fa-solid fa-check me-1"></i> {{ $plan['count'] }} in stock</span>
                        @elseif($plan['count'] > 0)
                            <span class="stock-badge-low"><i class="fa-solid fa-triangle-exclamation me-1"></i> {{ $plan['count'] }} left</span>
                        @else
                            <span class="stock-badge-empty"><i class="fa-solid fa-xmark me-1"></i> Out of stock</span>
                        @endif
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 6px;">
                    <!-- Quick Add Button -->
                    <button type="button" class="btn-stock-add" onclick="quickAddFromCard('{{ $p['id'] }}', '{{ $plan['key'] }}', '{{ addslashes($p['name']) }} - {{ addslashes($plan['label']) }}')" title="Add keys to this plan">
                        <i class="fa-solid fa-plus"></i> Add
                    </button>

                    <!-- Quick View Batch Button -->
                    @if($plan['count'] > 0)
                        <button type="button" class="btn-stock-view" onclick="openBatchModal('{{ $p['id'] }}', '{{ $plan['key'] }}', '{{ addslashes($p['name']) }} - {{ addslashes($plan['label']) }}')" title="View and copy keys">
                            <i class="fa-regular fa-eye"></i> {{ $plan['count'] }}
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
