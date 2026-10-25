@extends('admin.layout')

@section('title', 'Key Inventory Management')
@section('header_title', 'Key Inventory Management')

@section('styles')
<style>
    /* Stats Cards */
    .stat-card-gradient {
        border-radius: 14px;
        padding: 16px 20px;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .stat-card-white {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
        transition: transform 0.2s ease;
    }
    .stat-card-white:hover {
        transform: translateY(-2px);
    }

    /* Main Grid */
    .keys-layout-grid {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 24px;
        align-items: start;
    }

    @media (max-width: 1024px) {
        .keys-layout-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Cards */
    .panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
    }

    .panel-header-dark {
        background: #0f172a;
        color: #ffffff;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .panel-header-light {
        background: #ffffff;
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    /* Tabs */
    .tab-pills {
        display: inline-flex;
        background: #f1f5f9;
        padding: 4px;
        border-radius: 10px;
        gap: 4px;
    }

    .tab-pill-btn {
        padding: 6px 14px;
        font-size: 13px;
        font-weight: 700;
        border-radius: 8px;
        text-decoration: none;
        color: #64748b;
        transition: all 0.2s ease;
        border: none;
        background: transparent;
        cursor: pointer;
    }

    .tab-pill-btn.active {
        background: #2563eb;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
    }

    /* Grouped Stock View Styles */
    .stock-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
        align-items: start;
    }

    .stock-folder-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stock-folder-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 6px 12px -2px rgba(0,0,0,0.05);
    }

    .stock-folder-header {
        padding: 14px 18px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .stock-folder-body {
        padding: 14px 18px;
    }

    .plan-stock-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        margin-bottom: 10px;
        transition: background 0.15s;
    }

    .plan-stock-row:last-child {
        margin-bottom: 0;
    }

    .plan-stock-row:hover {
        background: #f1f5f9;
    }

    .stock-badge-healthy {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        font-size: 11px;
        font-weight: 800;
        padding: 3px 8px;
        border-radius: 6px;
    }

    .stock-badge-low {
        background: #fffbeb;
        color: #d97706;
        border: 1px solid #fde68a;
        font-size: 11px;
        font-weight: 800;
        padding: 3px 8px;
        border-radius: 6px;
    }

    .stock-badge-empty {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        font-size: 11px;
        font-weight: 800;
        padding: 3px 8px;
        border-radius: 6px;
    }

    .btn-stock-add {
        background: #059669;
        color: #ffffff;
        border: none;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.15s;
    }

    .btn-stock-add:hover {
        background: #047857;
    }

    .btn-stock-view {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s;
    }

    .btn-stock-view:hover {
        background: #dbeafe;
    }

    /* Keys Table */
    .key-table {
        width: 100%;
        border-collapse: collapse;
    }

    .key-table th {
        background: #f8fafc;
        color: #475569;
        padding: 12px 16px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
    }

    .key-table td {
        padding: 14px 16px;
        font-size: 13px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .key-table tr:hover td {
        background-color: #f8fafc;
    }

    .code-badge {
        font-family: monospace;
        font-size: 13px;
        font-weight: 700;
        background: #f1f5f9;
        color: #0f172a;
        padding: 6px 10px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .copy-btn {
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
        padding: 2px;
        font-size: 13px;
        transition: color 0.15s;
    }

    .copy-btn:hover {
        color: #2563eb;
    }

    .badge-pill-available {
        background-color: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        font-size: 12px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .badge-pill-used {
        background-color: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        font-size: 12px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* Modal Overlay */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.5);
        z-index: 99999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-content-box {
        background: #ffffff;
        border-radius: 16px;
        width: 100%;
        max-width: 650px;
        max-height: 85vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 20px 30px -10px rgba(0,0,0,0.3);
        animation: modalScaleUp 0.2s ease-out;
    }

    @keyframes modalScaleUp {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    @media (max-width: 768px) {
        .stock-grid {
            grid-template-columns: 1fr !important;
            gap: 12px !important;
        }
        .modal-content-box {
            width: 95vw !important;
            max-height: 90vh !important;
            border-radius: 12px !important;
        }
        .modal-overlay {
            padding: 10px !important;
        }
        .panel-header {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 12px !important;
            padding: 14px 16px !important;
        }
    }
</style>
@endsection

@section('content')
<div style="margin-bottom: 24px;">

    <!-- TOP ROW 1: GAME WISE ACTIVE KEYS COUNT -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
        
        <!-- Aim Carrom King -->
        <div class="stat-card-gradient" style="background: linear-gradient(135deg, #0284c7, #0369a1);">
            <div>
                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9;">AIM CARROM KING</div>
                <div style="font-size: 28px; font-weight: 800; line-height: 1.2; margin: 4px 0;">{{ $counts['aim_carrom'] }}</div>
                <div style="font-size: 11px; opacity: 0.85; font-weight: 600;">Available Keys</div>
            </div>
            <div style="font-size: 32px; opacity: 0.3;"><i class="fa-solid fa-crown"></i></div>
        </div>

        <!-- KOS Engine -->
        <div class="stat-card-gradient" style="background: linear-gradient(135deg, #059669, #047857);">
            <div>
                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9;">KOS ENGINE</div>
                <div style="font-size: 28px; font-weight: 800; line-height: 1.2; margin: 4px 0;">{{ $counts['ks_engine'] }}</div>
                <div style="font-size: 11px; opacity: 0.85; font-weight: 600;">Available Keys</div>
            </div>
            <div style="font-size: 32px; opacity: 0.3;"><i class="fa-solid fa-crosshairs"></i></div>
        </div>

        <!-- Snake Hack -->
        <div class="stat-card-gradient" style="background: linear-gradient(135deg, #7c3aed, #6d28d9);">
            <div>
                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9;">SNAKE HACK</div>
                <div style="font-size: 28px; font-weight: 800; line-height: 1.2; margin: 4px 0;">{{ $counts['snake'] }}</div>
                <div style="font-size: 11px; opacity: 0.85; font-weight: 600;">Available Keys</div>
            </div>
            <div style="font-size: 32px; opacity: 0.3;"><i class="fa-solid fa-staff-snake"></i></div>
        </div>

        <!-- Aim AI Engine -->
        <div class="stat-card-gradient" style="background: linear-gradient(135deg, #db2777, #be185d);">
            <div>
                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9;">AIM AI</div>
                <div style="font-size: 28px; font-weight: 800; line-height: 1.2; margin: 4px 0;">{{ $counts['aim_ai'] }}</div>
                <div style="font-size: 11px; opacity: 0.85; font-weight: 600;">Available Keys</div>
            </div>
            <div style="font-size: 32px; opacity: 0.3;"><i class="fa-solid fa-robot"></i></div>
        </div>

        <!-- Total Inventory Stock -->
        <div class="stat-card-gradient" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
            <div>
                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9;">TOTAL INVENTORY</div>
                <div style="font-size: 28px; font-weight: 800; line-height: 1.2; margin: 4px 0;">{{ $counts['total_available'] }}</div>
                <div style="font-size: 11px; opacity: 0.85; font-weight: 600;">{{ $counts['total_used'] }} used</div>
            </div>
            <div style="font-size: 32px; opacity: 0.3;"><i class="fa-solid fa-key"></i></div>
        </div>

    </div>

    <!-- TOP ROW 2: SALES & PROFIT METRICS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <div class="stat-card-white" style="border-left: 4px solid #059669;">
            <div>
                <div style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase;">TODAY'S SOLD KEYS</div>
                <div style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 4px 0;">{{ $counts['today_sold'] }}</div>
                <div style="font-size: 11px; color: #059669; font-weight: 700;"><i class="fa-solid fa-calendar-day me-1"></i> Today</div>
            </div>
            <div style="color: #059669; font-size: 24px;"><i class="fa-solid fa-cart-shopping"></i></div>
        </div>

        <div class="stat-card-white" style="border-left: 4px solid #0284c7;">
            <div>
                <div style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase;">YESTERDAY'S SOLD KEYS</div>
                <div style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 4px 0;">{{ $counts['yesterday_sold'] }}</div>
                <div style="font-size: 11px; color: #0284c7; font-weight: 700;"><i class="fa-solid fa-chart-line me-1"></i> Yesterday</div>
            </div>
            <div style="color: #0284c7; font-size: 24px;"><i class="fa-solid fa-chart-line"></i></div>
        </div>

        <div class="stat-card-white" style="border-left: 4px solid #7c3aed;">
            <div>
                <div style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase;">THIS MONTH'S SOLD KEYS</div>
                <div style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 4px 0;">{{ $counts['month_sold'] }}</div>
                <div style="font-size: 11px; color: #7c3aed; font-weight: 700;"><i class="fa-solid fa-boxes-packing me-1"></i> {{ date('F') }}</div>
            </div>
            <div style="color: #7c3aed; font-size: 24px;"><i class="fa-solid fa-boxes-packing"></i></div>
        </div>

        <div class="stat-card-white" style="border-left: 4px solid #d97706;">
            <div>
                <div style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase;">TODAY'S KEY PROFIT</div>
                <div style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 4px 0;">₹{{ number_format($counts['today_profit']) }}</div>
                <div style="font-size: 11px; color: #d97706; font-weight: 700;"><i class="fa-solid fa-wallet me-1"></i> Net Profit Today</div>
            </div>
            <div style="color: #d97706; font-size: 24px;"><i class="fa-solid fa-wallet"></i></div>
        </div>

        <div class="stat-card-white" style="border-left: 4px solid #db2777;">
            <div>
                <div style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase;">MONTHLY KEY PROFIT</div>
                <div style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 4px 0;">₹{{ number_format($counts['month_profit']) }}</div>
                <div style="font-size: 11px; color: #db2777; font-weight: 700;"><i class="fa-solid fa-piggy-bank me-1"></i> Net Profit {{ date('M') }}</div>
            </div>
            <div style="color: #db2777; font-size: 24px;"><i class="fa-solid fa-piggy-bank"></i></div>
        </div>

    </div>

    <!-- MAIN TWO-COLUMN WORKSPACE -->
    <div class="keys-layout-grid">
        
        <!-- LEFT COLUMN: ADD PRODUCT KEYS -->
        <div>
            <div class="panel-card" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.04);">
                <div class="panel-header-dark" style="background: #0f172a; padding: 14px 20px; color: #ffffff;">
                    <div style="font-weight: 700; font-size: 15px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-circle-plus" style="color: #10b981;"></i> Add Product Keys
                    </div>
                </div>
                <div style="padding: 20px;">
                    <form id="add_key_form" action="{{ route('admin.keys.store') }}" method="POST">
                        @csrf
                        
                        <!-- Select Product -->
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #475569; margin-bottom: 6px; display: block;">Select Game / Tool</label>
                            <select name="product_id" id="key_product_select" class="form-control" onchange="updateDurationsForSelectedProduct()" required style="font-size: 13px; font-weight: 700; padding: 8px 12px;">
                                <option value="" disabled selected>-- Choose Game / Tool --</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}" data-name="{{ strtolower($prod->name) }}" {{ old('product_id') == $prod->id ? 'selected' : '' }}>
                                        {{ $prod->formatted_display_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Select Duration / Variant -->
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #475569; margin-bottom: 6px; display: block;">Key Duration / Variant</label>
                            <select name="duration" id="key_duration_select" class="form-control" required style="font-size: 13px; font-weight: 700; padding: 8px 12px;">
                                <option value="" disabled selected>-- Choose Game / Tool First --</option>
                            </select>
                        </div>

                        <!-- Key Code(s) Textarea -->
                        <div class="form-group" style="margin-bottom: 18px;">
                            <label style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #475569; margin-bottom: 6px; display: block;">
                                Key Code(s)
                            </label>
                            <textarea name="license_keys" id="license_keys_input" class="form-control" rows="6" placeholder="Enter key code or paste multiple (one per line)" required style="font-family: monospace; font-size: 13px;"></textarea>
                            <span style="font-size: 11px; color: #64748b; margin-top: 4px; display: block;">
                                💡 Tip: You can paste 100+ keys at once. Each line is saved as 1 key.
                            </span>
                        </div>

                        <button type="submit" class="btn" style="width: 100%; background: #059669; color: #ffffff; font-weight: 700; padding: 12px; border-radius: 8px; font-size: 14px; border: none; cursor: pointer; transition: background 0.15s;">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save & Add Keys
                        </button>
                    </form>
                </div>
            </div>

            <!-- Pricing Matrix Quick Link -->
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 16px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 13px; font-weight: 800; color: #1e40af;">Manage Rates & Cost</div>
                    <div style="font-size: 11px; color: #3b82f6;">Set customer & reseller discounts in Matrix</div>
                </div>
                <a href="{{ route('admin.pricing.index') }}" class="btn" style="background: #2563eb; color: #fff; font-size: 12px; font-weight: 700; padding: 6px 12px; border-radius: 6px; text-decoration: none;">
                    Open Matrix <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <!-- RIGHT COLUMN: GROUPED STOCK FOLDERS & KEY INVENTORY -->
        <div>
            
            <!-- KEY INVENTORY MAIN CARD -->
            <div class="panel-card">
                
                <!-- Header with Tabs & View Switcher -->
                <div class="panel-header-light">
                    <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                        <div style="font-size: 17px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-boxes-stacked" style="color: #2563eb;"></i> Keys Stock & Inventory
                        </div>

                        <!-- Available / History Status Filter -->
                        <div class="tab-pills">
                            <a href="{{ route('admin.keys.index', ['tab' => 'available']) }}" class="tab-pill-btn {{ $tab === 'available' ? 'active' : '' }}">
                                Available ({{ $counts['total_available'] }})
                            </a>
                            <a href="{{ route('admin.keys.index', ['tab' => 'history']) }}" class="tab-pill-btn {{ $tab === 'history' ? 'active' : '' }}">
                                History / Used ({{ $counts['total_used'] }})
                            </a>
                        </div>
                    </div>

                    <!-- View Switcher (Grouped vs Table List) -->
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="tab-pills" style="background: #e2e8f0;">
                            <button type="button" id="btnViewGrouped" class="tab-pill-btn active" onclick="switchStockView('grouped')" style="font-size: 12px;">
                                <i class="fa-solid fa-folder-tree me-1"></i> Grouped View
                            </button>
                            <button type="button" id="btnViewList" class="tab-pill-btn" onclick="switchStockView('list')" style="font-size: 12px;">
                                <i class="fa-solid fa-list me-1"></i> All Keys List
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 1. GROUPED STOCK VIEW (COMPACT FOLDER CARDS BY GAME) -->
                <div id="sectionGroupedStock" style="padding: 20px;">
                    
                    @php
                        $carromStock = $productsStock->filter(fn($p) => strtolower($p['game_name'] ?? '') === 'carrom pool' || (str_contains(strtolower($p['name']), 'carrom') && !str_contains(strtolower($p['name']), 'soccer') && !str_contains(strtolower($p['name']), '8 ball')));
                        $eightBallStock = $productsStock->filter(fn($p) => strtolower($p['game_name'] ?? '') === '8 ball pool' || str_contains(strtolower($p['name']), '8 ball'));
                        $soccerStock = $productsStock->filter(fn($p) => strtolower($p['game_name'] ?? '') === 'soccer star' || str_contains(strtolower($p['name']), 'soccer'));
                        $otherStock = $productsStock->reject(fn($p) => $carromStock->contains('id', $p['id']) || $eightBallStock->contains('id', $p['id']) || $soccerStock->contains('id', $p['id']));
                    @endphp

                    <!-- A. CARROM POOL SECTION -->
                    <div style="margin-bottom: 24px;">
                        <div style="font-size: 15px; font-weight: 800; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #f1f5f9; padding-bottom: 6px;">
                            <span>🎯 Carrom Pool Stock</span>
                        </div>

                        <div class="stock-grid">
                            @foreach($carromStock as $p)
                                @include('admin.keys.partials.stock_card', ['p' => $p])
                            @endforeach
                        </div>
                    </div>

                    <!-- B. 8 BALL POOL SECTION -->
                    @if($eightBallStock->count() > 0)
                    <div style="margin-bottom: 24px;">
                        <div style="font-size: 15px; font-weight: 800; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #f1f5f9; padding-bottom: 6px;">
                            <span>🎱 8 Ball Pool Stock</span>
                        </div>

                        <div class="stock-grid">
                            @foreach($eightBallStock as $p)
                                @include('admin.keys.partials.stock_card', ['p' => $p])
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- C. SOCCER STAR SECTION -->
                    @if($soccerStock->count() > 0)
                    <div style="margin-bottom: 24px;">
                        <div style="font-size: 15px; font-weight: 800; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #f1f5f9; padding-bottom: 6px;">
                            <span>⚽ Soccer Star Stock</span>
                        </div>

                        <div class="stock-grid">
                            @foreach($soccerStock as $p)
                                @include('admin.keys.partials.stock_card', ['p' => $p])
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- D. OTHER SECTION -->
                    @if($otherStock->count() > 0)
                    <div>
                        <div style="font-size: 15px; font-weight: 800; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #f1f5f9; padding-bottom: 6px;">
                            <span>🎮 Other Games Stock</span>
                        </div>

                        <div class="stock-grid">
                            @foreach($otherStock as $p)
                                @include('admin.keys.partials.stock_card', ['p' => $p])
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>

                <!-- 2. TRADITIONAL ALL KEYS TABLE VIEW (OPTIONAL EXPANDABLE) -->
                <div id="sectionListStock" style="display: none;">
                    
                    <!-- Filter and Search Bar inside List View -->
                    <div style="padding: 12px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            @if($tab === 'history')
                                <form action="{{ route('admin.keys.clear') }}" method="POST" data-confirm="Clear all used license keys older than 48 hours?" data-confirm-title="Clear Old Keys" data-confirm-btn="Yes, Clear" data-confirm-color="#ef4444">
                                    @csrf
                                    <button type="submit" class="btn" style="background: #ef4444; color: #ffffff; padding: 6px 12px; font-size: 12px; font-weight: 700; border-radius: 6px;">
                                        <i class="fa-solid fa-trash-can me-1"></i> Clear Old
                                    </button>
                                </form>
                            @endif
                        </div>

                        <form method="GET" action="{{ route('admin.keys.index') }}" style="display: flex; gap: 8px; align-items: center;">
                            <input type="hidden" name="tab" value="{{ $tab }}">
                            <input type="hidden" name="view" value="list">
                            
                            <!-- Game Filter -->
                            <select name="game" class="form-control" style="padding: 6px 12px; font-size: 13px; width: auto;" onchange="this.form.submit()">
                                <option value="all">All Games</option>
                                <option value="Carrom" {{ request('game') == 'Carrom' ? 'selected' : '' }}>Carrom King</option>
                                <option value="KOS" {{ request('game') == 'KOS' ? 'selected' : '' }}>KOS Engine</option>
                                <option value="Snake" {{ request('game') == 'Snake' ? 'selected' : '' }}>Snake Hack</option>
                                <option value="Ai" {{ request('game') == 'Ai' ? 'selected' : '' }}>Aim AI</option>
                            </select>

                            <!-- Search Input -->
                            <div style="position: relative;">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search key..." class="form-control" style="padding: 6px 32px 6px 12px; font-size: 13px; width: 180px;">
                                <button type="submit" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer;">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Bulk Selection Action Bar -->
                    <form id="bulk_delete_form" action="{{ route('admin.keys.bulk-delete') }}" method="POST" data-confirm="Are you sure you want to delete all selected license keys?" data-confirm-title="Delete Selected Keys" data-confirm-btn="Yes, Delete" data-confirm-color="#ef4444">
                        @csrf
                        <div style="padding: 10px 20px; background: #ffffff; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #475569; cursor: pointer; margin: 0;">
                                <input type="checkbox" id="select_all_keys" style="cursor: pointer;"> Select All on Page
                            </label>
                            <button type="submit" id="btn_bulk_delete" class="btn" style="background: #ef4444; color: #ffffff; padding: 6px 14px; font-size: 12px; font-weight: 700; border-radius: 6px; display: none;">
                                <i class="fa-solid fa-trash me-1"></i> Delete Selected (<span id="selected_count">0</span>)
                            </button>
                        </div>

                        <!-- Keys Table -->
                        <div style="overflow-x: auto;">
                            <table class="key-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;"></th>
                                        <th>Game / Product</th>
                                        <th>Key Code</th>
                                        <th>Status</th>
                                        <th>{{ $tab === 'history' ? 'Sold On' : 'Added On' }}</th>
                                        @if($tab === 'history')
                                            <th>Purchased By</th>
                                        @endif
                                        <th style="text-align: center; width: 80px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($keys as $k)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="key_ids[]" value="{{ $k->id }}" class="key-item-checkbox" style="cursor: pointer;">
                                            </td>
                                            <td>
                                                @php
                                                    $prodName = $k->product ? $k->product->name : 'Unknown Product';
                                                @endphp
                                                <div style="font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                                    <span>{{ $prodName }} - {{ strtoupper(str_replace('_queue', '', $k->duration)) }}</span>
                                                    @if(str_contains(strtolower($k->duration), 'queue'))
                                                        <span class="badge" style="background: #fdf4ff; color: #a21caf; border: 1px solid #f0abfc; font-size: 10px; font-weight: 800; padding: 2px 6px;">
                                                            <i class="fa-solid fa-bolt" style="font-size: 9px;"></i> QUEUE
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="code-badge">
                                                    <span>{{ $k->license_key }}</span>
                                                    <button type="button" class="copy-btn" onclick="copyToClipboard('{{ $k->license_key }}', this)" title="Copy Key">
                                                        <i class="fa-regular fa-copy"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                @if($k->is_sold)
                                                    <span class="badge-pill-used"><i class="fa-solid fa-circle-xmark"></i> Used</span>
                                                @else
                                                    <span class="badge-pill-available"><i class="fa-solid fa-circle-check"></i> Available</span>
                                                @endif
                                            </td>
                                            <td style="color: #64748b; font-size: 12px;">
                                                {{ $k->is_sold && $k->sold_at ? $k->sold_at->format('M d, Y h:i A') : $k->created_at->format('M d, Y h:i A') }}
                                            </td>
                                            @if($tab === 'history')
                                                <td>
                                                    @if($k->telegramUser)
                                                        <a href="{{ route('admin.users.show', $k->telegramUser->id) }}" style="font-weight: 700; color: #2563eb; text-decoration: none;">
                                                            {{ $k->telegramUser->first_name ?: $k->telegramUser->username }}
                                                        </a>
                                                    @else
                                                        <span style="color: #94a3b8;">—</span>
                                                    @endif
                                                </td>
                                            @endif
                                            <td style="text-align: center;">
                                                <button type="button" class="btn" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); width: 32px; height: 32px; padding: 0; border-radius: 6px; cursor: pointer;" onclick="deleteSingleKey('{{ $k->id }}')">
                                                    <i class="fa-solid fa-trash-can" style="font-size: 13px;"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $tab === 'history' ? 7 : 6 }}" style="text-align: center; padding: 40px; color: #94a3b8;">
                                                <i class="fa-solid fa-key" style="font-size: 32px; margin-bottom: 12px; opacity: 0.5;"></i>
                                                <p style="font-weight: 600; margin: 0;">No license keys found in this view.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <!-- Pagination -->
                    @if($keys->hasPages())
                        <div style="padding: 16px 20px; border-top: 1px solid #e2e8f0; background: #ffffff;">
                            {{ $keys->links() }}
                        </div>
                    @endif

                </div>

            </div>

        </div>

    </div>

</div>

<!-- BATCH KEYS POPUP MODAL -->
<div id="batch_keys_modal" class="modal-overlay">
    <div class="modal-content-box">
        
        <!-- Modal Header -->
        <div style="padding: 16px 20px; background: #0f172a; color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h4 id="batch_modal_title" style="margin: 0; font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-key" style="color: #38bdf8;"></i>
                    <span>Product - Duration Keys</span>
                </h4>
                <div id="batch_modal_subtitle" style="font-size: 12px; color: #94a3b8; margin-top: 2px;">
                    0 available keys in this stock pool
                </div>
            </div>
            <button type="button" onclick="closeBatchModal()" style="background: rgba(255,255,255,0.1); border: none; color: #ffffff; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Modal Action Bar -->
        <div style="padding: 12px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <button type="button" id="btn_copy_all_keys" class="btn" style="background: #2563eb; color: #ffffff; font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 6px; border: none; cursor: pointer;">
                    <i class="fa-regular fa-copy me-1"></i> Copy All Keys
                </button>
                <button type="button" onclick="toggleModalQuickAdd()" class="btn" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; font-size: 12px; font-weight: 700; padding: 6px 12px; border-radius: 6px; cursor: pointer;">
                    <i class="fa-solid fa-plus me-1"></i> Add More
                </button>
            </div>

            <!-- Filter Inside Modal -->
            <input type="text" id="modal_key_search" onkeyup="filterModalKeys(this.value)" placeholder="Search key..." class="form-control" style="font-size: 12px; padding: 5px 10px; width: 160px; height: auto;">
        </div>

        <!-- Modal Inline Quick Add (Hidden by default) -->
        <div id="modal_quick_add_wrap" style="display: none; padding: 14px 20px; background: #f1f5f9; border-bottom: 1px solid #cbd5e1;">
            <form action="{{ route('admin.keys.store') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" id="modal_quick_product_id">
                <input type="hidden" name="duration" id="modal_quick_duration">
                
                <label style="font-size: 11px; font-weight: 700; color: #334155; text-transform: uppercase; margin-bottom: 4px; display: block;">
                    Paste New Keys For This Batch:
                </label>
                <textarea name="license_keys" rows="3" class="form-control" placeholder="Paste keys here (one per line)" required style="font-family: monospace; font-size: 12px; margin-bottom: 8px;"></textarea>
                
                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" onclick="toggleModalQuickAdd()" class="btn" style="background: #e2e8f0; color: #475569; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 6px; border: none;">Cancel</button>
                    <button type="submit" class="btn" style="background: #059669; color: #fff; font-size: 12px; font-weight: 700; padding: 4px 14px; border-radius: 6px; border: none;">Save Keys</button>
                </div>
            </form>
        </div>

        <!-- Modal Keys Scrollable List -->
        <div style="padding: 16px 20px; overflow-y: auto; flex: 1; max-height: 400px;" id="modal_keys_list_container">
            <div style="text-align: center; padding: 30px; color: #94a3b8;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 24px;"></i>
                <p style="margin-top: 8px; font-size: 13px;">Loading keys...</p>
            </div>
        </div>

        <!-- Modal Footer -->
        <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: right;">
            <button type="button" onclick="closeBatchModal()" class="btn" style="background: #e2e8f0; color: #334155; font-size: 12px; font-weight: 700; padding: 6px 16px; border-radius: 6px; border: none; cursor: pointer;">
                Close
            </button>
        </div>

    </div>
</div>

<!-- Single Delete Form -->
<form id="single_delete_form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@section('scripts')
<script>
// Duration maps per product category
const durationMaps = {
    carrom: [
        { key: '3d', label: '3 Days' },
        { key: '7d', label: '7 Days' },
        { key: '30d', label: '30 Days' },
        { key: '90d', label: '90 Days' },
        { key: '3d_queue', label: '3D Auto Queue' },
        { key: '7d_queue', label: '7D Auto Queue' },
        { key: '30d_queue', label: '30D Auto Queue' },
        { key: '90d_queue', label: '90D Auto Queue' },
    ],
    bitaim: [
        { key: '7d', label: '7 Days (1 Week)' },
        { key: '30d', label: '30 Days (1 Month)' },
        { key: '90d', label: '90 Days (3 Months)' },
        { key: 'lifetime', label: '💎 Lifetime' }
    ],
    kos: [
        { key: '1d', label: '1 Day' },
        { key: '7d', label: '7 Days' },
        { key: '15d', label: '15 Days' },
        { key: '30d', label: '30 Days' }
    ],
    soccer: [
        { key: '3d', label: '3 Days' },
        { key: '10d', label: '10 Days' },
        { key: '30d', label: '30 Days' }
    ],
    snake: [
        { key: '3d', label: '3 Days' },
        { key: '10d', label: '10 Days' },
        { key: '30d', label: '30 Days' },
        { key: '90d', label: '90 Days' }
    ],
    ai: [
        { key: '1d', label: '1 Day' },
        { key: '3d', label: '3 Days' },
        { key: '7d', label: '7 Days' },
        { key: '15d', label: '15 Days' },
        { key: '30d', label: '30 Days' },
        { key: '90d', label: '90 Days' }
    ],
    default: [
        { key: '1d', label: '1 Day' },
        { key: '7d', label: '7 Days' },
        { key: '30d', label: '30 Days' }
    ]
};

function updateDurationsForSelectedProduct() {
    const select = document.getElementById('key_product_select');
    const durSelect = document.getElementById('key_duration_select');
    if (!select || !durSelect) return;

    const opt = select.options[select.selectedIndex];
    const name = (opt && opt.getAttribute('data-name')) ? opt.getAttribute('data-name').toLowerCase() : '';

    let list = durationMaps.default;
    if (name.includes('soccer')) list = durationMaps.soccer;
    else if (name.includes('bitaim') || name.includes('bit')) list = durationMaps.bitaim;
    else if (name.includes('carrom')) list = durationMaps.carrom;
    else if (name.includes('kos') || name.includes('ks')) list = durationMaps.kos;
    else if (name.includes('snake')) list = durationMaps.snake;
    else if (name.includes('ai')) list = durationMaps.ai;

    durSelect.innerHTML = '';
    list.forEach((item, index) => {
        const option = document.createElement('option');
        option.value = item.key;
        option.textContent = item.label;
        if (index === 0) option.selected = true;
        durSelect.appendChild(option);
    });
}

// Quick Add Keys from Folder Cards
function quickAddFromCard(productId, durationKey, title) {
    const prodSelect = document.getElementById('key_product_select');
    if (prodSelect) {
        prodSelect.value = productId;
        updateDurationsForSelectedProduct();
    }
    
    setTimeout(() => {
        const durSelect = document.getElementById('key_duration_select');
        if (durSelect) {
            durSelect.value = durationKey;
        }
        
        const keysInput = document.getElementById('license_keys_input');
        if (keysInput) {
            keysInput.focus();
            keysInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            keysInput.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.4)';
            setTimeout(() => {
                keysInput.style.boxShadow = '';
            }, 1500);
        }
    }, 50);
}

// Switch between Grouped View & Table List View
function switchStockView(view) {
    const secGrouped = document.getElementById('sectionGroupedStock');
    const secList = document.getElementById('sectionListStock');
    const btnGrouped = document.getElementById('btnViewGrouped');
    const btnList = document.getElementById('btnViewList');

    if (view === 'grouped') {
        if (secGrouped) secGrouped.style.display = 'block';
        if (secList) secList.style.display = 'none';
        if (btnGrouped) btnGrouped.classList.add('active');
        if (btnList) btnList.classList.remove('active');
        localStorage.setItem('keys_view_pref', 'grouped');
    } else {
        if (secGrouped) secGrouped.style.display = 'none';
        if (secList) secList.style.display = 'block';
        if (btnGrouped) btnGrouped.classList.remove('active');
        if (btnList) btnList.classList.add('active');
        localStorage.setItem('keys_view_pref', 'list');
    }
}

// Batch Modal Logic
let currentBatchKeys = [];

function openBatchModal(productId, durationKey, title) {
    const modal = document.getElementById('batch_keys_modal');
    const titleEl = document.getElementById('batch_modal_title');
    const subTitleEl = document.getElementById('batch_modal_subtitle');
    const container = document.getElementById('modal_keys_list_container');
    const qProduct = document.getElementById('modal_quick_product_id');
    const qDuration = document.getElementById('modal_quick_duration');

    if (qProduct) qProduct.value = productId;
    if (qDuration) qDuration.value = durationKey;
    if (titleEl) titleEl.innerHTML = `<i class="fa-solid fa-key" style="color: #38bdf8;"></i> <span>${title}</span>`;
    if (subTitleEl) subTitleEl.textContent = 'Fetching keys...';
    if (container) container.innerHTML = `<div style="text-align: center; padding: 30px; color: #94a3b8;"><i class="fa-solid fa-spinner fa-spin" style="font-size: 24px;"></i><p style="margin-top: 8px;">Loading keys...</p></div>`;

    if (modal) modal.style.display = 'flex';

    fetch(`{{ route('admin.keys.batch_keys') }}?product_id=${productId}&duration=${durationKey}`)
        .then(res => res.json())
        .then(data => {
            currentBatchKeys = data.keys || [];
            if (subTitleEl) subTitleEl.textContent = `${currentBatchKeys.length} available keys in this stock pool`;
            renderModalKeysList(currentBatchKeys);

            // Wire Copy All Button
            const copyAllBtn = document.getElementById('btn_copy_all_keys');
            if (copyAllBtn) {
                copyAllBtn.onclick = function() {
                    if (currentBatchKeys.length === 0) return;
                    const allText = currentBatchKeys.map(k => k.license_key).join("\n");
                    navigator.clipboard.writeText(allText).then(() => {
                        copyAllBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Copied All!';
                        copyAllBtn.style.background = '#059669';
                        setTimeout(() => {
                            copyAllBtn.innerHTML = '<i class="fa-regular fa-copy me-1"></i> Copy All Keys';
                            copyAllBtn.style.background = '#2563eb';
                        }, 2000);
                    });
                };
            }
        })
        .catch(err => {
            if (container) container.innerHTML = `<div style="text-align: center; color: #ef4444; padding: 20px;">Failed to load keys.</div>`;
        });
}

function renderModalKeysList(keys) {
    const container = document.getElementById('modal_keys_list_container');
    if (!container) return;

    if (keys.length === 0) {
        container.innerHTML = `<div style="text-align: center; padding: 30px; color: #94a3b8;"><i class="fa-solid fa-key" style="font-size: 28px; opacity: 0.4;"></i><p style="margin-top: 8px;">No keys available in this plan pool.</p></div>`;
        return;
    }

    let html = `<div style="display: flex; flex-direction: column; gap: 8px;">`;
    keys.forEach((k, idx) => {
        html += `
            <div class="modal-key-row" style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; padding: 8px 12px; border-radius: 8px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 11px; font-weight: 700; color: #94a3b8; width: 24px;">#${idx + 1}</span>
                    <span style="font-family: monospace; font-size: 13px; font-weight: 700; color: #0f172a;">${k.license_key}</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <button type="button" class="btn" style="background: #e2e8f0; color: #334155; font-size: 11px; padding: 4px 8px; border-radius: 4px; border: none; cursor: pointer;" onclick="copyToClipboard('${k.license_key}', this)">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                    <button type="button" class="btn" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; font-size: 11px; padding: 4px 8px; border-radius: 4px; border: none; cursor: pointer;" onclick="deleteSingleKey('${k.id}')">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    html += `</div>`;
    container.innerHTML = html;
}

function filterModalKeys(query) {
    const q = query.toLowerCase().trim();
    if (!q) {
        renderModalKeysList(currentBatchKeys);
        return;
    }
    const filtered = currentBatchKeys.filter(k => k.license_key.toLowerCase().includes(q));
    renderModalKeysList(filtered);
}

function toggleModalQuickAdd() {
    const wrap = document.getElementById('modal_quick_add_wrap');
    if (wrap) {
        wrap.style.display = (wrap.style.display === 'none' || wrap.style.display === '') ? 'block' : 'none';
    }
}

function closeBatchModal() {
    const modal = document.getElementById('batch_keys_modal');
    if (modal) modal.style.display = 'none';
}

function deleteSingleKey(keyId) {
    window.confirmAction({
        title: 'Delete Key?',
        text: 'Are you sure you want to permanently delete this license key?',
        icon: 'warning',
        confirmText: 'Yes, Delete',
        confirmColor: '#ef4444',
        onConfirm: () => {
            const form = document.getElementById('single_delete_form');
            form.action = `/admin/keys/${keyId}`;
            form.submit();
        }
    });
}

function copyToClipboard(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check" style="color: #059669;"></i>';
        setTimeout(() => {
            btn.innerHTML = orig;
        }, 1500);
    });
}

// Bulk delete checkbox handling
document.addEventListener('DOMContentLoaded', () => {
    const selectAll = document.getElementById('select_all_keys');
    const items = document.querySelectorAll('.key-item-checkbox');
    const btnBulk = document.getElementById('btn_bulk_delete');
    const countEl = document.getElementById('selected_count');

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            items.forEach(item => item.checked = this.checked);
            updateBulkBtn();
        });
    }

    items.forEach(item => {
        item.addEventListener('change', updateBulkBtn);
    });

    function updateBulkBtn() {
        const checked = document.querySelectorAll('.key-item-checkbox:checked');
        if (countEl) countEl.textContent = checked.length;
        if (btnBulk) btnBulk.style.display = checked.length > 0 ? 'inline-block' : 'none';
    }

    // Check saved view preference
    const viewPref = localStorage.getItem('keys_view_pref');
    const urlParams = new URLSearchParams(window.location.search);
    const viewParam = urlParams.get('view');
    if (viewParam === 'list' || viewPref === 'list') {
        switchStockView('list');
    }

    // Init first product durations
    updateDurationsForSelectedProduct();
});
</script>
@endsection
