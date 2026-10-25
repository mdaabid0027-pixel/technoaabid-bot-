@extends('admin.layout')

@section('title', 'Reseller History & Ledger - ' . ($user->first_name ?: $user->username))
@section('header_title', 'Reseller Profile & Purchase History')

@section('styles')
<style>
    .hero-profile-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
    }

    .hero-user-details {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .hero-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2563eb, #6366f1);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 800;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
    }

    .stats-pill-grid {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .stat-pill {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 18px;
        text-align: center;
    }

    .stat-pill-label {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-pill-val {
        font-size: 18px;
        font-weight: 800;
        margin-top: 2px;
    }

    /* Section Cards */
    .section-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
    }

    .section-header {
        padding: 16px 20px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .section-header h3 {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
    }

    .custom-table th {
        background: #f8fafc;
        padding: 12px 18px;
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
    }

    .custom-table td {
        padding: 14px 18px;
        font-size: 13px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .custom-table tr:hover td {
        background-color: #f8fafc;
    }

    .key-badge {
        font-family: monospace;
        font-weight: 700;
        background: #f1f5f9;
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .copy-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: #64748b;
        font-size: 12px;
        transition: color 0.2s;
    }

    .copy-btn:hover {
        color: #2563eb;
    }

    .badge-debit {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        padding: 3px 8px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 700;
    }

    .badge-credit {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        padding: 3px 8px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 700;
    }

    /* Adjust Balance Split */
    .adjust-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }

    @media (max-width: 1024px) {
        .adjust-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')

<!-- Back Link -->
<div style="margin-bottom: 16px;">
    <a href="{{ route('admin.users.index') }}" style="color: #2563eb; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; font-size: 13px;">
        <i class="fa-solid fa-arrow-left"></i> Back to Resellers Directory
    </a>
</div>

<!-- 1. Top Reseller Hero Card -->
<div class="hero-profile-card">
    <div class="hero-user-details">
        <div class="hero-avatar">
            {{ strtoupper(substr($user->first_name ?: ($user->username ?: 'U'), 0, 1)) }}
        </div>
        <div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <h2 style="font-size: 20px; font-weight: 800; color: #0f172a;">
                    {{ $user->first_name ?: 'Telegram Reseller' }} {{ $user->last_name }}
                </h2>
                @if($user->is_blocked)
                    <span style="padding: 2px 8px; background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; border-radius: 9999px; font-size: 10px; font-weight: 800;">
                        BLOCKED
                    </span>
                @else
                    <span style="padding: 2px 8px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #059669; border-radius: 9999px; font-size: 10px; font-weight: 800;">
                        ACTIVE
                    </span>
                @endif
            </div>
            <div style="font-size: 13px; color: #2563eb; font-weight: 600; margin-top: 2px;">
                {{ $user->username ? '@' . $user->username : 'No Username' }} &bull; <span style="color: #64748b; font-weight: 500;">Telegram ID: {{ $user->telegram_id }}</span>
            </div>
        </div>
    </div>

    <!-- Quick Stats Summary -->
    <div class="stats-pill-grid">
        <div class="stat-pill">
            <div class="stat-pill-label">Wallet Balance</div>
            <div class="stat-pill-val" style="color: #059669;">₹{{ number_format($user->balance, 2) }}</div>
        </div>

        <div class="stat-pill">
            <div class="stat-pill-label">Total Spent</div>
            <div class="stat-pill-val" style="color: #dc2626;">₹{{ number_format($totalSpent, 2) }}</div>
        </div>

        <div class="stat-pill">
            <div class="stat-pill-label">Keys Bought</div>
            <div class="stat-pill-val" style="color: #9333ea;">{{ $totalKeysBought }} Keys</div>
        </div>

        <div class="stat-pill">
            <div class="stat-pill-label">Invited Users</div>
            <div class="stat-pill-val" style="color: #16a34a;">
                {{ $inviteCount }} <span style="font-size: 12px; font-weight: 600; color: #15803d;">(+₹{{ number_format($inviteEarnings, 2) }})</span>
            </div>
        </div>

        <div class="stat-pill">
            <div class="stat-pill-label">Spin Earnings</div>
            <div class="stat-pill-val" style="color: #7c3aed;">
                ₹{{ number_format($spinEarnings, 2) }} <span style="font-size: 12px; font-weight: 600; color: #6d28d9;">({{ $totalSpins }} spins)</span>
            </div>
        </div>

        <div class="stat-pill">
            <div class="stat-pill-label">Total Topup</div>
            <div class="stat-pill-val" style="color: #2563eb;">₹{{ number_format($totalDeposited, 2) }}</div>
        </div>
    </div>
</div>

<!-- 2. Main Content Split: Keys Purchased Log & Adjust Balance -->
<div class="adjust-grid">
    
    <!-- LEFT: Custom Rates, Purchased Keys & Financial Ledger -->
    <div style="display: flex; flex-direction: column; gap: 24px;">

        @if(isset($customPrices) && $customPrices->count() > 0)
            <!-- Assigned Custom Rates Section -->
            <div class="section-card">
                <div class="section-header" style="background: #faf5ff; border-bottom: 1px solid #f3e8ff;">
                    <h3 style="color: #6b21a8;">
                        <i class="fa-solid fa-tags" style="color: #9333ea;"></i>
                        <span>Assigned Custom Reseller Rates ({{ $customPrices->count() }})</span>
                    </h3>
                    <a href="{{ route('admin.keys.index') }}#vip-pricing" style="font-size: 12px; color: #7c3aed; font-weight: 600; text-decoration: none;">
                        + Manage in Keys & Pricing &rarr;
                    </a>
                </div>
                <div style="overflow-x: auto;">
                    <table class="custom-table">
                        <thead style="background: #fdf4ff;">
                            <tr>
                                <th>Product / Tool</th>
                                <th>Duration</th>
                                <th>Market Price</th>
                                <th>Assigned Rate</th>
                                <th>Margin / Discount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customPrices as $cp)
                                @php
                                    $basePrice = $cp->product ? $cp->product->getPriceForDuration($cp->duration) : 0;
                                    $discount = $basePrice > 0 ? max(0, $basePrice - $cp->price) : 0;
                                    $discountPercent = $basePrice > 0 ? round(($discount / $basePrice) * 100) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                            <i class="fa-solid fa-gamepad" style="color: #9333ea;"></i>
                                            {{ $cp->product->name ?? 'Product' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span style="padding: 2px 8px; background: #eef2ff; border: 1px solid #c7d2fe; color: #4338ca; border-radius: 6px; font-weight: 700; font-size: 11px;">
                                            {{ strtoupper($cp->duration) }}
                                        </span>
                                    </td>
                                    <td style="color: #64748b; text-decoration: line-through; font-size: 12px;">
                                        ₹{{ number_format($basePrice, 2) }}
                                    </td>
                                    <td style="font-weight: 800; color: #059669; font-size: 14px;">
                                        ₹{{ number_format($cp->price, 2) }}
                                    </td>
                                    <td>
                                        <span style="display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #f3e8ff; color: #7e22ce;">
                                            Save ₹{{ number_format($discount, 2) }} ({{ $discountPercent }}% Margin)
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
        
        <!-- Purchased Keys History -->
        <div class="section-card">
            <div class="section-header">
                <h3>
                    <i class="fa-solid fa-key" style="color: #9333ea;"></i>
                    <span>Purchased License Keys History ({{ $purchasedKeys->total() }})</span>
                </h3>
                <span style="font-size: 12px; color: #64748b;">Keys delivered via Bot</span>
            </div>

            @if($purchasedKeys->isEmpty())
                <div style="padding: 40px 20px; text-align: center; color: #94a3b8;">
                    <i class="fa-solid fa-key-skeleton" style="font-size: 32px; color: #cbd5e1; margin-bottom: 8px;"></i>
                    <div>This reseller has not purchased any license keys yet.</div>
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Product / Game</th>
                                <th>Duration</th>
                                <th>License Key</th>
                                <th>Purchased On</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchasedKeys as $key)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #0f172a;">{{ $key->product->name ?? 'License Key' }}</div>
                                        <div style="font-size: 11px; color: #64748b;">{{ $key->product->game_name ?? 'AutoPlay' }}</div>
                                    </td>
                                    <td>
                                        <span style="padding: 3px 8px; background: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb; border-radius: 6px; font-weight: 700; font-size: 11px;">
                                            {{ strtoupper($key->duration) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="key-badge">
                                            <span>{{ $key->license_key }}</span>
                                            <button class="copy-btn" onclick="navigator.clipboard.writeText('{{ $key->license_key }}'); alert('Key copied to clipboard!');" title="Copy Key">
                                                <i class="fa-regular fa-copy"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td style="font-size: 12px; color: #64748b;">
                                        {{ $key->sold_at ? $key->sold_at->format('d M Y, h:i A') : $key->updated_at->format('d M Y, h:i A') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="padding: 12px 20px; border-top: 1px solid #e2e8f0;">
                    {{ $purchasedKeys->appends(['tx_page' => $transactions->currentPage()])->links() }}
                </div>
            @endif
        </div>

        <!-- Financial Transaction Ledger -->
        <div class="section-card">
            <div class="section-header">
                <h3>
                    <i class="fa-solid fa-receipt" style="color: #10b981;"></i>
                    <span>Financial Transaction Ledger ({{ $transactions->total() }})</span>
                </h3>
                <span style="font-size: 12px; color: #64748b;">All Debits & Credits</span>
            </div>

            @if($transactions->isEmpty())
                <div style="padding: 40px 20px; text-align: center; color: #94a3b8;">
                    No transactions recorded for this reseller.
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Description</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $tx)
                                <tr>
                                    <td>
                                        <span class="{{ $tx->type === 'debit' ? 'badge-debit' : 'badge-credit' }}">
                                            {{ strtoupper($tx->type) }}
                                        </span>
                                    </td>
                                    <td style="font-weight: 700; color: {{ $tx->type === 'debit' ? '#dc2626' : '#059669' }};">
                                        {{ $tx->type === 'debit' ? '-' : '+' }}₹{{ number_format($tx->amount, 2) }}
                                    </td>
                                    <td style="font-size: 12px; color: #334155;">
                                        {{ $tx->description }}
                                    </td>
                                    <td style="font-size: 12px; color: #64748b;">
                                        {{ $tx->created_at->format('d M Y, h:i A') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="padding: 12px 20px; border-top: 1px solid #e2e8f0;">
                    {{ $transactions->appends(['keys_page' => $purchasedKeys->currentPage()])->links() }}
                </div>
            @endif
        </div>

    </div>

    <!-- RIGHT: Adjust Wallet Balance Card -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        
        <div class="section-card" style="position: sticky; top: 80px;">
            <div class="section-header">
                <h3>
                    <i class="fa-solid fa-money-bill-transfer" style="color: #059669;"></i>
                    <span>Adjust Wallet Balance</span>
                </h3>
            </div>

            <div style="padding: 20px;">
                <form action="{{ route('admin.users.adjust-balance', $user->id) }}" method="POST">
                    @csrf

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px;">Action Type</label>
                        <select name="type" class="form-control" style="width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-weight: 600;" required>
                            <option value="credit">➕ Credit (Add Balance)</option>
                            <option value="debit">➖ Debit (Deduct Balance)</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px;">Amount (₹ INR)</label>
                        <input type="number" step="0.01" min="1" name="amount" class="form-control" placeholder="e.g. 500" style="width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 15px; font-weight: 700;" required>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px;">Reason / Description</label>
                        <input type="text" name="description" class="form-control" placeholder="e.g. UPI Topup verification, Manual adjustment" style="width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px;">
                    </div>

                    <button type="submit" style="width: 100%; padding: 12px; background: #059669; color: #ffffff; border: none; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s;">
                        <i class="fa-solid fa-check"></i> Submit Balance Update
                    </button>
                </form>

                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0;">
                    <form action="{{ route('admin.users.toggle-block', $user->id) }}" method="POST" @if(!$user->is_blocked) data-confirm="Are you sure you want to block this user from using the bot?" data-confirm-title="Block User" data-confirm-btn="Yes, Block" data-confirm-color="#dc2626" @endif>
                        @csrf
                        @if($user->is_blocked)
                            <button type="submit" style="width: 100%; padding: 10px; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer;">
                                <i class="fa-solid fa-lock-open"></i> Unblock Reseller
                            </button>
                        @else
                            <button type="submit" style="width: 100%; padding: 10px; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer;">
                                <i class="fa-solid fa-ban"></i> Block Reseller
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection
