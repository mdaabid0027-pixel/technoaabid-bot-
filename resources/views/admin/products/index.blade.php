@extends('admin.layout')

@section('title', 'Stock & Key Management')
@section('header_title', 'Stock & License Key Management')

@section('styles')
<style>
    /* Products Stock Grid */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 28px;
    }

    .prod-stock-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .prod-stock-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
    }

    .prod-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
    }

    .duration-chips-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-bottom: 16px;
    }

    .dur-chip {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 6px 8px;
        text-align: center;
    }

    .dur-chip-label {
        font-size: 10px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
    }

    .dur-chip-count {
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
    }

    /* Inventory Section */
    .inventory-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
    }

    .filter-bar {
        padding: 16px 20px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .filter-controls {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-select {
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        outline: none;
    }

    /* Bulk Actions Bar */
    .bulk-actions-bar {
        padding: 12px 20px;
        background: #eff6ff;
        border-bottom: 1px solid #bfdbfe;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
    }

    .custom-table th {
        background: #f8fafc;
        padding: 12px 16px;
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
    }

    .custom-table td {
        padding: 14px 16px;
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

    .btn-delete-single {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-delete-single:hover {
        background: #dc2626;
        color: #ffffff;
    }

    /* Modal Styling */
    .modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 200;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-backdrop.active {
        display: flex;
    }

    .modal-box {
        background: #ffffff;
        border-radius: 16px;
        width: 100%;
        max-width: 520px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .modal-header {
        padding: 16px 20px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
</style>
@endsection

@section('content')

<!-- 1. Top Products Stock Overview Cards -->
<div class="product-grid">
    @foreach($products as $prod)
        <div class="prod-stock-card">
            <div>
                <div class="prod-header">
                    <div>
                        <div style="font-weight: 800; font-size: 15px; color: #0f172a;">{{ $prod->name }}</div>
                        <div style="font-size: 11px; color: #64748b;">{{ $prod->game_name ?? 'Carrom Pool' }}</div>
                    </div>
                    <span style="font-size: 13px; font-weight: 800; padding: 4px 10px; border-radius: 9999px; background: {{ $prod->total_available_count > 0 ? '#ecfdf5' : '#fef2f2' }}; color: {{ $prod->total_available_count > 0 ? '#059669' : '#dc2626' }};">
                        {{ $prod->total_available_count }} Available
                    </span>
                </div>

                <!-- Duration Breakdown -->
                <div class="duration-chips-grid">
                    <div class="dur-chip">
                        <div class="dur-chip-label">1 Day</div>
                        <div class="dur-chip-count">{{ $prod->keys_1d_count ?? 0 }}</div>
                    </div>
                    <div class="dur-chip">
                        <div class="dur-chip-label">3 Days</div>
                        <div class="dur-chip-count">{{ $prod->keys_3d_count ?? 0 }}</div>
                    </div>
                    <div class="dur-chip">
                        <div class="dur-chip-label">7 Days</div>
                        <div class="dur-chip-count">{{ $prod->keys_7d_count ?? 0 }}</div>
                    </div>
                    <div class="dur-chip">
                        <div class="dur-chip-label">10 Days</div>
                        <div class="dur-chip-count">{{ $prod->keys_10d_count ?? 0 }}</div>
                    </div>
                    <div class="dur-chip">
                        <div class="dur-chip-label">15/30d</div>
                        <div class="dur-chip-count">{{ ($prod->keys_15d_count ?? 0) + ($prod->keys_30d_count ?? 0) }}</div>
                    </div>
                    <div class="dur-chip">
                        <div class="dur-chip-label">90 Days</div>
                        <div class="dur-chip-count">{{ $prod->keys_90d_count ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <button type="button" class="btn-import-quick" onclick="openImportModal('{{ $prod->id }}', '{{ $prod->name }}')" style="width: 100%; padding: 10px; background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <i class="fa-solid fa-plus-circle"></i> + Add Stock Keys
            </button>
        </div>
    @endforeach
</div>

<!-- 2. Granular Key Inventory & Bulk Actions Section -->
<div class="inventory-card">
    
    <!-- Filter Bar -->
    <div class="filter-bar">
        <div>
            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-key" style="color: #2563eb;"></i>
                <span>Key Inventory ({{ $keys->total() }})</span>
            </h3>
        </div>

        <form action="{{ route('admin.products.index') }}" method="GET" class="filter-controls" style="margin: 0;">
            <!-- Filter Product -->
            <select name="product_id" class="filter-select" onchange="this.form.submit()">
                <option value="">All Products</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ $productId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>

            <!-- Filter Duration -->
            <select name="duration" class="filter-select" onchange="this.form.submit()">
                <option value="">All Durations</option>
                <option value="1d" {{ $duration === '1d' ? 'selected' : '' }}>1 Day</option>
                <option value="3d" {{ $duration === '3d' ? 'selected' : '' }}>3 Days</option>
                <option value="7d" {{ $duration === '7d' ? 'selected' : '' }}>7 Days</option>
                <option value="10d" {{ $duration === '10d' ? 'selected' : '' }}>10 Days</option>
                <option value="15d" {{ $duration === '15d' ? 'selected' : '' }}>15 Days</option>
                <option value="30d" {{ $duration === '30d' ? 'selected' : '' }}>30 Days</option>
                <option value="90d" {{ $duration === '90d' ? 'selected' : '' }}>90 Days</option>
            </select>

            <!-- Filter Status -->
            <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="available" {{ $status === 'available' ? 'selected' : '' }}>🟢 Available</option>
                <option value="sold" {{ $status === 'sold' ? 'selected' : '' }}>🔴 Sold</option>
            </select>

            <!-- Search by Key -->
            <input type="text" name="search_key" placeholder="Search key string..." value="{{ $searchKey }}" class="filter-select" style="width: 180px;">

            <button type="submit" style="padding: 8px 14px; background: #2563eb; color: #ffffff; border: none; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer;">
                <i class="fa-solid fa-filter"></i> Filter
            </button>

            @if($productId || $duration || $status || $searchKey)
                <a href="{{ route('admin.products.index') }}" style="color: #64748b; font-size: 13px; text-decoration: none; font-weight: 600;">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Bulk Actions Toolbar -->
    <form id="bulkDeleteForm" action="{{ route('admin.keys.bulk-delete') }}" method="POST" onsubmit="return confirmBulkDelete();">
        @csrf
        
        <div class="bulk-actions-bar" id="bulkToolbar">
            <div style="display: flex; align-items: center; gap: 12px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #1e3a8a; cursor: pointer;">
                    <input type="checkbox" id="selectAllCheckbox" style="width: 16px; height: 16px; cursor: pointer;">
                    <span>Select All on Page</span>
                </label>
                <span id="selectedCountBadge" style="display: none; padding: 2px 8px; background: #2563eb; color: #ffffff; border-radius: 9999px; font-size: 11px; font-weight: 800;">
                    0 Selected
                </span>
            </div>

            <div>
                <button type="submit" id="btnBulkDelete" style="padding: 6px 14px; background: #dc2626; color: #ffffff; border: none; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: none; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-trash-can"></i> Delete Selected Keys
                </button>
            </div>
        </div>

        @if($keys->isEmpty())
            <div style="padding: 60px 20px; text-align: center; color: #94a3b8;">
                <i class="fa-solid fa-key" style="font-size: 36px; color: #cbd5e1; margin-bottom: 8px;"></i>
                <div style="font-weight: 600; font-size: 15px;">No Keys Found in Stock</div>
                <div style="font-size: 13px; color: #94a3b8; margin-top: 4px;">Try changing filters or import new keys above.</div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>License Key</th>
                            <th>Product / Game</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Purchased By</th>
                            <th>Added On</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($keys as $key)
                            <tr>
                            <td>
                                <input type="checkbox" name="key_ids[]" value="{{ $key->id }}" class="key-checkbox" style="width: 16px; height: 16px; cursor: pointer;">
                            </td>
                            <td>
                                <div class="key-badge">
                                    <span>{{ $key->license_key }}</span>
                                    <button type="button" class="copy-btn" onclick="navigator.clipboard.writeText('{{ $key->license_key }}'); window.showToast('Key copied to clipboard!', 'success');" title="Copy Key" style="background: none; border: none; cursor: pointer; color: #64748b;">
                                        <i class="fa-regular fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #0f172a;">{{ $key->product->name ?? 'Product' }}</div>
                                <div style="font-size: 11px; color: #64748b;">{{ $key->product->game_name ?? 'AutoPlay' }}</div>
                            </td>
                            <td>
                                <span style="padding: 3px 8px; background: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb; border-radius: 6px; font-weight: 700; font-size: 11px;">
                                    {{ strtoupper($key->duration) }}
                                </span>
                            </td>
                            <td>
                                @if($key->is_sold)
                                    <span style="padding: 3px 8px; background: #fee2e2; border: 1px solid #fecaca; color: #dc2626; border-radius: 6px; font-weight: 700; font-size: 11px;">
                                        SOLD
                                    </span>
                                @else
                                    <span style="padding: 3px 8px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #059669; border-radius: 6px; font-weight: 700; font-size: 11px;">
                                        AVAILABLE
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($key->telegramUser)
                                    <a href="{{ route('admin.users.show', $key->telegramUser->id) }}" style="color: #2563eb; text-decoration: none; font-weight: 600; font-size: 12px;">
                                        {{ $key->telegramUser->first_name ?: '@' . $key->telegramUser->username }}
                                    </a>
                                @else
                                    <span style="color: #94a3b8; font-size: 12px;">—</span>
                                @endif
                            </td>
                            <td style="font-size: 12px; color: #64748b;">
                                {{ $key->created_at->format('d M Y') }}
                            </td>
                            <td style="text-align: right;">
                                <button type="button" class="btn" onclick="deleteSingleKey({{ $key->id }})" style="background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; padding: 4px 8px; border-radius: 6px; font-size: 12px;" title="Delete Key">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="padding: 16px 24px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-size: 13px; color: #64748b;">
                Showing {{ $keys->firstItem() }} to {{ $keys->lastItem() }} of {{ $keys->total() }} keys
            </div>
            <div>
                {{ $keys->links() }}
            </div>
        </div>
    @endif
</div>

<!-- Hidden Single Delete Form -->
<form id="singleDeleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Hidden Bulk Delete Form -->
<form id="bulkDeleteForm" action="{{ route('admin.keys.bulk-delete') }}" method="POST" style="display: none;">
    @csrf
</form>

<!-- 3. Bulk Key Import Modal -->
<div class="modal-backdrop" id="importModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a;" id="importModalTitle">Import Stock Keys</h3>
            <button type="button" onclick="closeImportModal()" style="background: none; border: none; font-size: 18px; color: #94a3b8; cursor: pointer;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="importForm" method="POST" style="padding: 20px;">
            @csrf
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px;">Select Duration</label>
                <select name="duration" class="filter-select" style="width: 100%;" required>
                    <option value="1d">1 Day</option>
                    <option value="3d">3 Days</option>
                    <option value="7d">7 Days</option>
                    <option value="10d">10 Days</option>
                    <option value="15d">15 Days</option>
                    <option value="30d">30 Days</option>
                    <option value="90d">90 Days</option>
                    <option value="lifetime">Lifetime</option>
                </select>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px;">License Keys (1 key per line)</label>
                <textarea name="keys" rows="8" placeholder="Paste your keys here line by line..." class="filter-select" style="width: 100%; font-family: monospace; font-size: 12px; resize: vertical;" required></textarea>
            </div>

            <button type="submit" style="width: 100%; padding: 12px; background: #2563eb; color: #ffffff; border: none; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer;">
                <i class="fa-solid fa-cloud-arrow-up"></i> Import Keys into Stock
            </button>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Checkbox Multi-selection & Bulk Delete Handler
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const keyCheckboxes = document.querySelectorAll('.key-checkbox');
    const btnBulkDelete = document.getElementById('btnBulkDelete');
    const selectedCountBadge = document.getElementById('selectedCountBadge');

    function updateSelectedState() {
        const checked = document.querySelectorAll('.key-checkbox:checked');
        const count = checked.length;

        if (count > 0) {
            btnBulkDelete.style.display = 'inline-flex';
            selectedCountBadge.style.display = 'inline-block';
            selectedCountBadge.textContent = count + ' Selected';
        } else {
            btnBulkDelete.style.display = 'none';
            selectedCountBadge.style.display = 'none';
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            keyCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
            updateSelectedState();
        });
    }

    keyCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedState);
    });

    window.confirmBulkDelete = function() {
        const count = document.querySelectorAll('.key-checkbox:checked').length;
        if (count === 0) {
            window.showToast('Please select keys to delete.', 'info');
            return false;
        }

        window.confirmAction({
            title: 'Delete Selected Keys?',
            text: `Are you sure you want to permanently delete ${count} selected license keys?`,
            icon: 'warning',
            confirmText: `Yes, Delete (${count})`,
            confirmColor: '#ef4444',
            onConfirm: () => {
                const bulkForm = document.getElementById('bulkDeleteForm');
                bulkForm.innerHTML = `@csrf`;
                document.querySelectorAll('.key-checkbox:checked').forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = cb.value;
                    bulkForm.appendChild(input);
                });
                bulkForm.submit();
            }
        });
        return false;
    };

    window.deleteSingleKey = function(keyId) {
        window.confirmAction({
            title: 'Delete Key?',
            text: 'Are you sure you want to permanently delete this license key?',
            icon: 'warning',
            confirmText: 'Yes, Delete',
            confirmColor: '#ef4444',
            onConfirm: () => {
                const form = document.getElementById('singleDeleteForm');
                form.action = '/admin/keys/' + keyId;
                form.submit();
            }
        });
    };

    // Modal Import Helpers
    function openImportModal(productId, productName) {
        const modal = document.getElementById('importModal');
        const form = document.getElementById('importForm');
        const title = document.getElementById('importModalTitle');

        title.textContent = 'Import Stock Keys for ' + productName;
        form.action = '/admin/products/' + productId + '/add-keys';
        modal.classList.add('active');
    }

    function closeImportModal() {
        document.getElementById('importModal').classList.remove('active');
    }
</script>
@endsection
