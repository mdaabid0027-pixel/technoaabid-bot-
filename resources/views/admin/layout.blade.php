<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Khelo Store</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js for Visual Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SweetAlert2 for Premium Modern Popups -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Premium Light Mode Design System -->
    <style>
        :root {
            --bg-primary: #f8fafc;
            --bg-secondary: #ffffff;
            --bg-card: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            
            --primary: #3b82f6;
            --primary-glow: rgba(59, 130, 246, 0.2);
            --success: #10b981;
            --success-glow: rgba(16, 185, 129, 0.2);
            --danger: #ef4444;
            --danger-glow: rgba(239, 68, 68, 0.2);
            --warning: #f59e0b;
            --violet: #6366f1;
            --border-color: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        aside {
            width: 260px;
            background-color: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
        }

        .brand {
            height: 70px;
            padding: 0 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 19px;
            font-weight: 700;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-color);
            text-decoration: none;
            box-sizing: border-box;
        }

        .brand i {
            color: var(--primary);
        }

        .nav-links {
            list-style: none;
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex-grow: 1;
            overflow-y: auto;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .nav-links a:hover, .nav-links li.active > a {
            color: var(--primary);
            background-color: rgba(59, 130, 246, 0.05);
            border-left: 3px solid var(--primary);
            font-weight: 600;
        }

        .nav-links a i {
            font-size: 18px;
            width: 20px;
        }

        /* Submenu Styling */
        .has-submenu .submenu-links {
            list-style: none;
            padding-left: 28px;
            margin-top: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, margin-top 0.2s ease;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .has-submenu.open .submenu-links {
            max-height: 300px;
            margin-top: 4px;
        }

        .has-submenu .submenu-chevron {
            transition: transform 0.3s ease;
        }

        .has-submenu.open .submenu-chevron {
            transform: rotate(180deg);
        }

        .submenu-links a {
            padding: 8px 12px;
            font-size: 13px;
            color: var(--text-secondary);
            border-left: none !important;
            border-radius: 6px;
        }

        .submenu-links a:hover, .submenu-links li.active a {
            color: var(--primary);
            background-color: rgba(59, 130, 246, 0.08);
            font-weight: 600;
        }

        .logout-section {
            padding: 24px 16px;
            border-top: 1px solid var(--border-color);
        }

        .btn-logout {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            background-color: rgba(239, 68, 68, 0.05);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.15);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-logout:hover {
            background-color: var(--danger);
            color: #fff;
            box-shadow: 0 4px 12px var(--danger-glow);
        }

        /* Main Content & Header Styling */
        .admin-main {
            margin-left: 260px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: var(--bg-primary);
            min-width: 0;
        }

        .admin-header {
            height: 70px;
            background-color: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            padding: 0 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
            box-sizing: border-box;
        }

        .header-title h2 {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: 1px solid var(--border-color);
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            color: var(--text-primary);
            font-size: 16px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .live-bot-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background-color: rgba(59, 130, 246, 0.08);
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: #2563eb;
            font-size: 13px;
            font-weight: 600;
            border-radius: 9999px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .live-bot-badge:hover {
            background-color: rgba(59, 130, 246, 0.15);
            transform: translateY(-1px);
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #10b981;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        /* Profile Dropdown Styling */
        .profile-dropdown-wrapper {
            position: relative;
        }

        .profile-trigger-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 14px 5px 5px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 9999px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .profile-trigger-btn:hover, .profile-dropdown-wrapper.active .profile-trigger-btn {
            background-color: #f8fafc;
            border-color: #cbd5e1;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .profile-trigger-btn .avatar-circle {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        .profile-trigger-btn .user-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .profile-trigger-btn .profile-chevron {
            font-size: 11px;
            color: #94a3b8;
            transition: transform 0.2s ease;
            margin-left: 2px;
        }

        .profile-dropdown-wrapper.active .profile-chevron {
            transform: rotate(180deg);
        }

        .profile-menu-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 220px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            box-shadow: 0 12px 28px -4px rgba(0, 0, 0, 0.12), 0 4px 10px -2px rgba(0, 0, 0, 0.05);
            padding: 6px;
            z-index: 1000;
            display: none;
            flex-direction: column;
            gap: 2px;
            animation: dropdownFadeIn 0.15s ease-out;
        }

        .profile-dropdown-wrapper.active .profile-menu-dropdown {
            display: flex;
        }

        @keyframes dropdownFadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .profile-menu-header {
            padding: 10px 12px;
            background: #f8fafc;
            border-radius: 8px;
            margin-bottom: 4px;
        }

        .profile-menu-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 4px 0;
        }

        .profile-menu-item {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            background: transparent;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-align: left;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .profile-menu-item:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }

        .profile-menu-item.profile-logout-item:hover {
            background-color: #fef2f2;
            color: #dc2626;
        }

        /* Change Password Modal */
        .modal-pwd-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-pwd-backdrop.active {
            display: flex;
        }

        .modal-pwd-card {
            background: #ffffff;
            border-radius: 10px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: modalPop 0.15s ease-out;
            border: 1px solid #cbd5e1;
        }

        @keyframes modalPop {
            from { opacity: 0; transform: scale(0.97); }
            to { opacity: 1; transform: scale(1); }
        }

        .admin-content {
            padding: 32px;
            flex-grow: 1;
        }

        @media (max-width: 768px) {
            aside {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            aside.active {
                transform: translateX(0);
            }
            .admin-main {
                margin-left: 0;
            }
            .mobile-toggle {
                display: block;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 90;
            }
            .sidebar-overlay.active {
                display: block;
            }
            .live-bot-badge {
                display: none;
            }
        }

        /* Premium Light Card base style */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05), 0 10px 15px -3px rgba(0, 0, 0, 0.02);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }

        /* Notification Banners */
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: var(--success);
        }

        .alert-danger {
            background-color: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--danger);
        }

        /* Common Form Styling */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            color: var(--text-primary);
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #2563eb;
            box-shadow: 0 4px 12px var(--primary-glow);
            transform: translateY(-1px);
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            padding: 16px;
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 14px;
            border-bottom: 1px solid var(--border-color);
            background-color: #f8fafc;
        }

        td {
            padding: 16px;
            font-size: 15px;
            border-bottom: 1px solid #f1f5f9;
            color: var(--text-primary);
        }

        tr:hover td {
            background-color: #f8fafc;
        }

        /* Badge/Pill Styles */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .badge-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        .badge-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        /* Pagination Styles (Bootstrap 5 & Custom) */
        .pagination {
            display: flex;
            align-items: center;
            list-style: none;
            gap: 6px;
            margin: 0;
            padding: 0;
        }

        .page-item {
            display: inline-block;
        }

        .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
            min-width: 36px;
            height: 36px;
        }

        .page-link:hover {
            background-color: #eff6ff;
            color: #2563eb;
            border-color: #93c5fd;
        }

        .page-item.active .page-link {
            background-color: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.25);
        }

        .page-item.disabled .page-link {
            color: #94a3b8;
            background-color: #f8fafc;
            border-color: #e2e8f0;
            cursor: not-allowed;
            pointer-events: none;
        }

        nav[role="navigation"] svg {
            width: 16px !important;
            height: 16px !important;
            max-width: 16px !important;
            max-height: 16px !important;
            display: inline-block !important;
        }

        /* Bootstrap Pagination Utilities */
        .d-flex { display: flex !important; }
        .justify-content-between { justify-content: space-between !important; }
        .align-items-center, .align-items-sm-center { align-items: center !important; }
        .justify-content-sm-between { justify-content: space-between !important; }
        .flex-fill { flex: 1 1 auto !important; }
        .flex-sm-fill { flex: 1 1 auto !important; }
        .d-sm-none { display: none !important; }
        .d-sm-flex { display: flex !important; }
        .text-muted { color: #64748b !important; }
        .fw-semibold { font-weight: 700 !important; color: #0f172a !important; }
        .small { font-size: 13px !important; }

        @media (max-width: 640px) {
            .d-sm-none { display: flex !important; }
            .d-sm-flex { display: none !important; }
        }

        /* Mobile Responsive Styles */
        .btn-sidebar-close {
            display: none;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            z-index: 998;
        }

        @media (max-width: 768px) {
            body {
                flex-direction: column;
                overflow-x: hidden;
            }

            .admin-main {
                margin-left: 0 !important;
                width: 100% !important;
                min-width: 100% !important;
            }

            .admin-header {
                height: 58px !important;
                padding: 0 14px !important;
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                justify-content: space-between !important;
                position: sticky !important;
                top: 0 !important;
                z-index: 100 !important;
                background: #ffffff !important;
                border-bottom: 1px solid #e2e8f0 !important;
                box-shadow: 0 2px 4px rgba(0,0,0,0.03) !important;
            }

            .mobile-toggle {
                display: flex !important;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                background: #f8fafc;
                border: 1px solid #cbd5e1;
                border-radius: 8px;
                color: #0f172a;
                font-size: 16px;
                cursor: pointer;
            }

            .header-title h2 {
                font-size: 16px !important;
                font-weight: 800 !important;
                color: #0f172a !important;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 170px;
                margin: 0;
            }

            .user-name {
                display: none !important;
            }

            .profile-trigger-btn {
                padding: 4px 6px 4px 4px !important;
                gap: 4px !important;
            }

            .admin-content {
                padding: 14px 10px !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            /* Sidebar Drawer */
            aside {
                position: fixed !important;
                top: 0 !important;
                bottom: 0 !important;
                left: 0 !important;
                width: 270px !important;
                z-index: 1000 !important;
                transform: translateX(-100%) !important;
                transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
                box-shadow: 10px 0 30px rgba(0, 0, 0, 0.25) !important;
                background-color: #ffffff !important;
            }

            aside.active {
                transform: translateX(0) !important;
            }

            .btn-sidebar-close {
                display: flex !important;
            }

            .sidebar-overlay.active {
                display: block !important;
            }

            /* Full Width Layout Adjustments */
            .panel-card, .card, .stat-card-gradient, .stat-card-white {
                border-radius: 12px !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            .keys-layout-grid, .dashboard-split, .metrics-grid, .pricing-grid {
                grid-template-columns: 1fr !important;
                gap: 14px !important;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Backdrop Overlay for Mobile Sidebar -->
    <div class="sidebar-overlay" id="sidebar_overlay"></div>

    <!-- Sidebar -->
    <aside id="admin_sidebar">
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding: 0 16px 0 20px; height: 60px;">
            <a href="{{ route('admin.dashboard') }}" class="brand" style="border-bottom: none; padding: 0; height: auto;">
                <i class="fa-solid fa-gamepad" style="color: #2563eb;"></i>
                <span>KHELO STORE</span>
            </a>
            <button type="button" class="btn-sidebar-close" onclick="closeSidebar()" style="background: #f1f5f9; border: 1px solid #cbd5e1; width: 30px; height: 30px; border-radius: 6px; color: #64748b; cursor: pointer; align-items: center; justify-content: center; font-size: 14px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <ul class="nav-links">
            <li class="{{ Route::is('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="{{ Route::is('admin.resellers.*') ? 'active' : '' }}">
                <a href="{{ route('admin.resellers.index') }}">
                    <i class="fa-solid fa-user-tag" style="color: #a855f7;"></i>
                    <span>Resellers</span>
                    @php $vipCount = \App\Models\TelegramUser::whereHas('customPrices')->count(); @endphp
                    @if($vipCount > 0)
                        <span style="background: #a855f7; color: #ffffff; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 9999px; margin-left: auto;">{{ $vipCount }}</span>
                    @endif
                </a>
            </li>
            <li class="{{ Route::is('admin.customers.*') || (Route::is('admin.users.*') && !Route::is('admin.resellers.*')) ? 'active' : '' }}">
                <a href="{{ route('admin.customers.index') }}">
                    <i class="fa-solid fa-users" style="color: #3b82f6;"></i>
                    <span>Customers</span>
                    @php $custCount = \App\Models\TelegramUser::whereDoesntHave('customPrices')->count(); @endphp
                    <span style="background: #3b82f6; color: #ffffff; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 9999px; margin-left: auto;">{{ $custCount }}</span>
                </a>
            </li>
            <li class="{{ Route::is('admin.keys.*') ? 'active' : '' }}">
                <a href="{{ route('admin.keys.index') }}">
                    <i class="fa-solid fa-key" style="color: #0ea5e9;"></i>
                    <span>Keys Stock</span>
                </a>
            </li>
            <li class="{{ Route::is('admin.pricing.*') ? 'active' : '' }}">
                <a href="{{ route('admin.pricing.index') }}">
                    <i class="fa-solid fa-tags" style="color: #10b981;"></i>
                    <span>Pricing Matrix</span>
                </a>
            </li>
            <li class="{{ Route::is('admin.ingame-items.*') ? 'active' : '' }}">
                <a href="{{ route('admin.ingame-items.index') }}">
                    <i class="fa-solid fa-gem" style="color: #ec4899;"></i>
                    <span>In-Game Items</span>
                </a>
            </li>
            <li class="{{ Route::is('admin.sounds.*') ? 'active' : '' }}">
                <a href="{{ route('admin.sounds.index') }}">
                    <i class="fa-solid fa-music" style="color: #f59e0b;"></i>
                    <span>Sound Effects</span>
                    <span style="background: #f59e0b; color: #ffffff; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 9999px; margin-left: auto;">FX</span>
                </a>
            </li>
            <li class="{{ Route::is('admin.rewards-audit.*') ? 'active' : '' }}">
                <a href="{{ route('admin.rewards-audit.index') }}">
                    <i class="fa-solid fa-gift" style="color: #e11d48;"></i>
                    <span>Rewards Audit</span>
                </a>
            </li>
        </ul>
    </aside>

    <div class="admin-main">
        <header class="admin-header">
            <div style="display: flex; align-items: center; gap: 12px;">
                <button class="mobile-toggle" id="btn_mobile_toggle" aria-label="Toggle Navigation">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="header-title">
                    <h2>@yield('header_title', 'Dashboard')</h2>
                </div>
            </div>
            
            <div class="header-actions">
                <div class="profile-dropdown-wrapper" id="profile_dropdown_wrapper">
                    <button type="button" class="profile-trigger-btn" id="profile_trigger_btn" onclick="toggleProfileDropdown(event)">
                        <div class="avatar-circle">
                            {{ strtoupper(substr(Auth::user()->name ?? 'Shabab Gaming', 0, 1)) }}
                        </div>
                        <span class="user-name">{{ Auth::user()->name ?? 'Shabab Gaming' }}</span>
                        <i class="fa-solid fa-chevron-down profile-chevron"></i>
                    </button>

                    <div class="profile-menu-dropdown" id="profile_menu_dropdown">
                        <div class="profile-menu-header">
                            <div style="font-weight: 700; font-size: 13px; color: var(--text-primary);">{{ Auth::user()->name ?? 'Admin User' }}</div>
                            <div style="font-size: 11px; color: var(--text-secondary); margin-top: 1px;">{{ Auth::user()->email ?? 'admin@khelostore.com' }}</div>
                        </div>
                        <button type="button" class="profile-menu-item" onclick="openChangePasswordModal()">
                            <i class="fa-solid fa-key" style="color: #6366f1; width: 16px;"></i>
                            <span>Change Password</span>
                        </button>
                        <div class="profile-menu-divider"></div>
                        <form method="POST" action="{{ route('admin.logout') }}" style="margin: 0;">
                            @csrf
                            <button type="submit" class="profile-menu-item profile-logout-item">
                                <i class="fa-solid fa-arrow-right-from-bracket" style="color: #ef4444; width: 16px;"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="admin-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Change Password Modal -->
    <div class="modal-pwd-backdrop" id="modal_change_password" onclick="if(event.target === this) closeChangePasswordModal()">
        <div class="modal-pwd-card">
            <div style="padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                <div style="font-weight: 700; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-key" style="color: #6366f1;"></i>
                    <span>Change Admin Password</span>
                </div>
                <button type="button" onclick="closeChangePasswordModal()" style="background: none; border: none; font-size: 16px; color: #94a3b8; cursor: pointer;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form action="{{ route('admin.password.update') }}" method="POST" style="padding: 22px 24px;">
                @csrf
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 7px;">New Password</label>
                    <input type="password" name="password" required minlength="6" placeholder="Min. 6 characters" style="width: 100%; height: 42px; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none; box-sizing: border-box; transition: border-color 0.2s;">
                </div>
                <div style="margin-bottom: 22px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 7px;">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required minlength="6" placeholder="Confirm new password" style="width: 100%; height: 42px; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none; box-sizing: border-box; transition: border-color 0.2s;">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeChangePasswordModal()" style="height: 40px; padding: 0 18px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; color: #475569; display: inline-flex; align-items: center; justify-content: center;">Cancel</button>
                    <button type="submit" style="height: 40px; padding: 0 22px; background: #4f46e5; border: none; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer; color: #ffffff; box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25); display: inline-flex; align-items: center; justify-content: center;">Save Password</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleAutoPlaySubmenu(event) {
            event.preventDefault();
            const parent = event.currentTarget.closest('.has-submenu');
            if (parent) {
                parent.classList.toggle('open');
            }
        }

        function toggleProfileDropdown(event) {
            event.stopPropagation();
            const wrapper = document.getElementById('profile_dropdown_wrapper');
            if (wrapper) {
                wrapper.classList.toggle('active');
            }
        }

        document.addEventListener('click', (e) => {
            const wrapper = document.getElementById('profile_dropdown_wrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                wrapper.classList.remove('active');
            }
        });

        function openChangePasswordModal() {
            const wrapper = document.getElementById('profile_dropdown_wrapper');
            if (wrapper) wrapper.classList.remove('active');
            const modal = document.getElementById('modal_change_password');
            if (modal) modal.classList.add('active');
        }

        function closeChangePasswordModal() {
            const modal = document.getElementById('modal_change_password');
            if (modal) modal.classList.remove('active');
        }

        function closeSidebar() {
            const adminSidebar = document.getElementById('admin_sidebar');
            const sidebarOverlay = document.getElementById('sidebar_overlay');
            if (adminSidebar) adminSidebar.classList.remove('active');
            if (sidebarOverlay) sidebarOverlay.classList.remove('active');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const btnMobileToggle = document.getElementById('btn_mobile_toggle');
            const adminSidebar = document.getElementById('admin_sidebar');
            const sidebarOverlay = document.getElementById('sidebar_overlay');

            if (btnMobileToggle && adminSidebar && sidebarOverlay) {
                const toggleSidebar = (e) => {
                    if (e) e.stopPropagation();
                    adminSidebar.classList.toggle('active');
                    sidebarOverlay.classList.toggle('active');
                };

                btnMobileToggle.addEventListener('click', toggleSidebar);
                sidebarOverlay.addEventListener('click', closeSidebar);
            }
        });

        // Premium Global SweetAlert2 Confirm Modal System
        window.confirmAction = function(options) {
            const {
                title = 'Are you sure?',
                text = 'This action cannot be undone.',
                icon = 'warning',
                confirmText = 'Yes, Proceed',
                cancelText = 'Cancel',
                confirmColor = '#ef4444',
                onConfirm = () => {}
            } = options;

            return Swal.fire({
                title: title,
                text: text,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#94a3b8',
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
                reverseButtons: true,
                focusCancel: true,
                customClass: {
                    popup: 'swal2-modern-popup'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    onConfirm();
                }
            });
        };

        window.showToast = function(message, icon = 'success') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            Toast.fire({
                icon: icon,
                title: message
            });
        };

        // Automatic interceptor for forms with data-confirm
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form && form.hasAttribute('data-confirm')) {
                e.preventDefault();
                const msg = form.getAttribute('data-confirm');
                const title = form.getAttribute('data-confirm-title') || 'Confirm Action';
                const btnText = form.getAttribute('data-confirm-btn') || 'Yes, Proceed';
                const btnColor = form.getAttribute('data-confirm-color') || '#ef4444';
                const icon = form.getAttribute('data-confirm-icon') || 'warning';

                window.confirmAction({
                    title: title,
                    text: msg,
                    icon: icon,
                    confirmText: btnText,
                    confirmColor: btnColor,
                    onConfirm: () => {
                        form.removeAttribute('data-confirm');
                        form.submit();
                    }
                });
            }
        });
    </script>

    <style>
        /* Modern SweetAlert2 UI Styling */
        .swal2-popup {
            border-radius: 16px !important;
            padding: 24px 20px !important;
            font-family: 'Inter', sans-serif !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25) !important;
        }
        .swal2-title {
            font-size: 18px !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            margin-bottom: 6px !important;
        }
        .swal2-html-container {
            font-size: 13.5px !important;
            color: #475569 !important;
            line-height: 1.5 !important;
        }
        .swal2-actions {
            gap: 10px !important;
            margin-top: 20px !important;
        }
        .swal2-confirm {
            border-radius: 8px !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            padding: 9px 20px !important;
        }
        .swal2-cancel {
            border-radius: 8px !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            padding: 9px 18px !important;
            background: #f1f5f9 !important;
            color: #475569 !important;
            border: 1px solid #cbd5e1 !important;
        }
    </style>

    @yield('scripts')
</body>
</html>
