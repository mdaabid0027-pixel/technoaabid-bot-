@php
    $pName = strtolower($prod->name);
    $isBitAim = str_contains($pName, 'bitaim') || str_contains($pName, 'bit');
    $isCarrom = str_contains($pName, 'carrom');
    $isSoccer = str_contains($pName, 'soccer');
    $isSnake = str_contains($pName, 'snake') && !$isSoccer;
    $isKos = str_contains($pName, 'kos') || str_contains($pName, 'ks');
    $isAimAi = str_contains($pName, 'ai') || str_contains($pName, 'aim ai');
@endphp

<div class="pricing-card">
    <div class="pricing-card-header">
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
        <span style="font-size: 11px; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 6px;">
            {{ $prod->game_name ?: ($prod->category->name ?? 'Game') }}
        </span>
    </div>

    <div class="pricing-card-body">
        <form action="{{ route('admin.pricing.update_customer', $prod->id) }}" method="POST" onsubmit="saveCustomerPricesAjax(event, this, '{{ addslashes($prod->name) }}')">
            @csrf
            @method('PUT')

            @if($isSoccer)
                <!-- SOCCER STAR: 3D, 10D, 30D -->
                <div class="plans-grid">
                    @include('admin.pricing.partials.duration_box', ['label' => '3 Days (3D)', 'priceKey' => 'price_3d', 'costKey' => 'cost_3d', 'priceVal' => $prod->price_3d, 'costVal' => $prod->cost_3d])
                    @include('admin.pricing.partials.duration_box', ['label' => '10 Days (10D)', 'priceKey' => 'price_10d', 'costKey' => 'cost_10d', 'priceVal' => $prod->price_10d, 'costVal' => $prod->cost_10d])
                    @include('admin.pricing.partials.duration_box', ['label' => '30 Days (30D)', 'priceKey' => 'price_30d', 'costKey' => 'cost_30d', 'priceVal' => $prod->price_30d, 'costVal' => $prod->cost_30d])
                </div>

            @elseif($isBitAim)
                <!-- BITAIM+: 7D, 30D, 90D, LIFETIME -->
                <div class="plans-grid">
                    @include('admin.pricing.partials.duration_box', ['label' => '7 Days (1 Week)', 'priceKey' => 'price_7d', 'costKey' => 'cost_7d', 'priceVal' => $prod->price_7d, 'costVal' => $prod->cost_7d])
                    @include('admin.pricing.partials.duration_box', ['label' => '30 Days (1 Month)', 'priceKey' => 'price_30d', 'costKey' => 'cost_30d', 'priceVal' => $prod->price_30d, 'costVal' => $prod->cost_30d])
                    @include('admin.pricing.partials.duration_box', ['label' => '90 Days (3 Months)', 'priceKey' => 'price_90d', 'costKey' => 'cost_90d', 'priceVal' => $prod->price_90d, 'costVal' => $prod->cost_90d])
                    @include('admin.pricing.partials.duration_box', ['label' => '💎 Lifetime', 'priceKey' => 'price_lifetime', 'costKey' => 'cost_lifetime', 'priceVal' => $prod->price_lifetime, 'costVal' => $prod->cost_lifetime, 'isHighlight' => true])
                </div>

            @elseif($isCarrom)
                <!-- AIM CARROM KING: NORMAL MOD & AUTO QUEUE MOD -->
                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #334155; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-shield-halved" style="color: #3b82f6;"></i> Normal Mod Plans
                </div>
                <div class="plans-grid" style="margin-bottom: 14px;">
                    @include('admin.pricing.partials.duration_box', ['label' => '3 Days (3D)', 'priceKey' => 'price_3d', 'costKey' => 'cost_3d', 'priceVal' => $prod->price_3d, 'costVal' => $prod->cost_3d])
                    @include('admin.pricing.partials.duration_box', ['label' => '7 Days (7D)', 'priceKey' => 'price_7d', 'costKey' => 'cost_7d', 'priceVal' => $prod->price_7d, 'costVal' => $prod->cost_7d])
                    @include('admin.pricing.partials.duration_box', ['label' => '30 Days (30D)', 'priceKey' => 'price_30d', 'costKey' => 'cost_30d', 'priceVal' => $prod->price_30d, 'costVal' => $prod->cost_30d])
                    @include('admin.pricing.partials.duration_box', ['label' => '90 Days (90D)', 'priceKey' => 'price_90d', 'costKey' => 'cost_90d', 'priceVal' => $prod->price_90d, 'costVal' => $prod->cost_90d])
                </div>

                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #7c3aed; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-bolt" style="color: #7c3aed;"></i> Auto Queue Mod Plans
                </div>
                <div class="plans-grid">
                    @include('admin.pricing.partials.duration_box', ['label' => '3D Queue', 'priceKey' => 'price_3d_queue', 'costKey' => 'cost_3d_queue', 'priceVal' => ($prod->price_3d_queue > 0 ? $prod->price_3d_queue : $prod->price_3d), 'costVal' => $prod->cost_3d_queue, 'isQueue' => true])
                    @include('admin.pricing.partials.duration_box', ['label' => '7D Queue', 'priceKey' => 'price_7d_queue', 'costKey' => 'cost_7d_queue', 'priceVal' => ($prod->price_7d_queue > 0 ? $prod->price_7d_queue : $prod->price_7d), 'costVal' => $prod->cost_7d_queue, 'isQueue' => true])
                    @include('admin.pricing.partials.duration_box', ['label' => '30D Queue', 'priceKey' => 'price_30d_queue', 'costKey' => 'cost_30d_queue', 'priceVal' => ($prod->price_30d_queue > 0 ? $prod->price_30d_queue : $prod->price_30d), 'costVal' => $prod->cost_30d_queue, 'isQueue' => true])
                    @include('admin.pricing.partials.duration_box', ['label' => '90D Queue', 'priceKey' => 'price_90d_queue', 'costKey' => 'cost_90d_queue', 'priceVal' => ($prod->price_90d_queue > 0 ? $prod->price_90d_queue : $prod->price_90d), 'costVal' => $prod->cost_90d_queue, 'isQueue' => true])
                </div>

            @elseif($isKos)
                <!-- KOS ENGINE: 1D, 7D, 15D, 30D -->
                <div class="plans-grid">
                    @include('admin.pricing.partials.duration_box', ['label' => '1 Day (1D)', 'priceKey' => 'price_1d', 'costKey' => 'cost_1d', 'priceVal' => $prod->price_1d, 'costVal' => $prod->cost_1d])
                    @include('admin.pricing.partials.duration_box', ['label' => '7 Days (7D)', 'priceKey' => 'price_7d', 'costKey' => 'cost_7d', 'priceVal' => $prod->price_7d, 'costVal' => $prod->cost_7d])
                    @include('admin.pricing.partials.duration_box', ['label' => '15 Days (15D)', 'priceKey' => 'price_15d', 'costKey' => 'cost_15d', 'priceVal' => $prod->price_15d, 'costVal' => $prod->cost_15d])
                    @include('admin.pricing.partials.duration_box', ['label' => '30 Days (30D)', 'priceKey' => 'price_30d', 'costKey' => 'cost_30d', 'priceVal' => $prod->price_30d, 'costVal' => $prod->cost_30d])
                </div>

            @elseif($isSnake)
                <!-- SNAKE ENGINE: 3D, 10D, 30D, 90D -->
                <div class="plans-grid">
                    @include('admin.pricing.partials.duration_box', ['label' => '3 Days (3D)', 'priceKey' => 'price_3d', 'costKey' => 'cost_3d', 'priceVal' => $prod->price_3d, 'costVal' => $prod->cost_3d])
                    @include('admin.pricing.partials.duration_box', ['label' => '10 Days (10D)', 'priceKey' => 'price_10d', 'costKey' => 'cost_10d', 'priceVal' => $prod->price_10d, 'costVal' => $prod->cost_10d])
                    @include('admin.pricing.partials.duration_box', ['label' => '30 Days (30D)', 'priceKey' => 'price_30d', 'costKey' => 'cost_30d', 'priceVal' => $prod->price_30d, 'costVal' => $prod->cost_30d])
                    @include('admin.pricing.partials.duration_box', ['label' => '90 Days (90D)', 'priceKey' => 'price_90d', 'costKey' => 'cost_90d', 'priceVal' => $prod->price_90d, 'costVal' => $prod->cost_90d])
                </div>

            @elseif($isAimAi)
                <!-- AIM AI ENGINE: 1D, 3D, 7D, 15D, 30D, 90D -->
                <div class="plans-grid">
                    @include('admin.pricing.partials.duration_box', ['label' => '1 Day (1D)', 'priceKey' => 'price_1d', 'costKey' => 'cost_1d', 'priceVal' => $prod->price_1d, 'costVal' => $prod->cost_1d])
                    @include('admin.pricing.partials.duration_box', ['label' => '3 Days (3D)', 'priceKey' => 'price_3d', 'costKey' => 'cost_3d', 'priceVal' => $prod->price_3d, 'costVal' => $prod->cost_3d])
                    @include('admin.pricing.partials.duration_box', ['label' => '7 Days (7D)', 'priceKey' => 'price_7d', 'costKey' => 'cost_7d', 'priceVal' => $prod->price_7d, 'costVal' => $prod->cost_7d])
                    @include('admin.pricing.partials.duration_box', ['label' => '15 Days (15D)', 'priceKey' => 'price_15d', 'costKey' => 'cost_15d', 'priceVal' => $prod->price_15d, 'costVal' => $prod->cost_15d])
                    @include('admin.pricing.partials.duration_box', ['label' => '30 Days (30D)', 'priceKey' => 'price_30d', 'costKey' => 'cost_30d', 'priceVal' => $prod->price_30d, 'costVal' => $prod->cost_30d])
                    @include('admin.pricing.partials.duration_box', ['label' => '90 Days (90D)', 'priceKey' => 'price_90d', 'costKey' => 'cost_90d', 'priceVal' => $prod->price_90d, 'costVal' => $prod->cost_90d])
                </div>

            @else
                <!-- DEFAULT GENERIC PLANS -->
                <div class="plans-grid">
                    @include('admin.pricing.partials.duration_box', ['label' => '1 Day (1D)', 'priceKey' => 'price_1d', 'costKey' => 'cost_1d', 'priceVal' => $prod->price_1d, 'costVal' => $prod->cost_1d])
                    @include('admin.pricing.partials.duration_box', ['label' => '7 Days (7D)', 'priceKey' => 'price_7d', 'costKey' => 'cost_7d', 'priceVal' => $prod->price_7d, 'costVal' => $prod->cost_7d])
                    @include('admin.pricing.partials.duration_box', ['label' => '30 Days (30D)', 'priceKey' => 'price_30d', 'costKey' => 'cost_30d', 'priceVal' => $prod->price_30d, 'costVal' => $prod->cost_30d])
                </div>
            @endif

            <button type="submit" class="btn-save-prices">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Save Customer & Cost Prices</span>
            </button>
        </form>
    </div>
</div>
