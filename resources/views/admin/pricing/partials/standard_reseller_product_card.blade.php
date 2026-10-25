@php
    $pName = strtolower($prod->name);
    $isBitAim = str_contains($pName, 'bitaim') || str_contains($pName, 'bit');
    $isCarrom = str_contains($pName, 'carrom');
    $isSoccer = str_contains($pName, 'soccer');
    $isSnake = str_contains($pName, 'snake') && !$isSoccer;
    $isKos = str_contains($pName, 'kos') || str_contains($pName, 'ks');
    $isAimAi = str_contains($pName, 'ai') || str_contains($pName, 'aim ai');

    $fmt = function($val) {
        if ($val === null || $val === '' || (float)$val <= 0) return '';
        $f = (float)$val;
        return ($f == (int)$f) ? (string)((int)$f) : number_format($f, 2, '.', '');
    };
@endphp

<div class="pricing-card standard-reseller-card" 
     id="std_card_{{ $prod->id }}"
     data-prod-id="{{ $prod->id }}"
     data-prod-name="{{ $prod->name }}"
     data-cust-reseller_price_1d="{{ $prod->price_1d }}"
     data-cust-reseller_price_3d="{{ $prod->price_3d }}"
     data-cust-reseller_price_3d_queue="{{ $prod->price_3d_queue ?: $prod->price_3d }}"
     data-cust-reseller_price_7d="{{ $prod->price_7d }}"
     data-cust-reseller_price_7d_queue="{{ $prod->price_7d_queue ?: $prod->price_7d }}"
     data-cust-reseller_price_10d="{{ $prod->price_10d }}"
     data-cust-reseller_price_15d="{{ $prod->price_15d }}"
     data-cust-reseller_price_30d="{{ $prod->price_30d }}"
     data-cust-reseller_price_30d_queue="{{ $prod->price_30d_queue ?: $prod->price_30d }}"
     data-cust-reseller_price_90d="{{ $prod->price_90d }}"
     data-cust-reseller_price_90d_queue="{{ $prod->price_90d_queue ?: $prod->price_90d }}"
     data-cust-reseller_price_lifetime="{{ $prod->price_lifetime }}"
     style="border-color: #cbd5e1;">
    <div class="pricing-card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
        <div class="pricing-card-title">
            @if($isSoccer)
                <i class="fa-solid fa-futbol" style="color: #059669;"></i>
            @elseif($isBitAim)
                <i class="fa-solid fa-rocket" style="color: #ec4899;"></i>
            @elseif($isCarrom)
                <i class="fa-solid fa-bullseye" style="color: #ef4444;"></i>
            @elseif($isSnake)
                <i class="fa-solid fa-staff-snake" style="color: #f59e0b;"></i>
            @elseif($isKos)
                <i class="fa-solid fa-gamepad" style="color: #3b82f6;"></i>
            @elseif($isAimAi)
                <i class="fa-solid fa-robot" style="color: #8b5cf6;"></i>
            @else
                <i class="fa-solid fa-gamepad" style="color: #2563eb;"></i>
            @endif
            <span>{{ $prod->name }}</span>
        </div>

        <div style="display: flex; align-items: center; gap: 6px;">
            <input type="number" id="card_pct_input_{{ $prod->id }}" placeholder="%" min="0.1" max="99" step="0.5" style="width: 52px; padding: 3px 6px; font-size: 11px; font-weight: 700; border: 1px solid #cbd5e1; border-radius: 5px; text-align: center; outline: none; background: #fff;" onkeydown="if(event.key==='Enter'){event.preventDefault(); applyCustomCardDiscount({{ $prod->id }});}">
            <button type="button" class="btn-card-pct-apply" onclick="applyCustomCardDiscount({{ $prod->id }})" title="Apply % discount" style="padding: 3px 8px; font-size: 11px; font-weight: 700; border-radius: 5px;">Apply</button>
            <button type="button" class="btn-card-reset" onclick="resetCardToCustomerPrice({{ $prod->id }})" title="Reset this product rates back to 100% Customer Price" style="padding: 3px 8px; font-size: 11px; border-radius: 5px;">
                <i class="fa-solid fa-rotate-left"></i> Reset
            </button>
        </div>
    </div>

    <div class="pricing-card-body">
        <form action="{{ route('admin.pricing.update_standard_reseller', $prod->id) }}" method="POST" onsubmit="saveStandardResellerPricesAjax(event, this, '{{ addslashes($prod->name) }}')">
            @csrf
            @method('PUT')

            @if($isSoccer)
                <!-- SOCCER STAR: 3D, 10D, 30D -->
                <div class="plans-grid">
                    @include('admin.pricing.partials.duration_box', ['label' => '3 Days (3D)', 'priceKey' => 'reseller_price_3d', 'costKey' => 'cost_3d', 'priceVal' => ($prod->reseller_price_3d > 0 ? $prod->reseller_price_3d : $prod->price_3d), 'costVal' => $prod->cost_3d])
                    @include('admin.pricing.partials.duration_box', ['label' => '10 Days (10D)', 'priceKey' => 'reseller_price_10d', 'costKey' => 'cost_10d', 'priceVal' => ($prod->reseller_price_10d > 0 ? $prod->reseller_price_10d : $prod->price_10d), 'costVal' => $prod->cost_10d])
                    @include('admin.pricing.partials.duration_box', ['label' => '30 Days (30D)', 'priceKey' => 'reseller_price_30d', 'costKey' => 'cost_30d', 'priceVal' => ($prod->reseller_price_30d > 0 ? $prod->reseller_price_30d : $prod->price_30d), 'costVal' => $prod->cost_30d])
                </div>

            @elseif($isBitAim)
                <!-- BITAIM+: 7D, 30D, 90D, LIFETIME -->
                <div class="plans-grid">
                    @include('admin.pricing.partials.duration_box', ['label' => '7 Days (1 Week)', 'priceKey' => 'reseller_price_7d', 'costKey' => 'cost_7d', 'priceVal' => ($prod->reseller_price_7d > 0 ? $prod->reseller_price_7d : $prod->price_7d), 'costVal' => $prod->cost_7d])
                    @include('admin.pricing.partials.duration_box', ['label' => '30 Days (1 Month)', 'priceKey' => 'reseller_price_30d', 'costKey' => 'cost_30d', 'priceVal' => ($prod->reseller_price_30d > 0 ? $prod->reseller_price_30d : $prod->price_30d), 'costVal' => $prod->cost_30d])
                    @include('admin.pricing.partials.duration_box', ['label' => '90 Days (3 Months)', 'priceKey' => 'reseller_price_90d', 'costKey' => 'cost_90d', 'priceVal' => ($prod->reseller_price_90d > 0 ? $prod->reseller_price_90d : $prod->price_90d), 'costVal' => $prod->cost_90d])
                    @include('admin.pricing.partials.duration_box', ['label' => '💎 Lifetime', 'priceKey' => 'reseller_price_lifetime', 'costKey' => 'cost_lifetime', 'priceVal' => ($prod->reseller_price_lifetime > 0 ? $prod->reseller_price_lifetime : $prod->price_lifetime), 'costVal' => $prod->cost_lifetime, 'isHighlight' => true])
                </div>

            @elseif($isCarrom)
                <!-- AIM CARROM KING: NORMAL MOD & AUTO QUEUE MOD -->
                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #334155; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-shield-halved" style="color: #3b82f6;"></i> Normal Mod Reseller Rates
                </div>
                <div class="plans-grid" style="margin-bottom: 14px;">
                    @include('admin.pricing.partials.duration_box', ['label' => '3 Days (3D)', 'priceKey' => 'reseller_price_3d', 'costKey' => 'cost_3d', 'priceVal' => ($prod->reseller_price_3d > 0 ? $prod->reseller_price_3d : $prod->price_3d), 'costVal' => $prod->cost_3d])
                    @include('admin.pricing.partials.duration_box', ['label' => '7 Days (7D)', 'priceKey' => 'reseller_price_7d', 'costKey' => 'cost_7d', 'priceVal' => ($prod->reseller_price_7d > 0 ? $prod->reseller_price_7d : $prod->price_7d), 'costVal' => $prod->cost_7d])
                    @include('admin.pricing.partials.duration_box', ['label' => '30 Days (30D)', 'priceKey' => 'reseller_price_30d', 'costKey' => 'cost_30d', 'priceVal' => ($prod->reseller_price_30d > 0 ? $prod->reseller_price_30d : $prod->price_30d), 'costVal' => $prod->cost_30d])
                    @include('admin.pricing.partials.duration_box', ['label' => '90 Days (90D)', 'priceKey' => 'reseller_price_90d', 'costKey' => 'cost_90d', 'priceVal' => ($prod->reseller_price_90d > 0 ? $prod->reseller_price_90d : $prod->price_90d), 'costVal' => $prod->cost_90d])
                </div>

                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #7c3aed; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-bolt" style="color: #7c3aed;"></i> Auto Queue Mod Reseller Rates
                </div>
                <div class="plans-grid">
                    @include('admin.pricing.partials.duration_box', ['label' => '3D Queue', 'priceKey' => 'reseller_price_3d_queue', 'costKey' => 'cost_3d_queue', 'priceVal' => ($prod->reseller_price_3d_queue > 0 ? $prod->reseller_price_3d_queue : ($prod->price_3d_queue > 0 ? $prod->price_3d_queue : $prod->price_3d)), 'costVal' => $prod->cost_3d_queue, 'isQueue' => true])
                    @include('admin.pricing.partials.duration_box', ['label' => '7D Queue', 'priceKey' => 'reseller_price_7d_queue', 'costKey' => 'cost_7d_queue', 'priceVal' => ($prod->reseller_price_7d_queue > 0 ? $prod->reseller_price_7d_queue : ($prod->price_7d_queue > 0 ? $prod->price_7d_queue : $prod->price_7d)), 'costVal' => $prod->cost_7d_queue, 'isQueue' => true])
                    @include('admin.pricing.partials.duration_box', ['label' => '30D Queue', 'priceKey' => 'reseller_price_30d_queue', 'costKey' => 'cost_30d_queue', 'priceVal' => ($prod->reseller_price_30d_queue > 0 ? $prod->reseller_price_30d_queue : ($prod->price_30d_queue > 0 ? $prod->price_30d_queue : $prod->price_30d)), 'costVal' => $prod->cost_30d_queue, 'isQueue' => true])
                    @include('admin.pricing.partials.duration_box', ['label' => '90D Queue', 'priceKey' => 'reseller_price_90d_queue', 'costKey' => 'cost_90d_queue', 'priceVal' => ($prod->reseller_price_90d_queue > 0 ? $prod->reseller_price_90d_queue : ($prod->price_90d_queue > 0 ? $prod->price_90d_queue : $prod->price_90d)), 'costVal' => $prod->cost_90d_queue, 'isQueue' => true])
                </div>

            @elseif($isKos)
                <!-- KOS ENGINE: 1D, 7D, 15D, 30D -->
                <div class="plans-grid">
                    @include('admin.pricing.partials.duration_box', ['label' => '1 Day (1D)', 'priceKey' => 'reseller_price_1d', 'costKey' => 'cost_1d', 'priceVal' => ($prod->reseller_price_1d > 0 ? $prod->reseller_price_1d : $prod->price_1d), 'costVal' => $prod->cost_1d])
                    @include('admin.pricing.partials.duration_box', ['label' => '7 Days (7D)', 'priceKey' => 'reseller_price_7d', 'costKey' => 'cost_7d', 'priceVal' => ($prod->reseller_price_7d > 0 ? $prod->reseller_price_7d : $prod->price_7d), 'costVal' => $prod->cost_7d])
                    @include('admin.pricing.partials.duration_box', ['label' => '15 Days (15D)', 'priceKey' => 'reseller_price_15d', 'costKey' => 'cost_15d', 'priceVal' => ($prod->reseller_price_15d > 0 ? $prod->reseller_price_15d : $prod->price_15d), 'costVal' => $prod->cost_15d])
                    @include('admin.pricing.partials.duration_box', ['label' => '30 Days (30D)', 'priceKey' => 'reseller_price_30d', 'costKey' => 'cost_30d', 'priceVal' => ($prod->reseller_price_30d > 0 ? $prod->reseller_price_30d : $prod->price_30d), 'costVal' => $prod->cost_30d])
                </div>

            @elseif($isSnake)
                <!-- SNAKE ENGINE: 3D, 10D, 30D, 90D -->
                <div class="plans-grid">
                    @include('admin.pricing.partials.duration_box', ['label' => '3 Days (3D)', 'priceKey' => 'reseller_price_3d', 'costKey' => 'cost_3d', 'priceVal' => ($prod->reseller_price_3d > 0 ? $prod->reseller_price_3d : $prod->price_3d), 'costVal' => $prod->cost_3d])
                    @include('admin.pricing.partials.duration_box', ['label' => '10 Days (10D)', 'priceKey' => 'reseller_price_10d', 'costKey' => 'cost_10d', 'priceVal' => ($prod->reseller_price_10d > 0 ? $prod->reseller_price_10d : $prod->price_10d), 'costVal' => $prod->cost_10d])
                    @include('admin.pricing.partials.duration_box', ['label' => '30 Days (30D)', 'priceKey' => 'reseller_price_30d', 'costKey' => 'cost_30d', 'priceVal' => ($prod->reseller_price_30d > 0 ? $prod->reseller_price_30d : $prod->price_30d), 'costVal' => $prod->cost_30d])
                    @include('admin.pricing.partials.duration_box', ['label' => '90 Days (90D)', 'priceKey' => 'reseller_price_90d', 'costKey' => 'cost_90d', 'priceVal' => ($prod->reseller_price_90d > 0 ? $prod->reseller_price_90d : $prod->price_90d), 'costVal' => $prod->cost_90d])
                </div>

            @elseif($isAimAi)
                <!-- AIM AI ENGINE: 1D, 3D, 7D, 15D, 30D, 90D -->
                <div class="plans-grid">
                    @include('admin.pricing.partials.duration_box', ['label' => '1 Day (1D)', 'priceKey' => 'reseller_price_1d', 'costKey' => 'cost_1d', 'priceVal' => ($prod->reseller_price_1d > 0 ? $prod->reseller_price_1d : $prod->price_1d), 'costVal' => $prod->cost_1d])
                    @include('admin.pricing.partials.duration_box', ['label' => '3 Days (3D)', 'priceKey' => 'reseller_price_3d', 'costKey' => 'cost_3d', 'priceVal' => ($prod->reseller_price_3d > 0 ? $prod->reseller_price_3d : $prod->price_3d), 'costVal' => $prod->cost_3d])
                    @include('admin.pricing.partials.duration_box', ['label' => '7 Days (7D)', 'priceKey' => 'reseller_price_7d', 'costKey' => 'cost_7d', 'priceVal' => ($prod->reseller_price_7d > 0 ? $prod->reseller_price_7d : $prod->price_7d), 'costVal' => $prod->cost_7d])
                    @include('admin.pricing.partials.duration_box', ['label' => '15 Days (15D)', 'priceKey' => 'reseller_price_15d', 'costKey' => 'cost_15d', 'priceVal' => ($prod->reseller_price_15d > 0 ? $prod->reseller_price_15d : $prod->price_15d), 'costVal' => $prod->cost_15d])
                    @include('admin.pricing.partials.duration_box', ['label' => '30 Days (30D)', 'priceKey' => 'reseller_price_30d', 'costKey' => 'cost_30d', 'priceVal' => ($prod->reseller_price_30d > 0 ? $prod->reseller_price_30d : $prod->price_30d), 'costVal' => $prod->cost_30d])
                    @include('admin.pricing.partials.duration_box', ['label' => '90 Days (90D)', 'priceKey' => 'reseller_price_90d', 'costKey' => 'cost_90d', 'priceVal' => ($prod->reseller_price_90d > 0 ? $prod->reseller_price_90d : $prod->price_90d), 'costVal' => $prod->cost_90d])
                </div>

            @else
                <!-- DEFAULT GENERIC PLANS -->
                <div class="plans-grid">
                    @include('admin.pricing.partials.duration_box', ['label' => '1 Day (1D)', 'priceKey' => 'reseller_price_1d', 'costKey' => 'cost_1d', 'priceVal' => ($prod->reseller_price_1d > 0 ? $prod->reseller_price_1d : $prod->price_1d), 'costVal' => $prod->cost_1d])
                    @include('admin.pricing.partials.duration_box', ['label' => '7 Days (7D)', 'priceKey' => 'reseller_price_7d', 'costKey' => 'cost_7d', 'priceVal' => ($prod->reseller_price_7d > 0 ? $prod->reseller_price_7d : $prod->price_7d), 'costVal' => $prod->cost_7d])
                    @include('admin.pricing.partials.duration_box', ['label' => '30 Days (30D)', 'priceKey' => 'reseller_price_30d', 'costKey' => 'cost_30d', 'priceVal' => ($prod->reseller_price_30d > 0 ? $prod->reseller_price_30d : $prod->price_30d), 'costVal' => $prod->cost_30d])
                </div>
            @endif

            <button type="submit" class="btn" style="width: 100%; margin-top: 14px; background: #4f46e5; color: #ffffff; font-weight: 700; padding: 10px 16px; border-radius: 8px; font-size: 13px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; border: none; transition: all 0.2s;">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Save Standard Reseller Prices</span>
            </button>
        </form>
    </div>
</div>
