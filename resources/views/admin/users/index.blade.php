@php
    $isResellerView = ($viewType ?? 'customers') === 'resellers';
    $pageTitle = $isResellerView ? 'Resellers' : 'Customers';
    $searchRoute = $isResellerView ? route('admin.resellers.index') : route('admin.customers.index');
@endphp

@extends('admin.layout')

@section('title', $pageTitle)
@section('header_title', $pageTitle)

@section('styles')
<style>
    .reseller-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
    }

    .top-controls {
        padding: 20px 24px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .search-input-box {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 8px 14px;
        width: 320px;
        max-width: 100%;
    }

    .search-input-box input {
        border: none;
        background: transparent;
        outline: none;
        font-size: 14px;
        width: 100%;
        color: #0f172a;
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
        white-space: nowrap;
    }

    .custom-table td {
        padding: 16px 18px;
        font-size: 13px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        white-space: nowrap;
    }

    .custom-table tr:hover td {
        background-color: #f8fafc;
    }

    .user-profile-meta {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 15px;
    }

    .action-btn-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-action {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-view {
        background: #eff6ff;
        color: #2563eb;
        border-color: #bfdbfe;
    }

    .btn-view:hover {
        background: #2563eb;
        color: #ffffff;
    }

    .btn-vip {
        background: #faf5ff;
        color: #7c3aed;
        border-color: #e9d5ff;
    }

    .btn-vip:hover {
        background: #7c3aed;
        color: #ffffff;
    }

    .btn-block {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
    }

    .btn-block:hover {
        background: #dc2626;
        color: #ffffff;
    }

    .btn-unblock {
        background: #ecfdf5;
        color: #059669;
        border-color: #a7f3d0;
    }

    .btn-unblock:hover {
        background: #059669;
        color: #ffffff;
    }

    @media (max-width: 768px) {
        .top-controls {
            padding: 14px 12px !important;
            gap: 12px !important;
            flex-direction: column !important;
            align-items: stretch !important;
        }
        .top-controls > div {
            width: 100% !important;
            justify-content: space-between !important;
        }
        .search-input-box {
            width: 100% !important;
        }
        .reseller-card {
            border-radius: 12px !important;
        }
    }
</style>
@endsection

@section('content')

<div class="reseller-card">
    <div class="top-controls">
        <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
            <div>
                <h2 style="font-size: 22px; font-weight: 600; color: #0f172a; margin: 0; letter-spacing: -0.3px;">
                    @if($isResellerView)
                        Resellers ({{ $users->total() }})
                    @else
                        Customers ({{ $users->total() }})
                    @endif
                </h2>
            </div>

            <!-- Bulk Delete Button -->
            <button type="button" id="btn_bulk_delete_users" onclick="submitBulkDeleteUsers()" class="btn-action btn-block" style="display: none; background: #dc2626; color: #ffffff; border: none; padding: 8px 16px; font-size: 13px; font-weight: 700; border-radius: 8px; box-shadow: 0 2px 6px rgba(220, 38, 38, 0.25);">
                <i class="fa-solid fa-trash-can"></i> Delete Selected (<span id="selected_users_count">0</span>)
            </button>
        </div>

        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <button type="button" onclick="openAdjustBalanceModal(null, '', 0)" class="btn-action" style="padding: 8px 16px; font-weight: 700; background: #10b981; color: #ffffff; border: none; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);">
                <i class="fa-solid fa-wallet"></i> Top-Up / Adjust Balance
            </button>
            @if($isResellerView)
                <button type="button" onclick="openAddResellerModal()" class="btn-action btn-vip" style="padding: 8px 16px; font-weight: 700; background: #7c3aed; color: #ffffff; border: none; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-user-plus"></i> Add Reseller
                </button>
                <a href="{{ route('admin.pricing.index', ['tab' => 'standard_reseller']) }}" class="btn-action" style="padding: 8px 14px; text-decoration: none; background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; font-weight: 700;">
                    <i class="fa-solid fa-crown"></i> Standard Reseller Rates
                </a>
            @else
                <a href="{{ route('admin.pricing.index', ['tab' => 'customer']) }}" class="btn-action" style="padding: 8px 14px; text-decoration: none; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-weight: 700;">
                    <i class="fa-solid fa-tags"></i> Customer Rates
                </a>
            @endif

            <form action="{{ $searchRoute }}" method="GET" style="margin: 0;">
                <div class="search-input-box">
                    <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
                    <input type="text" name="search" placeholder="Search by name, @username, or ID..." value="{{ $search }}">
                    @if($search)
                        <a href="{{ $searchRoute }}" style="color: #94a3b8; text-decoration: none;" title="Clear search">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if($users->isEmpty())
        <div style="padding: 60px 20px; text-align: center; color: #64748b;">
            @if($isResellerView)
                <i class="fa-solid fa-user-tag" style="font-size: 40px; color: #e9d5ff; margin-bottom: 12px;"></i>
                <div style="font-weight: 600; font-size: 15px; color: #1e1b4b;">No Resellers Found</div>
                <div style="font-size: 13px; color: #64748b; margin-top: 4px; max-width: 460px; margin-left: auto; margin-right: auto;">
                    @if($search)
                        No resellers matched your query "{{ $search }}".
                    @else
                        You haven't assigned custom prices to any customer yet. Assign custom prices from Keys & Pricing to promote customers to Resellers.
                    @endif
                </div>
                @if(!$search)
                    <div style="margin-top: 16px;">
                        <a href="{{ route('admin.keys.index') }}" class="btn-action btn-vip" style="padding: 10px 20px; font-size: 13px;">
                            <i class="fa-solid fa-tag me-1"></i> Go to Set Reseller Prices
                        </a>
                    </div>
                @endif
            @else
                <i class="fa-regular fa-user-slash" style="font-size: 40px; color: #cbd5e1; margin-bottom: 12px;"></i>
                <div style="font-weight: 600; font-size: 15px;">No Customers Found</div>
                <div style="font-size: 13px; color: #94a3b8; margin-top: 4px;">
                    @if($search)
                        No users matched your query "{{ $search }}".
                    @else
                        Customers will appear here as soon as they start using your Telegram bot.
                    @endif
                </div>
            @endif
        </div>
    @else
        <div style="overflow-x: auto;">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 42px; text-align: center;">
                            <input type="checkbox" id="select_all_users" style="width: 16px; height: 16px; accent-color: #2563eb; cursor: pointer;">
                        </th>
                        <th style="width: 50px; text-align: center;">#</th>
                        <th>{{ $isResellerView ? 'Reseller' : 'Customer' }}</th>
                        <th>Telegram ID</th>
                        <th>Wallet</th>
                        <th>Keys</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td style="text-align: center;">
                                <input type="checkbox" name="ids[]" class="user-item-checkbox" value="{{ $user->id }}" style="width: 16px; height: 16px; accent-color: #2563eb; cursor: pointer;">
                            </td>
                            <td style="font-weight: 700; color: #94a3b8; text-align: center; font-size: 13px;">
                                {{ $users->firstItem() + $loop->index }}
                            </td>
                            <td>
                                <div class="user-profile-meta">
                                    <div class="user-avatar" style="background: {{ $isResellerView ? 'linear-gradient(135deg, #7c3aed, #a855f7)' : 'linear-gradient(135deg, #3b82f6, #6366f1)' }};">
                                        {{ strtoupper(substr($user->first_name ?: ($user->username ?: 'U'), 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: #0f172a; font-size: 14px;">
                                            {{ $user->first_name ?: 'Telegram User' }} {{ $user->last_name }}
                                        </div>
                                        <div style="font-size: 12px; color: {{ $isResellerView ? '#7c3aed' : '#2563eb' }}; font-weight: 500;">
                                            {{ $user->username ? '@' . $user->username : 'No Username' }}
                                        </div>
                                        @if($user->email)
                                            <div style="font-size: 11px; color: #475569; font-weight: 600; margin-top: 2px;">
                                                <i class="fa-regular fa-envelope" style="color: #64748b;"></i> {{ $user->email }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td style="font-family: monospace; font-weight: 600; color: #475569;">
                                {{ $user->telegram_id }}
                            </td>
                            <td>
                                <span style="font-size: 15px; font-weight: 800; color: #059669;">
                                    ₹{{ number_format($user->balance, 2) }}
                                </span>
                            </td>
                            <td>
                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; background: #faf5ff; border: 1px solid #e9d5ff; color: #9333ea; border-radius: 9999px; font-weight: 700; font-size: 12px;">
                                    <i class="fa-solid fa-key"></i> {{ $user->total_keys_bought ?? 0 }} Keys
                                </span>
                            </td>
                            <td>
                                <span style="font-weight: 700; color: #dc2626;">
                                    ₹{{ number_format($user->total_spent ?? 0, 2) }}
                                </span>
                            </td>
                            <td>
                                @if($user->is_blocked)
                                    <span style="padding: 4px 10px; background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; border-radius: 9999px; font-size: 11px; font-weight: 700;">
                                        <i class="fa-solid fa-ban"></i> BLOCKED
                                    </span>
                                @else
                                    <span style="padding: 4px 10px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #059669; border-radius: 9999px; font-size: 11px; font-weight: 700;">
                                        <i class="fa-solid fa-circle-check"></i> ACTIVE
                                    </span>
                                @endif
                            </td>
                            <td style="font-size: 12px; color: #64748b;">
                                {{ $user->created_at->format('d M Y') }}
                            </td>
                            <td>
                                <div class="action-btn-group">
                                    <button type="button" onclick="openAdjustBalanceModal({{ $user->id }}, '{{ addslashes($user->first_name ?: ($user->username ? '@' . $user->username : 'User #' . $user->id)) }}', {{ $user->balance }})" class="btn-action" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; font-weight: 700;" title="Add or Deduct Balance">
                                        <i class="fa-solid fa-wallet"></i> Top-Up
                                    </button>

                                    <button type="button" onclick="openUserHistoryModal({{ $user->id }})" class="btn-action btn-view" title="Quick Info, Keys & Rates History">
                                        <i class="fa-solid fa-circle-info"></i> Info
                                    </button>

                                    @if($isResellerView)
                                        <a href="{{ route('admin.pricing.index', ['tab' => 'vip_custom', 'reseller_id' => $user->id]) }}" class="btn-action btn-vip" title="Set VIP Custom Rates For This Reseller">
                                            <i class="fa-solid fa-star"></i> Custom Rates
                                        </a>
                                        <form action="{{ route('admin.users.toggle-reseller', $user->id) }}" method="POST" style="margin: 0;" data-confirm="Demote this reseller back to regular customer?" data-confirm-title="Demote Reseller" data-confirm-btn="Yes, Demote" data-confirm-color="#475569">
                                            @csrf
                                            <button type="submit" class="btn-action" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;" title="Demote to Regular Customer">
                                                <i class="fa-solid fa-user-minus"></i> Demote
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.users.toggle-reseller', $user->id) }}" method="POST" style="margin: 0;" data-confirm="Promote this customer to VIP Reseller with special pricing privileges?" data-confirm-title="Make VIP Reseller" data-confirm-btn="Yes, Promote" data-confirm-color="#6366f1" data-confirm-icon="question">
                                            @csrf
                                            <button type="submit" class="btn-action btn-vip" title="Promote to VIP Reseller">
                                                <i class="fa-solid fa-crown"></i> Make Reseller
                                            </button>
                                        </form>
                                    @endif

                                    @if($user->email)
                                        <form action="{{ route('admin.users.notify-bitaim-activated', $user->id) }}" method="POST" style="margin: 0;" data-confirm="Send bitAIM+ activation notification to {{ addslashes($user->first_name ?: $user->username) }} ({{ $user->email }}) on Telegram?" data-confirm-title="Send bitAIM+ Notification" data-confirm-btn="Yes, Notify Customer" data-confirm-color="#10b981" data-confirm-icon="question">
                                            @csrf
                                            <button type="submit" class="btn-action" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; font-weight: 700;" title="Send 1-Click bitAIM+ Activation message to customer">
                                                <i class="fa-solid fa-bolt"></i> Notify bitAIM
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('admin.users.toggle-block', $user->id) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        @if($user->is_blocked)
                                            <button type="submit" class="btn-action btn-unblock" title="Unblock User">
                                                <i class="fa-solid fa-lock-open"></i>
                                            </button>
                                        @else
                                            <button type="submit" class="btn-action btn-block" title="Block User">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        @endif
                                    </form>

                                    <!-- Delete Single User -->
                                    <button type="button" onclick="deleteSingleUser({{ $user->id }}, '{{ addslashes($user->first_name ?: $user->username) }}')" class="btn-action btn-block" style="background: #fff1f2; color: #e11d48; border-color: #fecdd3;" title="Delete User">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="padding: 16px 24px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-size: 13px; color: #64748b;">
                Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} {{ $isResellerView ? 'resellers' : 'customers' }}
            </div>
            <div>
                {{ $users->links() }}
            </div>
        </div>
    @endif
</div>

<!-- Rich User History Info Modal -->
<div id="userHistoryModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 2000; align-items: center; justify-content: center; padding: 20px;" onclick="if(event.target === this) closeUserHistoryModal()">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 820px; max-height: 85vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; border: 1px solid #cbd5e1;">
        
        <!-- Modal Header -->
        <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div id="mUserAvatar" style="width: 44px; height: 44px; border-radius: 10px; background: #2563eb; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 18px;">U</div>
                <div>
                    <h4 id="mUserName" style="margin: 0; font-size: 16px; color: #0f172a; font-weight: 700;">Customer / Reseller</h4>
                    <div id="mUserMeta" style="font-size: 12px; color: #64748b;">@username • Telegram ID: 12345678</div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 18px;">
                <div style="text-align: right;">
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Wallet Balance</div>
                    <div id="mUserBalance" style="font-size: 18px; font-weight: 800; color: #059669;">₹0.00</div>
                </div>
                <button type="button" onclick="closeUserHistoryModal()" style="background: #e2e8f0; border: none; font-size: 16px; color: #475569; width: 32px; height: 32px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div id="mModalBody" style="padding: 22px 24px; overflow-y: auto; flex: 1;">
            
            <!-- Loading Indicator -->
            <div id="mLoading" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 0; gap: 12px;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 28px; color: #2563eb;"></i>
                <span style="font-size: 13px; color: #64748b; font-weight: 500;">Loading purchase history & keys...</span>
            </div>

            <!-- Content Container -->
            <div id="mContent" style="display: none;">
                
                <!-- Metrics Bar (4 Cards) -->
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 20px;">
                    <!-- 1. Total Keys -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase;">Keys Bought</div>
                            <div id="mTotalKeysBought" style="font-size: 18px; font-weight: 800; color: #0f172a; margin-top: 2px;">0</div>
                        </div>
                        <div style="width: 32px; height: 32px; border-radius: 6px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                            <i class="fa-solid fa-key"></i>
                        </div>
                    </div>

                    <!-- 2. Lifetime Spent -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase;">Total Spent</div>
                            <div id="mTotalSpent" style="font-size: 18px; font-weight: 800; color: #dc2626; margin-top: 2px;">₹0.00</div>
                        </div>
                        <div style="width: 32px; height: 32px; border-radius: 6px; background: #fef2f2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </div>
                    </div>

                    <!-- 3. Invites / Referrals -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase;">Invited Users</div>
                            <div style="display: flex; align-items: baseline; gap: 4px; margin-top: 2px;">
                                <span id="mInviteCount" style="font-size: 18px; font-weight: 800; color: #16a34a;">0</span>
                                <span id="mInviteEarned" style="font-size: 11px; font-weight: 600; color: #15803d;">(+₹0)</span>
                            </div>
                        </div>
                        <div style="width: 32px; height: 32px; border-radius: 6px; background: #f0fdf4; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                            <i class="fa-solid fa-user-plus"></i>
                        </div>
                    </div>

                    <!-- 4. Lucky Spin Earnings -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase;">Spin Earnings</div>
                            <div style="display: flex; align-items: baseline; gap: 4px; margin-top: 2px;">
                                <span id="mSpinEarned" style="font-size: 18px; font-weight: 800; color: #9333ea;">₹0.00</span>
                                <span id="mSpinCount" style="font-size: 11px; font-weight: 600; color: #7e22ce;">(0)</span>
                            </div>
                        </div>
                        <div style="width: 32px; height: 32px; border-radius: 6px; background: #faf5ff; color: #9333ea; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                            <i class="fa-solid fa-dharmachakra"></i>
                        </div>
                    </div>
                </div>

                <!-- Custom Reseller Rates Section -->
                <div id="mCustomRatesSection" style="margin-bottom: 24px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                        <h5 style="margin: 0; font-size: 14px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-tags" style="color: #9333ea;"></i>
                            <span>Assigned Custom Reseller Rates</span>
                        </h5>
                        <a href="{{ route('admin.keys.index') }}#vip-pricing" style="font-size: 12px; color: #7c3aed; font-weight: 600; text-decoration: none;">
                            + Manage Rates in Keys & Pricing &rarr;
                        </a>
                    </div>

                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #ffffff;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                            <thead style="background: #faf5ff; color: #6b21a8; font-weight: 600;">
                                <tr>
                                    <th style="padding: 10px 14px;">Product / Tool</th>
                                    <th style="padding: 10px 14px;">Duration</th>
                                    <th style="padding: 10px 14px;">Market Price</th>
                                    <th style="padding: 10px 14px;">Assigned Reseller Rate</th>
                                    <th style="padding: 10px 14px;">Reseller Discount / Margin</th>
                                </tr>
                            </thead>
                            <tbody id="mCustomRatesList">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Keys History Section -->
                <div style="margin-bottom: 24px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                        <h5 style="margin: 0; font-size: 14px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-clock-rotate-left" style="color: #f59e0b;"></i>
                            <span>Purchased Gaming Keys Log</span>
                        </h5>
                        <span style="font-size: 12px; color: #64748b;">(Exact dates & remaining validity)</span>
                    </div>

                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #ffffff;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                            <thead style="background: #f1f5f9; color: #475569; font-weight: 600;">
                                <tr>
                                    <th style="padding: 10px 14px;">Product & Duration</th>
                                    <th style="padding: 10px 14px;">License Key</th>
                                    <th style="padding: 10px 14px;">Purchased Date</th>
                                    <th style="padding: 10px 14px;">Status & Expiry</th>
                                </tr>
                            </thead>
                            <tbody id="mKeysList">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Transactions Log -->
                <div>
                    <h5 style="margin: 0 0 10px 0; font-size: 14px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-receipt" style="color: #3b82f6;"></i>
                        <span>Recent Wallet & Ledger Transactions</span>
                    </h5>

                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #ffffff;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                            <thead style="background: #f1f5f9; color: #475569; font-weight: 600;">
                                <tr>
                                    <th style="padding: 8px 12px;">Type</th>
                                    <th style="padding: 8px 12px;">Amount</th>
                                    <th style="padding: 8px 12px;">Description</th>
                                    <th style="padding: 8px 12px;">Date & Time</th>
                                </tr>
                            </thead>
                            <tbody id="mTxList">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

        <!-- Modal Footer -->
        <div style="padding: 14px 24px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
            <a id="mProfileBtn" href="#" style="font-size: 13px; color: #2563eb; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 6px;">
                <span>View Full Profile & Balance Manager</span> <i class="fa-solid fa-arrow-right"></i>
            </a>
            <button type="button" onclick="closeUserHistoryModal()" style="padding: 8px 16px; background: #e2e8f0; color: #334155; border: none; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer;">
                Close
            </button>
        </div>

    </div>
</div>

<!-- Hidden Forms for Delete Actions -->
<form id="bulk_delete_users_form" action="{{ route('admin.users.bulk-delete') }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<form id="single_delete_user_form" action="" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select_all_users');
    const userCheckboxes = document.querySelectorAll('.user-item-checkbox');
    const bulkDeleteBtn = document.getElementById('btn_bulk_delete_users');
    const selectedCountSpan = document.getElementById('selected_users_count');

    function updateBulkButton() {
        const checked = document.querySelectorAll('.user-item-checkbox:checked');
        const count = checked.length;
        if (selectedCountSpan) selectedCountSpan.textContent = count;
        if (bulkDeleteBtn) {
            bulkDeleteBtn.style.display = count > 0 ? 'inline-flex' : 'none';
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            userCheckboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkButton();
        });
    }

    userCheckboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            if (!this.checked && selectAll) selectAll.checked = false;
            updateBulkButton();
        });
    });
});

window.submitBulkDeleteUsers = function() {
    const checked = document.querySelectorAll('.user-item-checkbox:checked');
    if (checked.length === 0) {
        if (window.showToast) {
            window.showToast('Please select at least one customer to delete.', 'info');
        } else {
            alert('Please select at least one customer to delete.');
        }
        return;
    }

    if (window.confirmAction) {
        window.confirmAction({
            title: 'Delete Selected Users?',
            text: `Are you sure you want to permanently delete ${checked.length} selected customer(s)? This action cannot be undone.`,
            icon: 'warning',
            confirmText: `Yes, Delete (${checked.length})`,
            confirmColor: '#dc2626',
            onConfirm: () => {
                executeBulkDeleteSubmit(checked);
            }
        });
    } else {
        if (confirm(`Are you sure you want to delete ${checked.length} selected user(s)?`)) {
            executeBulkDeleteSubmit(checked);
        }
    }
};

function executeBulkDeleteSubmit(checked) {
    let form = document.getElementById('bulk_delete_users_form');
    if (!form) {
        form = document.createElement('form');
        form.id = 'bulk_delete_users_form';
        form.action = '{{ route("admin.users.bulk-delete") }}';
        form.method = 'POST';
        form.style.display = 'none';
        document.body.appendChild(form);
    }

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '{{ csrf_token() }}';

    form.innerHTML = `
        <input type="hidden" name="_token" value="${csrfToken}">
        <input type="hidden" name="_method" value="DELETE">
    `;
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = cb.value;
        form.appendChild(input);
    });
    form.submit();
}

window.deleteSingleUser = function(id, name) {
    if (window.confirmAction) {
        window.confirmAction({
            title: 'Delete User?',
            text: `Are you sure you want to permanently delete "${name}" (ID: ${id})?`,
            icon: 'warning',
            confirmText: 'Yes, Delete',
            confirmColor: '#dc2626',
            onConfirm: () => {
                executeSingleDeleteSubmit(id);
            }
        });
    } else {
        if (confirm(`Are you sure you want to delete "${name}" (ID: ${id})?`)) {
            executeSingleDeleteSubmit(id);
        }
    }
};

function executeSingleDeleteSubmit(id) {
    let form = document.getElementById('single_delete_user_form');
    if (!form) {
        form = document.createElement('form');
        form.id = 'single_delete_user_form';
        form.method = 'POST';
        form.style.display = 'none';
        document.body.appendChild(form);
    }
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '{{ csrf_token() }}';

    form.action = '/admin/users/' + id;
    form.innerHTML = `
        <input type="hidden" name="_token" value="${csrfToken}">
        <input type="hidden" name="_method" value="DELETE">
    `;
    form.submit();
}

// User History Info Modal Handler
function openUserHistoryModal(userId) {
    const modal = document.getElementById('userHistoryModal');
    const loading = document.getElementById('mLoading');
    const content = document.getElementById('mContent');

    modal.style.display = 'flex';
    loading.style.display = 'flex';
    content.style.display = 'none';

    fetch(`/admin/users/${userId}/history-api`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert('Could not load user data.');
                closeUserHistoryModal();
                return;
            }

            const u = data.user;
            document.getElementById('mUserAvatar').textContent = u.name ? u.name.charAt(0).toUpperCase() : 'U';
            document.getElementById('mUserName').textContent = u.name;
            document.getElementById('mUserMeta').textContent = `${u.username} • Telegram ID: ${u.telegram_id}`;
            document.getElementById('mUserBalance').textContent = `₹${u.balance}`;
            document.getElementById('mTotalKeysBought').textContent = u.total_keys;
            document.getElementById('mTotalSpent').textContent = `₹${u.total_spent}`;
            document.getElementById('mInviteCount').textContent = u.invite_count;
            document.getElementById('mInviteEarned').textContent = `(+₹${u.invite_earnings})`;
            document.getElementById('mSpinEarned').textContent = `₹${u.spin_earnings}`;
            document.getElementById('mSpinCount').textContent = `(${u.total_spins} spins)`;
            document.getElementById('mProfileBtn').href = u.profile_url;

            // Render Custom Rates
            const customRatesSection = document.getElementById('mCustomRatesSection');
            const customRatesList = document.getElementById('mCustomRatesList');
            customRatesList.innerHTML = '';
            if (data.custom_prices && data.custom_prices.length > 0) {
                customRatesSection.style.display = 'block';
                data.custom_prices.forEach(cp => {
                    customRatesList.innerHTML += `
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 10px 14px; font-weight: 700; color: #0f172a;">
                                <i class="fa-solid fa-gamepad" style="color: #6366f1; margin-right: 6px;"></i> ${cp.product_name}
                            </td>
                            <td style="padding: 10px 14px;">
                                <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; background: #eef2ff; color: #4f46e5;">${cp.duration}</span>
                            </td>
                            <td style="padding: 10px 14px; color: #64748b; text-decoration: line-through; font-size: 12px;">
                                ₹${cp.base_price}
                            </td>
                            <td style="padding: 10px 14px; font-weight: 800; color: #059669; font-size: 13px;">
                                ₹${cp.custom_price}
                            </td>
                            <td style="padding: 10px 14px;">
                                <span style="display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #f3e8ff; color: #7e22ce;">
                                    Save ₹${cp.discount} (${cp.discount_percent}% Margin)
                                </span>
                            </td>
                        </tr>
                    `;
                });
            } else {
                customRatesSection.style.display = 'none';
            }

            // Render Keys
            const keysList = document.getElementById('mKeysList');
            keysList.innerHTML = '';
            if (data.keys.length === 0) {
                keysList.innerHTML = `<tr><td colspan="4" style="text-align: center; color: #94a3b8; padding: 18px;">No keys purchased by this user yet.</td></tr>`;
            } else {
                data.keys.forEach(k => {
                    const statusBadge = k.is_expired 
                        ? `<span style="display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #fee2e2; color: #dc2626;">❌ Expired</span>`
                        : `<span style="display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #ecfdf5; color: #059669;">🟢 Active (${k.time_remaining})</span>`;

                    keysList.innerHTML += `
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 10px 14px; font-weight: 600; color: #0f172a;">
                                ${k.product}
                                <span style="font-size: 11px; font-weight: 700; color: #6366f1; background: #eef2ff; padding: 2px 6px; border-radius: 4px; margin-left: 4px;">${k.duration}</span>
                            </td>
                            <td style="padding: 10px 14px;">
                                <code style="background: #f1f5f9; padding: 3px 6px; border-radius: 4px; font-family: monospace; font-size: 11px; color: #0f172a; border: 1px solid #e2e8f0;">${k.license_key}</code>
                            </td>
                            <td style="padding: 10px 14px; color: #64748b; font-size: 11px;">
                                <i class="fa-regular fa-calendar" style="color: #94a3b8; margin-right: 4px;"></i> ${k.sold_at}
                            </td>
                            <td style="padding: 10px 14px;">
                                ${statusBadge}
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Exp: ${k.expires_at}</div>
                            </td>
                        </tr>
                    `;
                });
            }

            // Render Transactions
            const txList = document.getElementById('mTxList');
            txList.innerHTML = '';
            if (data.transactions.length === 0) {
                txList.innerHTML = `<tr><td colspan="4" style="text-align: center; color: #94a3b8; padding: 18px;">No transaction logs available.</td></tr>`;
            } else {
                data.transactions.forEach(t => {
                    const isDebit = t.type === 'DEBIT';
                    const badge = `<span style="padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700; background: ${isDebit ? '#fee2e2' : '#ecfdf5'}; color: ${isDebit ? '#dc2626' : '#059669'};">${t.type}</span>`;
                    const amtColor = isDebit ? '#dc2626' : '#059669';
                    const amtSign = isDebit ? '-' : '+';

                    txList.innerHTML += `
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 8px 12px;">${badge}</td>
                            <td style="padding: 8px 12px; font-weight: 700; color: ${amtColor};">${amtSign}₹${t.amount}</td>
                            <td style="padding: 8px 12px; color: #475569; font-size: 11px;">${t.description}</td>
                            <td style="padding: 8px 12px; color: #64748b; font-size: 11px;">${t.date}</td>
                        </tr>
                    `;
                });
            }

            loading.style.display = 'none';
            content.style.display = 'block';
        })
        .catch(err => {
            alert('Failed to load user history: ' + err.message);
            closeUserHistoryModal();
        });
}

function closeUserHistoryModal() {
    const modal = document.getElementById('userHistoryModal');
    if (modal) modal.style.display = 'none';
}

function openAddResellerModal() {
    const modal = document.getElementById('addResellerModal');
    if (modal) modal.style.display = 'flex';
}

function closeAddResellerModal() {
    const modal = document.getElementById('addResellerModal');
    if (modal) modal.style.display = 'none';
}

function openAdjustBalanceModal(userId, userName, currentBal) {
    const modal = document.getElementById('adjustBalanceModal');
    const userSelectGroup = document.getElementById('adj_user_select_group');
    const userFixedGroup = document.getElementById('adj_user_fixed_group');
    const fixedUserName = document.getElementById('adj_fixed_user_name');
    const fixedUserBal = document.getElementById('adj_fixed_user_bal');
    const userIdInput = document.getElementById('adj_user_id');
    const form = document.getElementById('adjustBalanceForm');

    if (userId) {
        userSelectGroup.style.display = 'none';
        userFixedGroup.style.display = 'block';
        fixedUserName.innerText = userName;
        fixedUserBal.innerText = '₹' + parseFloat(currentBal).toFixed(2);
        userIdInput.value = userId;
        form.action = '/admin/users/' + userId + '/adjust-balance';
    } else {
        userSelectGroup.style.display = 'block';
        userFixedGroup.style.display = 'none';
        userIdInput.value = '';
        form.action = '{{ route('admin.users.adjust-balance', 0) }}';
    }

    if (modal) modal.style.display = 'flex';
}

function closeAdjustBalanceModal() {
    const modal = document.getElementById('adjustBalanceModal');
    if (modal) modal.style.display = 'none';
}

function updateSelectUserBal(selectEl) {
    const selectedOption = selectEl.options[selectEl.selectedIndex];
    const bal = selectedOption.getAttribute('data-bal') || '0.00';
    document.getElementById('adj_select_bal_preview').innerText = 'Current: ₹' + parseFloat(bal).toFixed(2);
    document.getElementById('adj_user_id').value = selectEl.value;
}
</script>

<!-- Add Reseller Modal -->
<div id="addResellerModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); z-index: 2100; align-items: center; justify-content: center; padding: 20px;" onclick="if(event.target === this) closeAddResellerModal()">
    <div style="background: #ffffff; border-radius: 8px; width: 100%; max-width: 480px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15); overflow: hidden; border: 1px solid #e2e8f0;">
        <form action="{{ route('admin.resellers.add') }}" method="POST">
            @csrf
            
            <div style="padding: 16px 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; background: #faf5ff;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 6px; background: #7c3aed; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <h4 style="margin: 0; font-size: 15px; color: #0f172a; font-weight: 800;">Add New Reseller</h4>
                </div>
                <button type="button" onclick="closeAddResellerModal()" style="background: none; border: none; font-size: 15px; color: #64748b; cursor: pointer;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div style="padding: 20px;">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                        Telegram ID or Username (@handle):
                    </label>
                    <input type="text" name="telegram_identifier" required placeholder="e.g. 12345678 or @username" class="form-control" style="width: 100%; padding: 10px 12px; font-size: 13px; border: 1.5px solid #cbd5e1; border-radius: 6px; outline: none;">
                </div>
            </div>

            <div style="padding: 14px 20px; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: flex-end; gap: 8px; background: #f8fafc;">
                <button type="button" onclick="closeAddResellerModal()" style="padding: 8px 16px; background: #e2e8f0; color: #475569; border: none; border-radius: 6px; font-weight: 700; font-size: 12.5px; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" style="padding: 8px 18px; background: #7c3aed; color: #ffffff; border: none; border-radius: 6px; font-weight: 700; font-size: 12.5px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-check"></i> Add as Reseller
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Adjust Balance / Top-Up Modal -->
<div id="adjustBalanceModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); z-index: 2200; align-items: center; justify-content: center; padding: 20px;" onclick="if(event.target === this) closeAdjustBalanceModal()">
    <div style="background: #ffffff; border-radius: 8px; width: 100%; max-width: 520px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15); overflow: hidden; border: 1px solid #e2e8f0;">
        <form id="adjustBalanceForm" action="{{ route('admin.users.adjust-balance', 0) }}" method="POST">
            @csrf
            <input type="hidden" id="adj_user_id" name="telegram_user_id" value="">
            
            <div style="padding: 16px 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; background: #ecfdf5;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 6px; background: #10b981; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <h4 style="margin: 0; font-size: 15.5px; color: #065f46; font-weight: 800;">Wallet Top-Up / Adjust Balance</h4>
                </div>
                <button type="button" onclick="closeAdjustBalanceModal()" style="background: none; border: none; font-size: 15px; color: #047857; cursor: pointer;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div style="padding: 22px;">
                <!-- Fixed User View (when clicked from row) -->
                <div id="adj_user_fixed_group" style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 10.5px; font-weight: 700; color: #64748b; text-transform: uppercase;">Customer / Reseller</div>
                            <div id="adj_fixed_user_name" style="font-size: 14.5px; font-weight: 800; color: #0f172a;">User</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 10.5px; font-weight: 700; color: #64748b; text-transform: uppercase;">Current Balance</div>
                            <div id="adj_fixed_user_bal" style="font-size: 14.5px; font-weight: 800; color: #10b981;">₹0.00</div>
                        </div>
                    </div>
                </div>

                <!-- Select User View (when opened globally) -->
                <div id="adj_user_select_group" style="display: none; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                        <label style="font-size: 12.5px; font-weight: 700; color: #334155;">
                            Select User / Friend:
                        </label>
                        <span id="adj_select_bal_preview" style="font-size: 12px; font-weight: 700; color: #10b981;"></span>
                    </div>
                    <select name="telegram_user_id" class="form-control" onchange="updateSelectUserBal(this)" style="width: 100%; padding: 10px 12px; font-size: 13.5px; border: 1.5px solid #cbd5e1; border-radius: 6px; outline: none; background: #ffffff;">
                        <option value="">-- Choose User / Reseller --</option>
                        @if(isset($allUsers))
                            @foreach($allUsers as $u)
                                <option value="{{ $u->id }}" data-bal="{{ $u->balance }}">
                                    {{ $u->first_name ?: 'No Name' }} ({{ $u->username ? '@' . $u->username : 'ID: ' . $u->telegram_id }}) — ₹{{ number_format($u->balance, 2) }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Action Type (Credit / Debit) -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                        Action:
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <label style="border: 1.5px solid #a7f3d0; background: #ecfdf5; border-radius: 6px; padding: 10px 12px; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" name="type" value="credit" checked style="accent-color: #10b981; width: 16px; height: 16px;">
                            <span style="font-weight: 800; font-size: 13px; color: #065f46;">➕ Add Money</span>
                        </label>

                        <label style="border: 1.5px solid #fecaca; background: #fef2f2; border-radius: 6px; padding: 10px 12px; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" name="type" value="debit" style="accent-color: #ef4444; width: 16px; height: 16px;">
                            <span style="font-weight: 800; font-size: 13px; color: #991b1b;">➖ Deduct Money</span>
                        </label>
                    </div>
                </div>

                <!-- Amount -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px;">
                        Amount (₹):
                    </label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 12px; top: 10px; font-weight: 800; color: #64748b; font-size: 15px;">₹</span>
                        <input type="number" step="0.01" min="1" name="amount" required placeholder="500" class="form-control" style="width: 100%; padding: 10px 12px 10px 30px; font-size: 15px; font-weight: 800; border: 1.5px solid #cbd5e1; border-radius: 6px; outline: none;">
                    </div>
                </div>

                <!-- Note / Description -->
                <div style="margin-bottom: 8px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px;">
                        Note / Payment Reason:
                    </label>
                    <input type="text" name="description" placeholder="e.g. WhatsApp UPI / Friend Top-Up" class="form-control" style="width: 100%; padding: 9px 12px; font-size: 13px; border: 1.5px solid #cbd5e1; border-radius: 6px; outline: none;">
                </div>
            </div>

            <div style="padding: 14px 20px; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: flex-end; gap: 8px; background: #f8fafc;">
                <button type="button" onclick="closeAdjustBalanceModal()" style="padding: 8px 16px; background: #e2e8f0; color: #475569; border: none; border-radius: 6px; font-weight: 700; font-size: 12.5px; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" style="padding: 8px 20px; background: #10b981; color: #ffffff; border: none; border-radius: 6px; font-weight: 800; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-check"></i> Apply Balance
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
