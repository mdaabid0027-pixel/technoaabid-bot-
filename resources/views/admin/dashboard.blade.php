@extends('admin.layout')

@section('title', 'Dashboard')
@section('header_title', 'Executive Sales & Analytics Dashboard')

@section('styles')
<style>
    /* Filter Bar */
    .filter-toolbar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }

    .preset-pills {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .filter-pill {
        padding: 7px 14px;
        font-size: 12.5px;
        font-weight: 700;
        border-radius: 8px;
        text-decoration: none;
        color: #475569;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .filter-pill:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .filter-pill.active {
        background: #4f46e5;
        color: #ffffff;
        border-color: #4338ca;
        box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
    }

    .custom-date-form {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        flex-wrap: wrap;
    }

    .date-input-group {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .date-label {
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
    }

    .date-input {
        padding: 7px 10px;
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        outline: none;
        background: #ffffff;
        color: #1e293b;
        font-weight: 600;
    }

    .btn-apply-date {
        padding: 7px 16px;
        font-size: 12px;
        font-weight: 700;
        border-radius: 6px;
        background: #4f46e5;
        color: #ffffff;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background 0.15s ease;
    }

    .btn-apply-date:hover {
        background: #4338ca;
    }

    @media (max-width: 768px) {
        .filter-toolbar {
            flex-direction: column;
            align-items: stretch !important;
            padding: 12px 14px;
            gap: 12px;
        }

        .filter-toolbar > div:first-child {
            width: 100%;
        }

        .preset-pills {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
            width: 100%;
        }

        .filter-pill {
            justify-content: center;
            padding: 7px 4px;
            font-size: 11px;
            text-align: center;
        }

        .custom-date-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            width: 100%;
            padding-top: 10px;
            border-top: 1px dashed #e2e8f0;
        }

        .date-input-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
            width: 100%;
        }

        .date-input {
            width: 100%;
            box-sizing: border-box;
            font-size: 12px;
            padding: 7px 8px;
        }

        .btn-apply-date {
            grid-column: span 2;
            width: 100%;
            justify-content: center;
            padding: 10px;
            font-size: 13px;
            margin-top: 2px;
        }
    }

    /* 5-Column Metrics Grid */
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .metric-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.06);
    }

    .metric-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .metric-title {
        font-size: 11.5px;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .metric-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
    }

    .metric-value {
        font-size: 24px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
        margin-bottom: 8px;
    }

    .metric-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 11.5px;
        color: #64748b;
        font-weight: 600;
        border-top: 1px solid #f1f5f9;
        padding-top: 8px;
        margin-top: 4px;
    }

    .badge-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 7px;
        border-radius: 6px;
        font-size: 10.5px;
        font-weight: 800;
    }

    /* Icon Variants */
    .icon-emerald { background: #ecfdf5; color: #059669; }
    .icon-blue { background: #eff6ff; color: #2563eb; }
    .icon-purple { background: #faf5ff; color: #9333ea; }
    .icon-rose { background: #fff1f2; color: #e11d48; }
    .icon-amber { background: #fffbeb; color: #d97706; }

    /* 2 Column Main Content Grid */
    .dashboard-split {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    @media (max-width: 1024px) {
        .dashboard-split {
            grid-template-columns: 1fr;
        }
    }

    .quick-action-btn {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        text-decoration: none;
        color: #0f172a;
        font-weight: 700;
        font-size: 13px;
        transition: all 0.15s ease;
    }

    .quick-action-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        transform: translateX(2px);
    }
</style>
@endsection

@section('content')
<div style="max-width: 1360px; margin: 0 auto;">

    <!-- TOP EXECUTIVE DATE FILTER TOOLBAR -->
    <div class="filter-toolbar">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <div style="font-size: 12.5px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-calendar-days" style="color: #4f46e5;"></i>
                <span>Date Range:</span>
            </div>

            <div class="preset-pills">
                <a href="{{ route('admin.dashboard', ['period' => 'today']) }}" class="filter-pill {{ $period === 'today' ? 'active' : '' }}">
                    Today
                </a>
                <a href="{{ route('admin.dashboard', ['period' => 'yesterday']) }}" class="filter-pill {{ $period === 'yesterday' ? 'active' : '' }}">
                    Yesterday
                </a>
                <a href="{{ route('admin.dashboard', ['period' => '7days']) }}" class="filter-pill {{ $period === '7days' ? 'active' : '' }}">
                    Last 7 Days
                </a>
                <a href="{{ route('admin.dashboard', ['period' => '30days']) }}" class="filter-pill {{ $period === '30days' ? 'active' : '' }}">
                    Last 30 Days
                </a>
                <a href="{{ route('admin.dashboard', ['period' => 'this_month']) }}" class="filter-pill {{ $period === 'this_month' ? 'active' : '' }}">
                    This Month
                </a>
                <a href="{{ route('admin.dashboard', ['period' => 'all']) }}" class="filter-pill {{ $period === 'all' ? 'active' : '' }}">
                    All Time
                </a>
            </div>
        </div>

        <!-- Custom Date Range Picker -->
        <form action="{{ route('admin.dashboard') }}" method="GET" class="custom-date-form">
            <input type="hidden" name="period" value="custom">
            <div class="date-input-group">
                <span class="date-label">From:</span>
                <input type="date" name="start_date" class="date-input" value="{{ $customStart ?: $startDate->format('Y-m-d') }}" required>
            </div>
            <div class="date-input-group">
                <span class="date-label">To:</span>
                <input type="date" name="end_date" class="date-input" value="{{ $customEnd ?: $endDate->format('Y-m-d') }}" required>
            </div>
            <button type="submit" class="btn-apply-date">
                <i class="fa-solid fa-filter"></i> Apply
            </button>
        </form>
    </div>

    <!-- Active Period Label Banner -->
    <div style="margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between;">
        <div style="font-size: 13px; font-weight: 700; color: #64748b;">
            Showing analytics for: <span style="color: #4f46e5; font-weight: 800;">{{ $periodLabel }}</span>
        </div>
        <div style="font-size: 12px; color: #64748b;">
            Updated in Real-Time
        </div>
    </div>

    <!-- 5 EXECUTIVE KPI STAT CARDS -->
    <div class="metrics-grid">
        
        <!-- CARD 1: GROSS SALES -->
        <div class="metric-card">
            <div>
                <div class="metric-header">
                    <span class="metric-title">Sales (Revenue)</span>
                    <div class="metric-icon icon-emerald">
                        ₹
                    </div>
                </div>
                <div class="metric-value">
                    ₹{{ number_format($periodSales, 2) }}
                </div>
            </div>
            <div class="metric-footer">
                @if($salesGrowth !== null)
                    <span class="badge-chip {{ $salesGrowth >= 0 ? 'growth-positive' : 'text-danger' }}" style="background: {{ $salesGrowth >= 0 ? '#ecfdf5' : '#fef2f2' }}; color: {{ $salesGrowth >= 0 ? '#059669' : '#dc2626' }};">
                        <i class="fa-solid fa-arrow-{{ $salesGrowth >= 0 ? 'trend-up' : 'trend-down' }}"></i> {{ $salesGrowth }}%
                    </span>
                @else
                    <span class="badge-chip" style="background: #f1f5f9; color: #475569;">All-Time</span>
                @endif
                <span>Lifetime: ₹{{ number_format($lifetimeSales, 0) }}</span>
            </div>
        </div>

        <!-- CARD 2: NET PROFIT -->
        <div class="metric-card">
            <div>
                <div class="metric-header">
                    <span class="metric-title">Net Profit</span>
                    <div class="metric-icon icon-blue">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                </div>
                <div class="metric-value" style="color: #059669;">
                    ₹{{ number_format($periodNetProfit, 2) }}
                </div>
            </div>
            <div class="metric-footer">
                <span class="badge-chip" style="background: #eff6ff; color: #2563eb;">
                    {{ $profitMarginPercent }}% Margin
                </span>
                <span>Cost: ₹{{ number_format($periodCost, 0) }}</span>
            </div>
        </div>

        <!-- CARD 3: KEYS SOLD -->
        <div class="metric-card">
            <div>
                <div class="metric-header">
                    <span class="metric-title">Keys Sold</span>
                    <div class="metric-icon icon-purple">
                        <i class="fa-solid fa-key"></i>
                    </div>
                </div>
                <div class="metric-value">
                    {{ $periodKeysSold }} <span style="font-size: 14px; font-weight: 600; color: #64748b;">Keys</span>
                </div>
            </div>
            <div class="metric-footer">
                <span class="badge-chip" style="background: #faf5ff; color: #9333ea;">Period Orders</span>
                <span>Total: {{ $lifetimeKeysSold }} Sold</span>
            </div>
        </div>

        <!-- CARD 4: FREE REWARDS & CLAIMS GIVEN (OUTFLOW / ANTI-FRAUD) -->
        <div class="metric-card" style="border-left: 3px solid #e11d48;">
            <div>
                <div class="metric-header">
                    <span class="metric-title" style="color: #be123c;">Free Claims Given</span>
                    <div class="metric-icon icon-rose">
                        <i class="fa-solid fa-gift"></i>
                    </div>
                </div>
                <div class="metric-value" style="color: #be123c;">
                    ₹{{ number_format($periodTotalClaimsAmount, 2) }}
                </div>
            </div>
            <div class="metric-footer">
                <span class="badge-chip" style="background: #fff1f2; color: #e11d48;">
                    {{ $periodTotalClaimsCount }} Claims
                </span>
                <span title="Total given out via Spins & Referrals">Spins: ₹{{ number_format($periodSpinClaimsAmount, 0) }} • Ref: ₹{{ number_format($periodReferralClaimsAmount, 0) }}</span>
            </div>
        </div>

        <!-- CARD 5: AVAILABLE KEY STOCK & USERS -->
        <div class="metric-card">
            <div>
                <div class="metric-header">
                    <span class="metric-title">Available Stock</span>
                    <div class="metric-icon icon-amber">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                </div>
                <div class="metric-value">
                    {{ $totalKeysInStock }} <span style="font-size: 14px; font-weight: 600; color: #64748b;">Keys</span>
                </div>
            </div>
            <div class="metric-footer">
                <span class="badge-chip" style="background: #ecfdf5; color: #059669;">● Live Stock</span>
                <span>{{ $totalUsers }} Users</span>
            </div>
        </div>

    </div>

    <!-- 2 COLUMN SPLIT: PERFORMANCE CHART & QUICK ACTIONS -->
    <div class="dashboard-split">
        
        <!-- LEFT: SALES & CLAIMS CHART -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">
                        📈 Sales vs Free Claims Distribution
                    </h3>
                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Daily Revenue vs Free Claimed Rewards</div>
                </div>
                <div style="display: flex; gap: 14px; font-size: 12px; font-weight: 700;">
                    <span style="color: #4f46e5; display: flex; align-items: center; gap: 6px;">
                        <span style="width: 10px; height: 10px; border-radius: 2px; background: #4f46e5;"></span> Revenue (₹)
                    </span>
                    <span style="color: #e11d48; display: flex; align-items: center; gap: 6px;">
                        <span style="width: 10px; height: 10px; border-radius: 2px; background: #e11d48;"></span> Claims Paid (₹)
                    </span>
                </div>
            </div>

            <div style="height: 250px; position: relative;">
                <canvas id="salesAnalyticsChart"></canvas>
            </div>
        </div>

        <!-- RIGHT: QUICK ACTIONS PANEL -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h4 style="font-size: 15px; font-weight: 800; color: #0f172a; margin: 0 0 14px 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-bolt" style="color: #f59e0b;"></i> Quick Controls
                </h4>

                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="{{ route('admin.customers.index') }}" class="quick-action-btn" style="border-left: 3px solid #10b981;">
                        <div style="width: 32px; height: 32px; border-radius: 6px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="color: #065f46; font-weight: 800;">Wallet Top-Up / Adjust</div>
                            <div style="font-size: 11px; color: #047857; font-weight: 500;">Add/Deduct balance for any user</div>
                        </div>
                        <i class="fa-solid fa-chevron-right" style="font-size: 11px; color: #10b981;"></i>
                    </a>

                    <a href="{{ route('admin.keys.index') }}" class="quick-action-btn">
                        <div style="width: 32px; height: 32px; border-radius: 6px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-key"></i>
                        </div>
                        <div style="flex: 1;">
                            <div>Add Stock / Keys</div>
                            <div style="font-size: 11px; color: #64748b; font-weight: 500;">Upload bulk game keys</div>
                        </div>
                        <i class="fa-solid fa-chevron-right" style="font-size: 11px; color: #94a3b8;"></i>
                    </a>

                    <a href="{{ route('admin.pricing.index') }}" class="quick-action-btn">
                        <div style="width: 32px; height: 32px; border-radius: 6px; background: #faf5ff; color: #9333ea; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-tags"></i>
                        </div>
                        <div style="flex: 1;">
                            <div>Pricing Matrix</div>
                            <div style="font-size: 11px; color: #64748b; font-weight: 500;">Edit rates & profit margins</div>
                        </div>
                        <i class="fa-solid fa-chevron-right" style="font-size: 11px; color: #94a3b8;"></i>
                    </a>

                    <a href="{{ route('admin.sounds.index') }}" class="quick-action-btn">
                        <div style="width: 32px; height: 32px; border-radius: 6px; background: #fffbeb; color: #d97706; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-music"></i>
                        </div>
                        <div style="flex: 1;">
                            <div>Sound FX & Memes</div>
                            <div style="font-size: 11px; color: #64748b; font-weight: 500;">Manage bot voice notes</div>
                        </div>
                        <i class="fa-solid fa-chevron-right" style="font-size: 11px; color: #94a3b8;"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- ChartJS Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('salesAnalyticsChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [
                {
                    label: 'Revenue (₹)',
                    data: {!! json_encode($chartRevenue) !!},
                    backgroundColor: 'rgba(79, 70, 229, 0.85)',
                    borderRadius: 4,
                    barThickness: 16,
                },
                {
                    label: 'Claims Paid (₹)',
                    data: {!! json_encode($chartClaims) !!},
                    backgroundColor: 'rgba(225, 29, 72, 0.75)',
                    borderRadius: 4,
                    barThickness: 16,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.dataset.label + ': ₹' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11, weight: '600' }, color: '#64748b' }
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { size: 11, weight: '600' },
                        color: '#64748b',
                        callback: function(value) { return '₹' + value; }
                    }
                }
            }
        }
    });
});
</script>
@endsection
