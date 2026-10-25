@extends('admin.layout')

@section('title', 'Pricing Matrix')
@section('header_title', 'Pricing Matrix & Multi-Tier Rates')

@section('styles')
<style>
    .pricing-container {
        padding-bottom: 40px;
    }

    /* Top Mode Toggle Tabs */
    .pricing-tabs-nav {
        display: flex;
        background: #e2e8f0;
        padding: 4px;
        border-radius: 9999px;
        gap: 4px;
        margin-bottom: 24px;
        width: fit-content;
    }

    .pricing-tab-btn {
        padding: 8px 18px;
        border-radius: 9999px;
        font-size: 13px;
        font-weight: 700;
        border: none;
        background: transparent;
        color: #475569;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .pricing-tab-btn.active.tab-cust {
        background: #ffffff;
        color: #0f172a;
        box-shadow: 0 2px 4px rgba(0,0,0,0.06);
    }

    .pricing-tab-btn.active.tab-std-res {
        background: #4f46e5;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
    }

    .pricing-tab-btn.active.tab-vip {
        background: #7c3aed;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(124, 58, 237, 0.3);
    }

    /* Grid of Pricing Cards */
    .pricing-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
    }

    .pricing-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .pricing-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.05);
    }

    .pricing-card-header {
        padding: 14px 18px;
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .pricing-card-title {
        font-size: 15px;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pricing-card-body {
        padding: 18px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .plans-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 16px;
    }

    .plan-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 8px 10px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.2s;
    }

    .plan-box:hover {
        border-color: #cbd5e1;
        background: #f1f5f9;
    }

    .plan-box-label {
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .plan-input-wrap {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .plan-currency {
        font-weight: 800;
        font-size: 14px;
        color: #059669;
    }

    .plan-input {
        width: 100%;
        border: none;
        background: transparent;
        font-weight: 800;
        font-size: 15px;
        color: #0f172a;
        padding: 0;
        outline: none;
    }

    .plan-input:focus {
        color: #2563eb;
    }

    /* Cost Mode Toggle Styles */
    .cost-col {
        display: none;
    }
    .sell-col-label {
        display: none;
    }
    .profit-badge {
        display: none;
    }
    .plan-box-grid {
        display: flex;
        align-items: center;
    }

    body.show-cost-mode .cost-col {
        display: block;
    }
    body.show-cost-mode .sell-col-label {
        display: block;
    }
    body.show-cost-mode .profit-badge {
        display: inline-block;
    }
    body.show-cost-mode .plan-box-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-top: 4px;
    }
    body.show-cost-mode .default-currency-symbol {
        display: none;
    }

    .btn-save-prices {
        width: 100%;
        padding: 10px 16px;
        background: #059669;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 13px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: background 0.2s;
    }

    .btn-save-prices:hover {
        background: #047857;
    }

    /* Toast Notification */
    #pricing_toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: #0f172a;
        color: #ffffff;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        font-size: 13px;
        font-weight: 600;
        display: none;
        align-items: center;
        gap: 10px;
        z-index: 9999;
        border-left: 4px solid #10b981;
    }

    /* Percentage Quick Discount Styling */
    .btn-global-pct {
        background: #ffffff;
        color: #4338ca;
        border: 1.5px solid #c7d2fe;
        border-radius: 6px;
        padding: 5px 10px;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .btn-global-pct:hover {
        background: #4f46e5;
        color: #ffffff;
        border-color: #4f46e5;
        transform: translateY(-1px);
    }
    .btn-card-pct {
        background: #ffffff;
        color: #4f46e5;
        border: 1px solid #c7d2fe;
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 10px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .btn-card-pct:hover {
        background: #4f46e5;
        color: #ffffff;
        border-color: #4f46e5;
    }
    .btn-card-pct-apply {
        background: #4f46e5;
        color: #ffffff;
        border: none;
        border-radius: 4px;
        padding: 2px 8px;
        font-size: 11px;
        font-weight: 800;
        cursor: pointer;
    }
    .btn-card-reset {
        background: #ffffff;
        color: #dc2626;
        border: 1px solid #fca5a5;
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 10px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .btn-card-reset:hover {
        background: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
    }
    #btnResetAllResellers:hover {
        background: #dc2626 !important;
        color: #ffffff !important;
        border-color: #dc2626 !important;
    }
    .price-input-highlight {
        animation: flashGreen 0.8s ease;
    }
    @keyframes flashGreen {
        0% { background-color: #d1fae5; border-color: #10b981; }
        100% { background-color: transparent; }
    }

    @media (max-width: 768px) {
        .pricing-grid {
            grid-template-columns: 1fr !important;
            gap: 14px !important;
        }
        .pricing-tabs-nav {
            width: 100% !important;
            display: flex !important;
        }
        .pricing-tab-btn {
            flex: 1 !important;
            justify-content: center !important;
            padding: 8px 10px !important;
            font-size: 11px !important;
        }
        #btnToggleCostMode {
            width: 100% !important;
            justify-content: center !important;
        }
    }
</style>
@endsection

@section('content')
<div class="pricing-container">

    <!-- Top Mode Selector Tabs -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-tags" style="color: #10b981;"></i>
            <span>Pricing Matrix</span>
        </h3>

        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <!-- Show/Hide Cost Toggle Button -->
            <button type="button" id="btnToggleCostMode" onclick="toggleCostMode()" style="background: #ffffff; color: #334155; border: 1.5px solid #cbd5e1; border-radius: 9999px; font-weight: 700; font-size: 13px; padding: 8px 16px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.04); cursor: pointer; transition: all 0.2s;">
                <i class="fa-regular fa-eye" id="costModeIcon" style="color: #0284c7;"></i>
                <span id="costModeText">Show Cost (Kharid)</span>
            </button>

            <!-- 3-Tier Tab Selector -->
            <div class="pricing-tabs-nav" style="margin-bottom: 0;">
                <button type="button" id="btnTabCustomer" class="pricing-tab-btn tab-cust {{ $tab === 'customer' ? 'active' : '' }}" onclick="switchPricingTab('customer')">
                    <i class="fa-solid fa-users"></i>
                    <span>Customer Rates</span>
                </button>
                <button type="button" id="btnTabStdReseller" class="pricing-tab-btn tab-std-res {{ $tab === 'standard_reseller' ? 'active' : '' }}" onclick="switchPricingTab('standard_reseller')">
                    <i class="fa-solid fa-crown"></i>
                    <span>Standard Reseller</span>
                </button>
                <button type="button" id="btnTabVipCustom" class="pricing-tab-btn tab-vip {{ $tab === 'reseller' || $tab === 'vip_custom' ? 'active' : '' }}" onclick="switchPricingTab('vip_custom')">
                    <i class="fa-solid fa-star"></i>
                    <span>VIP Custom User</span>
                </button>
            </div>
        </div>
    </div>

    @php
        $carromProducts = $products->filter(fn($p) => strtolower($p->game_name ?? '') === 'carrom pool' || (str_contains(strtolower($p->name), 'carrom') && !str_contains(strtolower($p->name), 'soccer') && !str_contains(strtolower($p->name), '8 ball')));
        $eightBallProducts = $products->filter(fn($p) => strtolower($p->game_name ?? '') === '8 ball pool' || str_contains(strtolower($p->name), '8 ball'));
        $soccerProducts = $products->filter(fn($p) => strtolower($p->game_name ?? '') === 'soccer star' || str_contains(strtolower($p->name), 'soccer'));
        $otherProducts = $products->reject(fn($p) => $carromProducts->contains($p) || $eightBallProducts->contains($p) || $soccerProducts->contains($p));
    @endphp

    <!-- 1. CUSTOMER DEFAULT RATES VIEW (GROUPED BY GAME) -->
    <div id="sectionCustomerPricing" style="{{ $tab === 'customer' ? 'display: block;' : 'display: none;' }}">
        
        <!-- A. CARROM POOL SECTION -->
        <div style="margin-bottom: 32px;">
            <div style="margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
                <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>🎯 Carrom Pool</span>
                </h3>
            </div>

            <div class="pricing-grid">
                @foreach($carromProducts as $prod)
                    @include('admin.pricing.partials.product_card', ['prod' => $prod])
                @endforeach
            </div>
        </div>

        <!-- B. 8 BALL POOL SECTION -->
        @if($eightBallProducts->count() > 0)
        <div style="margin-bottom: 32px;">
            <div style="margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
                <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>🎱 8 Ball Pool</span>
                </h3>
            </div>

            <div class="pricing-grid">
                @foreach($eightBallProducts as $prod)
                    @include('admin.pricing.partials.product_card', ['prod' => $prod])
                @endforeach
            </div>
        </div>
        @endif

        <!-- C. SOCCER STAR SECTION -->
        @if($soccerProducts->count() > 0)
        <div style="margin-bottom: 32px;">
            <div style="margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
                <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>⚽ Soccer Star</span>
                </h3>
            </div>

            <div class="pricing-grid">
                @foreach($soccerProducts as $prod)
                    @include('admin.pricing.partials.product_card', ['prod' => $prod])
                @endforeach
            </div>
        </div>
        @endif

        <!-- D. OTHER CATEGORIES SECTION -->
        @if($otherProducts->count() > 0)
        <div>
            <div style="margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
                <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>🎮 Other Games</span>
                </h3>
            </div>

            <div class="pricing-grid">
                @foreach($otherProducts as $prod)
                    @include('admin.pricing.partials.product_card', ['prod' => $prod])
                @endforeach
            </div>
        </div>
        @endif

    </div>

    <!-- 2. GLOBAL STANDARD RESELLER RATES VIEW (GROUPED BY GAME) -->
    <div id="sectionStandardResellerPricing" style="{{ $tab === 'standard_reseller' ? 'display: block;' : 'display: none;' }}">
        
        <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 12px 18px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 800;">
                    <i class="fa-solid fa-percent"></i>
                </div>
                <span style="font-size: 15px; font-weight: 800; color: #0f172a;">All Products Discount</span>
            </div>

            <!-- Global Quick Discount Controls -->
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 4px;">
                    <input type="number" id="global_pct_input" placeholder="%" min="0.1" max="99" step="0.5" style="width: 65px; padding: 6px 8px; font-size: 13px; font-weight: 700; border: 1.5px solid #cbd5e1; border-radius: 6px; background: #ffffff; color: #0f172a; outline: none; text-align: center;" onkeydown="if(event.key==='Enter'){event.preventDefault(); applyCustomGlobalDiscount();}">
                    <button type="button" onclick="applyCustomGlobalDiscount()" style="background: #4f46e5; color: #ffffff; border: none; padding: 7px 14px; border-radius: 6px; font-size: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                        <span>Apply</span>
                    </button>
                </div>

                <!-- RESET TO CUSTOMER RATES (100%) BUTTON -->
                <button type="button" id="btnResetAllResellers" onclick="resetAllToCustomerRates()" style="background: #ffffff; color: #dc2626; border: 1.5px solid #fca5a5; padding: 7px 12px; border-radius: 6px; font-size: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.15s ease;" title="Reset all products back to exact 100% Customer Prices">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Reset</span>
                </button>

                <button type="button" id="btnSaveAllResellers" onclick="saveAllStandardResellerRates()" style="background: #10b981; color: #ffffff; border: none; padding: 7px 16px; border-radius: 6px; font-size: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25);">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>Save All Products</span>
                </button>
            </div>
        </div>

        <!-- A. CARROM POOL SECTION -->
        <div style="margin-bottom: 32px;">
            <div style="margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
                <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>🎯 Carrom Pool (Reseller Rates)</span>
                </h3>
            </div>

            <div class="pricing-grid">
                @foreach($carromProducts as $prod)
                    @include('admin.pricing.partials.standard_reseller_product_card', ['prod' => $prod])
                @endforeach
            </div>
        </div>

        <!-- B. 8 BALL POOL SECTION -->
        @if($eightBallProducts->count() > 0)
        <div style="margin-bottom: 32px;">
            <div style="margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
                <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>🎱 8 Ball Pool (Reseller Rates)</span>
                </h3>
            </div>

            <div class="pricing-grid">
                @foreach($eightBallProducts as $prod)
                    @include('admin.pricing.partials.standard_reseller_product_card', ['prod' => $prod])
                @endforeach
            </div>
        </div>
        @endif

        <!-- C. SOCCER STAR SECTION -->
        @if($soccerProducts->count() > 0)
        <div style="margin-bottom: 32px;">
            <div style="margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
                <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>⚽ Soccer Star (Reseller Rates)</span>
                </h3>
            </div>

            <div class="pricing-grid">
                @foreach($soccerProducts as $prod)
                    @include('admin.pricing.partials.standard_reseller_product_card', ['prod' => $prod])
                @endforeach
            </div>
        </div>
        @endif

        <!-- D. OTHER CATEGORIES SECTION -->
        @if($otherProducts->count() > 0)
        <div>
            <div style="margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
                <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>🎮 Other Games (Reseller Rates)</span>
                </h3>
            </div>

            <div class="pricing-grid">
                @foreach($otherProducts as $prod)
                    @include('admin.pricing.partials.standard_reseller_product_card', ['prod' => $prod])
                @endforeach
            </div>
        </div>
        @endif

    </div>

    <!-- 3. VIP CUSTOM USER RATES OVERRIDE VIEW -->
    <div id="sectionVipPricing" style="{{ $tab === 'reseller' || $tab === 'vip_custom' ? 'display: block;' : 'display: none;' }}">
        
        <!-- RESELLER SELECTOR COMPACT BAR WITH REAL-TIME LIVE SEARCH -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 18px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: #0f172a;">
                <i class="fa-solid fa-star" style="color: #7c3aed;"></i>
                <span>Customizing VIP Rates For:</span>
                <span style="color: #7c3aed; font-weight: 800;">{{ $selectedUser ? ($selectedUser->first_name ?: $selectedUser->username) : 'Select User' }}</span>
                @if($selectedUser && $selectedUser->username)
                    <span style="font-size: 12px; color: #64748b; font-weight: 500;">({{'@' . $selectedUser->username}})</span>
                @endif
                @if($selectedUser && $selectedUser->telegram_id)
                    <span style="font-size: 11px; background: #f1f5f9; color: #475569; padding: 2px 6px; border-radius: 4px; font-family: monospace;">ID: {{ $selectedUser->telegram_id }}</span>
                @endif
            </div>

            <!-- Live Search & Reseller Switcher Dropdown -->
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <!-- Fast Instant Autocomplete Search Box -->
                <div style="position: relative; display: flex; align-items: center;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; color: #94a3b8; font-size: 12px; z-index: 2;"></i>
                    <input type="text" id="user_search_quick_input" placeholder="🔍 Search name, @user, or ID..." oninput="handleUserSearchInput(this.value)" onfocus="handleUserSearchInput(this.value)" style="padding: 7px 12px 7px 30px; font-size: 13px; font-weight: 600; border: 1.5px solid #cbd5e1; border-radius: 8px; outline: none; width: 260px; background: #f8fafc; color: #0f172a;">
                    
                    <!-- Autocomplete Dropdown Popup -->
                    <div id="user_search_results_box" style="display: none; position: absolute; top: 100%; left: 0; width: 300px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; box-shadow: 0 12px 28px rgba(0,0,0,0.18); max-height: 280px; overflow-y: auto; z-index: 1000; margin-top: 6px;">
                    </div>
                </div>

                <!-- Dropdown (Direct Switcher) -->
                <div style="display: flex; align-items: center; gap: 6px;">
                    <select id="pricing_reseller_select" onchange="switchResellerUser(this.value)" class="form-control" style="width: 250px; font-size: 13px; font-weight: 700; padding: 7px 12px; border-radius: 8px; border: 1.5px solid #cbd5e1; height: auto;">
                        @foreach($allUsers as $u)
                            <option value="{{ $u->id }}" {{ ($selectedUser && $selectedUser->id === $u->id) ? 'selected' : '' }}>
                                {{ $u->first_name ?: ($u->username ?: 'User #' . $u->id) }} {{ $u->username ? '(@' . $u->username . ')' : '' }} {{ $u->is_reseller ? '👑' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        @if($selectedUser)
            <!-- A. CARROM POOL SECTION -->
            <div style="margin-bottom: 32px;">
                <div style="margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
                    <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <span>🎯 Carrom Pool (Custom VIP Rates)</span>
                    </h3>
                </div>

                <div class="pricing-grid">
                    @foreach($carromProducts as $prod)
                        @include('admin.pricing.partials.reseller_product_card', ['prod' => $prod, 'selectedUser' => $selectedUser, 'resellerPricesMap' => $resellerPricesMap])
                    @endforeach
                </div>
            </div>

            <!-- B. 8 BALL POOL SECTION -->
            @if($eightBallProducts->count() > 0)
            <div style="margin-bottom: 32px;">
                <div style="margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
                    <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <span>🎱 8 Ball Pool (Custom VIP Rates)</span>
                    </h3>
                </div>

                <div class="pricing-grid">
                    @foreach($eightBallProducts as $prod)
                        @include('admin.pricing.partials.reseller_product_card', ['prod' => $prod, 'selectedUser' => $selectedUser, 'resellerPricesMap' => $resellerPricesMap])
                    @endforeach
                </div>
            </div>
            @endif

            <!-- C. SOCCER STAR SECTION -->
            @if($soccerProducts->count() > 0)
            <div style="margin-bottom: 32px;">
                <div style="margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
                    <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <span>⚽ Soccer Star (Custom VIP Rates)</span>
                    </h3>
                </div>

                <div class="pricing-grid">
                    @foreach($soccerProducts as $prod)
                        @include('admin.pricing.partials.reseller_product_card', ['prod' => $prod, 'selectedUser' => $selectedUser, 'resellerPricesMap' => $resellerPricesMap])
                    @endforeach
                </div>
            </div>
            @endif

            <!-- D. OTHER CATEGORIES SECTION -->
            @if($otherProducts->count() > 0)
            <div style="margin-bottom: 32px;">
                <div style="margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
                    <h3 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <span>🎮 Other Games (Custom VIP Rates)</span>
                    </h3>
                </div>

                <div class="pricing-grid">
                    @foreach($otherProducts as $prod)
                        @include('admin.pricing.partials.reseller_product_card', ['prod' => $prod, 'selectedUser' => $selectedUser, 'resellerPricesMap' => $resellerPricesMap])
                    @endforeach
                </div>
            </div>
            @endif
        @endif

    </div>

</div>

<!-- Floating Toast Notification -->
<div id="pricing_toast">
    <i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 16px;"></i>
    <span id="pricing_toast_msg">Price saved successfully!</span>
</div>
@endsection

@section('scripts')
<script>
// Switch between 3 Tabs
function switchPricingTab(tab) {
    const secCust = document.getElementById('sectionCustomerPricing');
    const secStdRes = document.getElementById('sectionStandardResellerPricing');
    const secVip = document.getElementById('sectionVipPricing');

    const btnCust = document.getElementById('btnTabCustomer');
    const btnStdRes = document.getElementById('btnTabStdReseller');
    const btnVip = document.getElementById('btnTabVipCustom');

    // Hide all
    secCust.style.display = 'none';
    secStdRes.style.display = 'none';
    secVip.style.display = 'none';

    btnCust.classList.remove('active');
    btnStdRes.classList.remove('active');
    btnVip.classList.remove('active');

    if (tab === 'standard_reseller') {
        secStdRes.style.display = 'block';
        btnStdRes.classList.add('active');
        localStorage.setItem('pricing_active_tab', 'standard_reseller');
        window.history.replaceState(null, '', '?tab=standard_reseller');
    } else if (tab === 'vip_custom' || tab === 'reseller') {
        secVip.style.display = 'block';
        btnVip.classList.add('active');
        localStorage.setItem('pricing_active_tab', 'vip_custom');
        window.history.replaceState(null, '', '?tab=vip_custom');
    } else {
        secCust.style.display = 'block';
        btnCust.classList.add('active');
        localStorage.setItem('pricing_active_tab', 'customer');
        window.history.replaceState(null, '', '?tab=customer');
    }
}

// Switch Reseller User in VIP Custom Tab
function switchResellerUser(userId) {
    if (!userId) return;
    window.location.href = `{{ route('admin.pricing.index') }}?tab=vip_custom&reseller_id=${userId}`;
}

// All Users JSON Data for Instant Autocomplete
const allVipUsersList = {!! $allUsersJson !!};

// Render Instant Search Autocomplete
function handleUserSearchInput(query) {
    const box = document.getElementById('user_search_results_box');
    if (!box) return;

    const q = query.toLowerCase().trim();
    if (q === '') {
        box.style.display = 'none';
        return;
    }

    const matches = allVipUsersList.filter(u => {
        return u.name.toLowerCase().includes(q) ||
               u.username.toLowerCase().includes(q) ||
               u.telegram_id.includes(q);
    });

    if (matches.length === 0) {
        box.innerHTML = `<div style="padding: 12px 14px; font-size: 12px; color: #94a3b8; text-align: center;">No user matched "${query}".</div>`;
        box.style.display = 'block';
        return;
    }

    let html = '';
    matches.slice(0, 8).forEach(u => {
        const badge = u.is_reseller ? '<span style="background: #f3e8ff; color: #7c3aed; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700;">👑 Reseller</span>' : '';
        html += `
            <div onclick="switchResellerUser(${u.id})" style="padding: 10px 14px; border-bottom: 1px solid #f1f5f9; cursor: pointer; display: flex; align-items: center; justify-content: space-between; transition: background 0.15s;" onmouseover="this.style.background='#faf5ff'" onmouseout="this.style.background='#ffffff'">
                <div>
                    <div style="font-weight: 700; color: #0f172a; font-size: 13px;">${u.name}</div>
                    <div style="font-size: 11px; color: #64748b;">${u.username} • ID: ${u.telegram_id}</div>
                </div>
                <div>${badge}</div>
            </div>
        `;
    });

    box.innerHTML = html;
    box.style.display = 'block';
}

// Close autocomplete when clicking outside
document.addEventListener('click', (e) => {
    const box = document.getElementById('user_search_results_box');
    const input = document.getElementById('user_search_quick_input');
    if (box && !box.contains(e.target) && e.target !== input) {
        box.style.display = 'none';
    }
});

// AJAX save for customer prices
function saveCustomerPricesAjax(e, form, productName) {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    const origText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
    btn.disabled = true;

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(res => res.json())
    .then(data => {
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Saved!';
        btn.style.background = '#10b981';
        showPricingToast(`Customer prices saved for ${productName}!`);
        setTimeout(() => {
            btn.innerHTML = origText;
            btn.style.background = '#059669';
            btn.disabled = false;
        }, 1500);
    })
    .catch(err => {
        btn.innerHTML = origText;
        btn.disabled = false;
        form.submit();
    });
}

// AJAX save for standard reseller prices
function saveStandardResellerPricesAjax(e, form, productName) {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    const origText = btn ? btn.innerHTML : 'Save';
    if (btn) {
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;
    }

    const formData = new FormData(form);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form.querySelector('input[name="_token"]')?.value;

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('HTTP status ' + res.status);
        return res.json();
    })
    .then(data => {
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Saved!';
            btn.style.background = '#10b981';
        }
        showPricingToast(`Standard reseller rates saved for ${productName}!`);
        setTimeout(() => {
            if (btn) {
                btn.innerHTML = origText;
                btn.style.background = '#4f46e5';
                btn.disabled = false;
            }
        }, 1500);
    })
    .catch(err => {
        console.error('AJAX save error:', err);
        if (btn) {
            btn.innerHTML = origText;
            btn.disabled = false;
        }
        form.submit();
    });
}

// ----------------------------------------------------
// AUTO-DISCOUNT PERCENTAGE ENGINE FOR STANDARD RESELLER
// ----------------------------------------------------

function calculateDiscount(basePrice, pct) {
    const p = parseFloat(basePrice);
    if (isNaN(p) || p <= 0) return 0;
    const discounted = p * (1 - (pct / 100));
    return Math.max(1, Math.round(discounted));
}

// Apply % discount to a single product card
function applyCardDiscount(prodId, pct) {
    const card = document.getElementById('std_card_' + prodId);
    if (!card) return;
    pct = parseFloat(pct);
    if (isNaN(pct) || pct <= 0) return;

    const inputs = card.querySelectorAll('input.plan-input[name^="reseller_price_"]');
    inputs.forEach(input => {
        const name = input.getAttribute('name');
        const custVal = parseFloat(card.getAttribute('data-cust-' + name) || 0);
        if (custVal > 0) {
            const newPrice = calculateDiscount(custVal, pct);
            input.value = newPrice;
            input.classList.remove('price-input-highlight');
            void input.offsetWidth;
            input.classList.add('price-input-highlight');
        }
    });

    const prodName = card.getAttribute('data-prod-name') || 'Product';
    showPricingToast(`${pct}% discount applied to ${prodName}! Click "Save Standard Reseller Prices" below.`);
}

// Apply custom % discount from the card input
function applyCustomCardDiscount(prodId) {
    const input = document.getElementById('card_pct_input_' + prodId);
    if (!input) return;
    const val = parseFloat(input.value);
    if (isNaN(val) || val <= 0) {
        alert('Please enter a valid percentage discount (e.g. 2, 5, 10)');
        return;
    }
    applyCardDiscount(prodId, val);
}

// Apply % discount globally to ALL products in Standard Reseller tab
function applyGlobalDiscount(pct) {
    pct = parseFloat(pct);
    if (isNaN(pct) || pct <= 0) return;

    const cards = document.querySelectorAll('.standard-reseller-card');
    cards.forEach(card => {
        const prodId = card.getAttribute('data-prod-id');
        const inputs = card.querySelectorAll('input.plan-input[name^="reseller_price_"]');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            const custVal = parseFloat(card.getAttribute('data-cust-' + name) || 0);
            if (custVal > 0) {
                const newPrice = calculateDiscount(custVal, pct);
                input.value = newPrice;
                input.classList.remove('price-input-highlight');
                void input.offsetWidth;
                input.classList.add('price-input-highlight');
            }
        });
    });

    showPricingToast(`Applied ${pct}% discount across ALL products! Click "Save All Products" to save.`);
}

// Apply custom global % discount
function applyCustomGlobalDiscount() {
    const input = document.getElementById('global_pct_input');
    if (!input) return;
    const val = parseFloat(input.value);
    if (isNaN(val) || val <= 0) {
        alert('Please enter a valid percentage discount (e.g. 1, 2, 5, 7.5, 10)');
        return;
    }
    applyGlobalDiscount(val);
}

// Reset a single product card back to exact 100% Customer Price
function resetCardToCustomerPrice(prodId) {
    const card = document.getElementById('std_card_' + prodId);
    if (!card) return;

    const inputs = card.querySelectorAll('input.plan-input[name^="reseller_price_"]');
    inputs.forEach(input => {
        const name = input.getAttribute('name');
        const custVal = parseFloat(card.getAttribute('data-cust-' + name) || 0);
        if (custVal > 0) {
            input.value = (custVal == parseInt(custVal)) ? parseInt(custVal) : custVal;
            input.classList.remove('price-input-highlight');
            void input.offsetWidth;
            input.classList.add('price-input-highlight');
        }
    });

    const prodName = card.getAttribute('data-prod-name') || 'Product';
    showPricingToast(`Reset ${prodName} back to 100% Customer Price! Click Save to confirm.`);
}

// Reset ALL product cards back to exact 100% Customer Prices
function resetAllToCustomerRates() {
    const cards = document.querySelectorAll('.standard-reseller-card');
    cards.forEach(card => {
        const inputs = card.querySelectorAll('input.plan-input[name^="reseller_price_"]');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            const custVal = parseFloat(card.getAttribute('data-cust-' + name) || 0);
            if (custVal > 0) {
                input.value = (custVal == parseInt(custVal)) ? parseInt(custVal) : custVal;
                input.classList.remove('price-input-highlight');
                void input.offsetWidth;
                input.classList.add('price-input-highlight');
            }
        });
    });

    showPricingToast(`Reset ALL products to 100% Customer Prices! Click "Save All Products" to save.`);
}

// Save all Standard Reseller cards in one 1-click batch action using dedicated Batch API
async function saveAllStandardResellerRates() {
    const btn = document.getElementById('btnSaveAllResellers');
    const origHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving All...';
        btn.disabled = true;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;
    const cards = document.querySelectorAll('.standard-reseller-card');
    const payload = { products: {} };

    cards.forEach(card => {
        const prodId = card.getAttribute('data-prod-id');
        if (!prodId) return;
        payload.products[prodId] = {};
        const inputs = card.querySelectorAll('input.plan-input[name^="reseller_price_"]');
        inputs.forEach(inp => {
            payload.products[prodId][inp.name] = inp.value;
        });
    });

    try {
        const res = await fetch('{{ route('admin.pricing.update_standard_reseller_batch') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (res.ok && data.success) {
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-check"></i> All Saved!';
                btn.style.background = '#059669';
            }
            showPricingToast(`Successfully saved Standard Reseller rates for all ${cards.length} products! 🎉`);
        } else {
            throw new Error(data.message || 'Failed to save');
        }
    } catch (err) {
        console.error('Batch save error, falling back to per-card save:', err);
        // Fallback: submit all forms
        const forms = document.querySelectorAll('#sectionStandardResellerPricing form');
        for (const f of forms) {
            try {
                const fd = new FormData(f);
                await fetch(f.action, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
                });
            } catch(e){}
        }
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-check"></i> All Saved!';
            btn.style.background = '#059669';
        }
        showPricingToast(`Saved Standard Reseller rates!`);
    }

    setTimeout(() => {
        if (btn) {
            btn.innerHTML = origHtml;
            btn.style.background = '#10b981';
            btn.disabled = false;
        }
    }, 2000);
}

// AJAX save for batch VIP custom user prices
function saveResellerPricesAjax(e, form, productName) {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    const origText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
    btn.disabled = true;

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(res => res.json())
    .then(data => {
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Saved!';
        btn.style.background = '#10b981';
        showPricingToast(data.message || `VIP custom rates updated for ${productName}!`);
        setTimeout(() => {
            btn.innerHTML = origText;
            btn.style.background = '#7c3aed';
            btn.disabled = false;
        }, 1500);
    })
    .catch(err => {
        btn.innerHTML = origText;
        btn.disabled = false;
        form.submit();
    });
}

function showPricingToast(msg) {
    const toast = document.getElementById('pricing_toast');
    const toastMsg = document.getElementById('pricing_toast_msg');
    if (!toast || !toastMsg) return;
    toastMsg.textContent = msg;
    toast.style.display = 'flex';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}

// Toggle Cost Mode ON / OFF
function toggleCostMode(forceVal = null) {
    const isCurrentlyActive = document.body.classList.contains('show-cost-mode');
    const newState = (forceVal !== null) ? forceVal : !isCurrentlyActive;
    
    const btn = document.getElementById('btnToggleCostMode');
    const icon = document.getElementById('costModeIcon');
    const text = document.getElementById('costModeText');

    if (newState) {
        document.body.classList.add('show-cost-mode');
        if (btn) {
            btn.style.background = '#eff6ff';
            btn.style.color = '#1d4ed8';
            btn.style.borderColor = '#93c5fd';
        }
        if (icon) icon.className = 'fa-regular fa-eye-slash';
        if (text) text.textContent = 'Hide Cost Rates';
        localStorage.setItem('pricing_cost_mode', '1');
    } else {
        document.body.classList.remove('show-cost-mode');
        if (btn) {
            btn.style.background = '#ffffff';
            btn.style.color = '#475569';
            btn.style.borderColor = '#cbd5e1';
        }
        if (icon) icon.className = 'fa-regular fa-eye';
        if (text) text.textContent = 'Show Cost Rates';
        localStorage.setItem('pricing_cost_mode', '0');
    }
}

// Auto-check tab & cost mode on load
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab') || localStorage.getItem('pricing_active_tab') || 'customer';
    if (tabParam === 'standard_reseller') {
        switchPricingTab('standard_reseller');
    } else if (tabParam === 'reseller' || tabParam === 'vip_custom') {
        switchPricingTab('vip_custom');
    } else {
        switchPricingTab('customer');
    }

    const costPref = localStorage.getItem('pricing_cost_mode');
    if (costPref === '1') {
        toggleCostMode(true);
    }
});
</script>
@endsection
