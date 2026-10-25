@extends('admin.layout')

@section('title', 'Rewards & Claims Audit')
@section('header_title', 'Rewards & Claims Audit')

@section('styles')
<style>
    .rewards-card {
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

    .btn-bulk-clear {
        display: none;
        align-items: center;
        gap: 6px;
        background: #ef4444;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-bulk-clear:hover {
        background: #dc2626;
    }

    .audit-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .audit-table th {
        background: #f8fafc;
        padding: 12px 16px;
        font-weight: 700;
        color: #475569;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
    }

    .audit-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    .audit-table tr:hover {
        background: #fafafa;
    }

    .btn-delete-row {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fca5a5;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s;
    }

    .btn-delete-row:hover {
        background: #dc2626;
        color: #ffffff;
    }
</style>
@endsection

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">

    <!-- Back to Dashboard / Header stats -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('admin.dashboard') }}" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; background: #ffffff; border: 1px solid #cbd5e1; color: #475569; text-decoration: none;">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 0;">
                    🎁 Rewards & Claims Audit Log
                </h2>
                <p style="font-size: 13px; color: #64748b; margin: 2px 0 0 0;">
                    Complete historical log of Lucky Wheel Spins & Referral Bonuses.
                </p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 16px; font-size: 13px;">
                Lifetime Claims: <strong style="color: #e11d48;">₹{{ number_format($lifetimeClaimsDisbursed, 2) }}</strong>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="rewards-card">
        <div class="top-controls">
            <!-- Search Form -->
            <form action="{{ route('admin.rewards-audit.index') }}" method="GET" style="display: flex; gap: 10px; align-items: center;">
                <div class="search-input-box">
                    <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search customer name, @username, ID...">
                </div>
                <button type="submit" style="background: #0f172a; color: #ffffff; border: none; border-radius: 10px; padding: 8px 16px; font-weight: 600; font-size: 13px; cursor: pointer;">
                    Filter
                </button>
                @if($search)
                    <a href="{{ route('admin.rewards-audit.index') }}" style="color: #64748b; font-size: 13px; text-decoration: none; padding: 6px 10px;">Clear</a>
                @endif
            </form>

            <!-- Bulk Delete Button -->
            <button type="button" class="btn-bulk-clear" id="btnBulkClear" onclick="submitBulkClearAudit()">
                <i class="fa-solid fa-trash-can"></i> Clear Selected (<span id="selectedCount">0</span>)
            </button>
        </div>

        @if($claimers->isEmpty())
            <div style="padding: 40px; text-align: center; color: #64748b; font-size: 14px;">
                <i class="fa-solid fa-gift" style="font-size: 32px; color: #cbd5e1; margin-bottom: 12px; display: block;"></i>
                No reward claims found in this view.
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="selectAllCheck" onchange="toggleSelectAll(this)" style="cursor: pointer; width: 16px; height: 16px;">
                            </th>
                            <th>Customer</th>
                            <th>Spin Wins</th>
                            <th>Referral Bonus</th>
                            <th>Total Claimed</th>
                            <th>Wallet Balance</th>
                            <th>Last Claimed Date</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($claimers as $user)
                            @php
                                $spinAmt = (float) ($user->spin_claims_total ?? 0);
                                $refAmt = (float) ($user->referral_claims_total ?? 0);
                                $totClaim = $spinAmt + $refAmt;
                                $lastDate = $user->last_claim_date ? \Carbon\Carbon::parse($user->last_claim_date)->format('d M Y, h:i A') : 'N/A';
                                $lastAgo = $user->last_claim_date ? \Carbon\Carbon::parse($user->last_claim_date)->diffForHumans() : '';
                            @endphp
                            <tr id="row-audit-{{ $user->id }}">
                                <td style="text-align: center;">
                                    <input type="checkbox" class="audit-check" value="{{ $user->id }}" onchange="updateSelectedCount()" style="cursor: pointer; width: 16px; height: 16px;">
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: #0f172a;">{{ $user->first_name ?: 'User #' . $user->id }}</div>
                                    <div style="font-size: 11px; color: #64748b;">
                                        {{ $user->username ? '@' . $user->username : 'ID: ' . $user->telegram_id }}
                                    </div>
                                </td>
                                <td style="color: #d97706; font-weight: 700;">
                                    ₹{{ number_format($spinAmt, 2) }}
                                </td>
                                <td style="color: #7c3aed; font-weight: 700;">
                                    ₹{{ number_format($refAmt, 2) }}
                                </td>
                                <td style="font-weight: 800; color: #e11d48;">
                                    ₹{{ number_format($totClaim, 2) }}
                                </td>
                                <td style="font-weight: 700; color: #059669;">
                                    ₹{{ number_format($user->balance, 2) }}
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #1e293b;">{{ $lastDate }}</div>
                                    @if($lastAgo)
                                        <div style="font-size: 10.5px; color: #94a3b8;">{{ $lastAgo }}</div>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <button type="button" class="btn-delete-row" onclick="deleteSingleAudit({{ $user->id }}, '{{ addslashes($user->first_name) }}')">
                                        <i class="fa-solid fa-trash-can"></i> Clear Log
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div style="padding: 16px 24px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 13px; color: #64748b;">
                    Showing {{ $claimers->firstItem() ?? 0 }} to {{ $claimers->lastItem() ?? 0 }} of {{ $claimers->total() }} records
                </div>
                <div>
                    {{ $claimers->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.audit-check:checked');
        const count = checked.length;
        const btn = document.getElementById('btnBulkClear');
        const countSpan = document.getElementById('selectedCount');
        
        if (countSpan) countSpan.innerText = count;
        if (btn) {
            btn.style.display = count > 0 ? 'inline-flex' : 'none';
        }
    }

    function toggleSelectAll(master) {
        const checkboxes = document.querySelectorAll('.audit-check');
        checkboxes.forEach(cb => cb.checked = master.checked);
        updateSelectedCount();
    }

    function deleteSingleAudit(userId, userName) {
        Swal.fire({
            title: 'Clear Audit Record?',
            text: `Are you sure you want to clear the reward claim log for ${userName}? (Customer's current wallet balance will remain untouched).`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Clear Record'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/rewards-audit/${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Cleared!', data.message, 'success').then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Could not clear record', 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'Server connection error', 'error');
                });
            }
        });
    }

    function submitBulkClearAudit() {
        const checked = document.querySelectorAll('.audit-check:checked');
        const userIds = Array.from(checked).map(cb => cb.value);

        if (userIds.length === 0) return;

        Swal.fire({
            title: 'Clear Selected Audit Logs?',
            text: `Are you sure you want to clear ${userIds.length} selected claim log(s)? (Customer wallet balances will remain untouched).`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: `Yes, Clear ${userIds.length} Logs`
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('{{ route("admin.rewards-audit.bulk-clear") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ user_ids: userIds })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Cleared!', data.message, 'success').then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Could not clear records', 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'Server connection error', 'error');
                });
            }
        });
    }
</script>
@endsection
