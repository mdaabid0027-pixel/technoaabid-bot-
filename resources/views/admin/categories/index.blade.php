@extends('admin.layout')

@section('title', 'Categories Management')
@section('header_title', 'Product & Store Categories')

@section('styles')
<style>
    .cat-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .cat-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .cat-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .icon-blue { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
    .icon-emerald { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .icon-amber { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
    .icon-purple { background: #faf5ff; color: #9333ea; border: 1px solid #e9d5ff; }

    .category-split {
        display: grid;
        grid-template-columns: 1.7fr 1fr;
        gap: 24px;
    }

    @media (max-width: 1024px) {
        .category-split {
            grid-template-columns: 1fr;
        }
    }

    .card-modern {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
    }

    .card-header-bar {
        padding: 18px 24px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Category Item Card */
    .cat-item-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        margin-bottom: 14px;
        background: #ffffff;
        transition: all 0.2s ease;
        overflow: hidden;
    }

    .cat-item-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 6px 12px -2px rgba(0, 0, 0, 0.05);
    }

    .cat-item-header {
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #ffffff;
        cursor: pointer;
        user-select: none;
    }

    .cat-item-header:hover {
        background: #f8fafc;
    }

    .cat-avatar {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: 700;
    }

    .avatar-autoplay { background: linear-gradient(135deg, #2563eb, #3b82f6); color: #ffffff; }
    .avatar-keys { background: linear-gradient(135deg, #f59e0b, #d97706); color: #ffffff; }
    .avatar-coins { background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; }
    .avatar-apks { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: #ffffff; }
    .avatar-general { background: linear-gradient(135deg, #64748b, #475569); color: #ffffff; }

    .cat-drawer {
        display: none;
        padding: 18px 20px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    .cat-drawer.active {
        display: block;
        animation: slideDown 0.25s ease;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .product-mini-table {
        width: 100%;
        border-collapse: collapse;
        background: #ffffff;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 14px;
        font-size: 13px;
    }

    .product-mini-table th {
        background: #f1f5f9;
        padding: 10px 14px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        border-bottom: 1px solid #e2e8f0;
    }

    .product-mini-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .product-mini-table tr:last-child td {
        border-bottom: none;
    }

    .btn-toggle-drawer {
        padding: 6px 12px;
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .btn-toggle-drawer:hover {
        background: #2563eb;
        color: #ffffff;
    }

    .btn-edit-cat {
        padding: 6px 10px;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s;
    }

    .btn-edit-cat:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .btn-delete-cat {
        padding: 6px 10px;
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s;
    }

    .btn-delete-cat:hover {
        background: #dc2626;
        color: #ffffff;
    }

    /* Preset Buttons */
    .preset-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
        transition: all 0.2s;
        text-align: left;
    }

    .preset-pill:hover {
        background: #eff6ff;
        border-color: #93c5fd;
        color: #2563eb;
        transform: translateY(-1px);
    }

    /* Modal Styling */
    .modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
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
        max-width: 440px;
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

<!-- 1. Top Summary Stats Row -->
<div class="cat-summary-grid">
    <div class="cat-stat-card">
        <div class="cat-stat-icon icon-blue">
            <i class="fa-solid fa-layer-group"></i>
        </div>
        <div>
            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Categories</div>
            <div style="font-size: 22px; font-weight: 800; color: #0f172a;">{{ $categories->count() }}</div>
        </div>
    </div>

    <div class="cat-stat-card">
        <div class="cat-stat-icon icon-emerald">
            <i class="fa-solid fa-gamepad"></i>
        </div>
        <div>
            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Linked Products</div>
            <div style="font-size: 22px; font-weight: 800; color: #059669;">{{ $categories->sum('products_count') }} Active Tools</div>
        </div>
    </div>

    <div class="cat-stat-card">
        <div class="cat-stat-icon icon-amber">
            <i class="fa-solid fa-key"></i>
        </div>
        <div>
            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Stock Keys</div>
            <div style="font-size: 22px; font-weight: 800; color: #d97706;">{{ $totalKeysInStock ?? 0 }} Keys</div>
        </div>
    </div>

    <div class="cat-stat-card">
        <div class="cat-stat-icon icon-purple">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Telegram Bot Sync</div>
            <div style="font-size: 15px; font-weight: 800; color: #9333ea;">Auto-Synced 24/7</div>
        </div>
    </div>
</div>

<!-- 2. Main Categories Split Layout -->
<div class="category-split">
    
    <!-- LEFT: Available Categories & Expandable Option Bars -->
    <div>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px; margin: 0;">
                <i class="fa-solid fa-folder-tree" style="color: #2563eb;"></i>
                <span>Store Categories List ({{ $categories->count() }})</span>
            </h3>
        </div>

        @if($categories->isEmpty())
            <div class="card-modern" style="padding: 60px 20px; text-align: center; color: #94a3b8;">
                <i class="fa-regular fa-folder-open" style="font-size: 40px; color: #cbd5e1; margin-bottom: 10px;"></i>
                <div style="font-weight: 700; font-size: 16px; color: #0f172a;">No Categories Found</div>
                <div style="font-size: 13px; color: #64748b; margin-top: 4px;">Use the presets on the right to create your categories instantly.</div>
            </div>
        @else
            @foreach($categories as $index => $cat)
                @php
                    $isAutoPlay = str_contains(strtolower($cat->name), 'autoplay') || str_contains(strtolower($cat->name), 'aim');
                    $isKey = str_contains(strtolower($cat->name), 'key') || str_contains(strtolower($cat->name), 'pass');
                    $isCoin = str_contains(strtolower($cat->name), 'coin') || str_contains(strtolower($cat->name), 'topup') || str_contains(strtolower($cat->name), 'gem');
                    $isApk = str_contains(strtolower($cat->name), 'apk') || str_contains(strtolower($cat->name), 'download');

                    $avatarClass = $isAutoPlay ? 'avatar-autoplay' : ($isKey ? 'avatar-keys' : ($isCoin ? 'avatar-coins' : ($isApk ? 'avatar-apks' : 'avatar-general')));
                    $iconClass = $isAutoPlay ? 'fa-gamepad' : ($isKey ? 'fa-key' : ($isCoin ? 'fa-gem' : ($isApk ? 'fa-mobile-screen-button' : 'fa-folder')));
                    
                    $totalCatKeys = $cat->products->sum('available_keys_count');
                @endphp

                <div class="cat-item-card" id="cat-card-{{ $cat->id }}">
                    <!-- Category Header Bar (Clickable) -->
                    <div class="cat-item-header" onclick="toggleCategoryDrawer({{ $cat->id }})">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div class="cat-avatar {{ $avatarClass }}">
                                <i class="fa-solid {{ $iconClass }}"></i>
                            </div>
                            <div>
                                <div style="font-weight: 700; color: #0f172a; font-size: 16px;">
                                    {{ $cat->name }}
                                </div>
                                <div style="margin-top: 3px;">
                                    <span style="font-size: 13px; font-weight: 700; color: {{ $totalCatKeys > 0 ? '#059669' : '#dc2626' }};">
                                        <i class="fa-solid fa-key"></i> {{ $totalCatKeys }} Keys Stock
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Header Action Buttons -->
                        <div style="display: flex; align-items: center; gap: 8px;" onclick="event.stopPropagation();">
                            <button type="button" class="btn-toggle-drawer" onclick="toggleCategoryDrawer({{ $cat->id }})" id="btn-toggle-{{ $cat->id }}">
                                <span>Options & Items</span>
                                <i class="fa-solid fa-chevron-down" id="chevron-{{ $cat->id }}" style="transition: transform 0.2s;"></i>
                            </button>

                            <button type="button" class="btn-edit-cat" onclick="openEditCategoryModal('{{ $cat->id }}', '{{ addslashes($cat->name) }}')" title="Edit Name">
                                <i class="fa-solid fa-pen"></i> Edit
                            </button>

                            <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" data-confirm="Are you sure you want to delete '{{ addslashes($cat->name) }}'? All associated settings will be reset." data-confirm-title="Delete Category" data-confirm-btn="Yes, Delete" data-confirm-color="#ef4444" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete-cat" title="Delete Category">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Category Expandable Option Bar / Drawer -->
                    <div class="cat-drawer {{ $index === 0 ? 'active' : '' }}" id="cat-drawer-{{ $cat->id }}">
                        
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                            <div style="font-size: 13px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-list-check" style="color: #2563eb;"></i>
                                <span>Products & Tools in this Category ({{ $cat->products->count() }})</span>
                            </div>
                            <a href="{{ route('admin.keys.index') }}" style="font-size: 12px; color: #2563eb; font-weight: 600; text-decoration: none;">
                                + Add Keys / Stock &rarr;
                            </a>
                        </div>

                        @if($cat->products->isEmpty())
                            <div style="background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 10px; padding: 24px; text-align: center; color: #64748b; margin-bottom: 14px;">
                                <i class="fa-solid fa-box-open" style="font-size: 24px; color: #94a3b8; margin-bottom: 6px;"></i>
                                <div style="font-weight: 600; font-size: 13px; color: #1e293b;">No Products Assigned to {{ $cat->name }} Yet</div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Assign products or create new items under this category.</div>
                                <a href="{{ route('admin.keys.index') }}" style="display: inline-flex; align-items: center; gap: 6px; margin-top: 10px; padding: 6px 12px; background: #2563eb; color: #ffffff; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none;">
                                    <i class="fa-solid fa-plus"></i> Go to Keys & Pricing
                                </a>
                            </div>
                        @else
                            <table class="product-mini-table">
                                <thead>
                                    <tr>
                                        <th>Product / Tool Name</th>
                                        <th>Game Platform</th>
                                        <th>Live Stock Available</th>
                                        <th style="text-align: right;">Quick Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cat->products as $prod)
                                        @php
                                            $gameParam = str_contains($prod->name, 'Carrom') ? 'Carrom' : (str_contains($prod->name, 'KOS') ? 'KOS' : (str_contains($prod->name, 'Snake') ? 'Snake' : 'AI'));
                                        @endphp
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <i class="fa-solid fa-gamepad" style="color: #2563eb;"></i>
                                                    <span style="font-weight: 700; color: #0f172a;">{{ $prod->name }}</span>
                                                </div>
                                            </td>
                                            <td style="color: #64748b; font-size: 12px;">
                                                {{ $prod->game_name ?? 'Carrom Pool / Ludo' }}
                                            </td>
                                            <td>
                                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: {{ $prod->available_keys_count > 10 ? '#ecfdf5' : ($prod->available_keys_count > 0 ? '#fffbeb' : '#fef2f2') }}; color: {{ $prod->available_keys_count > 10 ? '#059669' : ($prod->available_keys_count > 0 ? '#d97706' : '#dc2626') }};">
                                                    <i class="fa-solid fa-circle" style="font-size: 6px;"></i> {{ $prod->available_keys_count }} Keys Available
                                                </span>
                                            </td>
                                            <td style="text-align: right;">
                                                <div style="display: inline-flex; gap: 6px;">
                                                    <a href="{{ route('admin.keys.index', ['game' => $gameParam]) }}" title="Add / View Keys in Keys & Pricing" style="padding: 5px 10px; background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                                        <i class="fa-solid fa-key"></i> + Add / View Keys
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- RIGHT: Add New Category -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        
        <!-- Add Custom Category Card -->
        <div class="card-modern">
            <div class="card-header-bar">
                <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px; margin: 0;">
                    <i class="fa-solid fa-plus-circle" style="color: #059669;"></i>
                    <span>Add Custom Category</span>
                </h3>
            </div>

            <div style="padding: 20px;">
                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px;">
                            Category Name
                        </label>
                        <input type="text" name="name" id="new_cat_name" placeholder="e.g. VIP Key Passes, Snake Hacks" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 14px; outline: none; font-weight: 600;" required oninput="updateSlugPreview(this.value)">
                    </div>

                    <div style="margin-bottom: 18px; padding: 10px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">URL Slug Preview:</div>
                        <div style="font-family: monospace; font-size: 12px; color: #2563eb; font-weight: 700; margin-top: 2px;" id="slugPreview">
                            category-slug-here
                        </div>
                    </div>

                    <button type="submit" style="width: 100%; padding: 12px; background: #2563eb; color: #ffffff; border: none; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s;">
                        <i class="fa-solid fa-folder-plus"></i> + Create Category
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>

<!-- 3. Edit Category Modal -->
<div class="modal-backdrop" id="editCatModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Edit Category</h3>
            <button type="button" onclick="closeEditCategoryModal()" style="background: none; border: none; font-size: 18px; color: #94a3b8; cursor: pointer;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="editCatForm" method="POST" style="padding: 20px;">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px;">Category Name</label>
                <input type="text" name="name" id="edit_cat_name" class="form-control" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 14px; font-weight: 600;" required>
            </div>

            <button type="submit" style="width: 100%; padding: 12px; background: #2563eb; color: #ffffff; border: none; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="fa-solid fa-check"></i> Save Changes
            </button>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function toggleCategoryDrawer(catId) {
        const drawer = document.getElementById('cat-drawer-' + catId);
        const chevron = document.getElementById('chevron-' + catId);

        if (drawer.classList.contains('active')) {
            drawer.classList.remove('active');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        } else {
            drawer.classList.add('active');
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        }
    }

    function applyPreset(name) {
        const input = document.getElementById('new_cat_name');
        input.value = name;
        updateSlugPreview(name);
        input.focus();
    }

    function updateSlugPreview(val) {
        const slug = val.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
        document.getElementById('slugPreview').textContent = slug || 'category-slug-here';
    }

    function openEditCategoryModal(catId, catName) {
        const modal = document.getElementById('editCatModal');
        const form = document.getElementById('editCatForm');
        const nameInput = document.getElementById('edit_cat_name');

        nameInput.value = catName;
        form.action = '/admin/categories/' + catId;
        modal.classList.add('active');
    }

    function closeEditCategoryModal() {
        document.getElementById('editCatModal').classList.remove('active');
    }

    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeEditCategoryModal();
    });
</script>
@endsection
