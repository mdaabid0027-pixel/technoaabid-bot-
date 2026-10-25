<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Khelo Store Telegram Mini App</title>
    <!-- Telegram WebApp SDK -->
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <!-- Google Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-page: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
            --red-btn: #ff3547;
            --blue-btn: #2b82d9;
            --green-btn: #22c55e;
            --gray-btn: #334155;
            --border-color: #334155;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background-color: var(--bg-page);
            color: var(--text-main);
            min-height: 100vh;
            padding: 16px;
            padding-bottom: 30px;
        }

        /* Top User Profile Header Card */
        .profile-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            color: #fff;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 700;
            font-size: 15px;
        }

        .user-id {
            font-size: 12px;
            color: var(--text-sub);
        }

        .balance-badge {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: var(--green-btn);
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 14px;
        }

        /* Section Titles */
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-sub);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Menu Grid Matching Screenshot Colors */
        .menu-grid {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .grid-row {
            display: flex;
            gap: 10px;
            width: 100%;
        }

        .menu-btn {
            flex: 1;
            padding: 16px;
            border-radius: 14px;
            border: none;
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-decoration: none;
        }

        .menu-btn:active {
            transform: scale(0.97);
        }

        /* Vibrant Colors matching screenshot */
        .btn-red {
            background: var(--red-btn);
        }

        .btn-blue {
            background: var(--blue-btn);
        }

        .btn-green {
            background: var(--green-btn);
        }

        .btn-gray {
            background: #e2e8f0;
            color: #0f172a;
        }

        /* Stock Status Container */
        .stock-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 16px;
            margin-top: 24px;
        }

        .stock-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 14px;
        }

        .stock-item:last-child {
            border-bottom: none;
        }

        .stock-count {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 700;
        }

        /* Bottom Modal Sheet */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 100;
            align-items: flex-end;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-sheet {
            background: #1e293b;
            width: 100%;
            border-top-left-radius: 24px;
            border-top-right-radius: 24px;
            padding: 24px;
            box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }

        .modal-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-close-modal {
            background: none;
            border: none;
            color: var(--text-sub);
            font-size: 20px;
            cursor: pointer;
        }

        .duration-options {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }

        .opt-btn {
            background: #0f172a;
            border: 1px solid var(--border-color);
            color: #fff;
            padding: 12px 16px;
            border-radius: 10px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .opt-btn.active {
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.15);
        }

        .btn-checkout {
            width: 100%;
            padding: 14px;
            background: var(--green-btn);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <!-- User Wallet Profile Card -->
    <div class="profile-card">
        <div class="user-info">
            <div class="user-avatar" id="user_avatar">KS</div>
            <div class="user-details">
                <span class="user-name" id="user_name">Khelo Store Reseller</span>
                <span class="user-id" id="user_id">Telegram User</span>
            </div>
        </div>
        <div class="balance-badge" id="user_balance">₹10,000.00</div>
    </div>

    <!-- Main Colored Menu Grid Matching User Screenshot -->
    <div class="section-title">
        <i class="fa-solid fa-gamepad"></i> Store Games & Tools
    </div>

    <div class="menu-grid">
        <!-- Row 1: Red Aim Carrom King (Full Width) -->
        <div class="grid-row">
            <button class="menu-btn btn-red" onclick="openProductModal('Aim Carrom King')" style="background: #ef4444; color: #ffffff; font-size: 16px; font-weight: 800; border-radius: 12px; padding: 16px; width: 100%;">
                🎯 Aim Carrom King
            </button>
        </div>

        <!-- Row 2: Blue Snake Engine (Full Width) -->
        <div class="grid-row">
            <button class="menu-btn btn-blue" onclick="openProductModal('Snake Engine')" style="background: #2563eb; color: #ffffff; font-size: 16px; font-weight: 800; border-radius: 12px; padding: 16px; width: 100%;">
                🐍 Snake Engine
            </button>
        </div>

        <!-- Row 3: Green KOS & Green Aim Ai Engine (2 Columns) -->
        <div class="grid-row">
            <button class="menu-btn btn-green" onclick="openProductModal('KOS')" style="background: #22c55e; color: #ffffff; font-size: 15px; font-weight: 800; border-radius: 12px; padding: 15px;">
                🎮 KOS
            </button>
            <button class="menu-btn btn-green" onclick="openProductModal('Aim Ai Engine')" style="background: #22c55e; color: #ffffff; font-size: 15px; font-weight: 800; border-radius: 12px; padding: 15px;">
                🤖 Aim Ai Engine
            </button>
        </div>

        <!-- Row 4: Gray bitAIM+ & Gray Web Shop (2 Columns) -->
        <div class="grid-row">
            <button class="menu-btn btn-gray" onclick="openProductModal('bitAIM+')" style="background: #e2e8f0; color: #0f172a; font-size: 15px; font-weight: 800; border-radius: 12px; padding: 15px;">
                🚀 bitAIM+
            </button>
            <button class="menu-btn btn-gray" onclick="alert('🛍️ Opening In-Game Items Store!')" style="background: #e2e8f0; color: #0f172a; font-size: 15px; font-weight: 800; border-radius: 12px; padding: 15px;">
                🛒 Web Shop
            </button>
        </div>

        <!-- Row 5: Blue Check Stock & Red Check Balance (2 Columns) -->
        <div class="grid-row">
            <button class="menu-btn btn-blue" onclick="scrollToStock()" style="background: #2563eb; color: #ffffff; font-size: 15px; font-weight: 800; border-radius: 12px; padding: 15px;">
                📦 Check Stock
            </button>
            <button class="menu-btn btn-red" onclick="alert('💰 Your Current Wallet Balance: ₹' + (window.userBalance || '0.00'))" style="background: #ef4444; color: #ffffff; font-size: 15px; font-weight: 800; border-radius: 12px; padding: 15px;">
                💰 Check Balance
            </button>
        </div>

        <!-- Row 6: Green Add Balance & Purple Spin Wheel (2 Columns) -->
        <div class="grid-row">
            <button class="menu-btn btn-green" onclick="alert('💳 Please scan UPI QR in Bot chat to add balance instantly!')" style="background: #16a34a; color: #ffffff; font-size: 15px; font-weight: 800; border-radius: 12px; padding: 15px;">
                💳 Add Balance
            </button>
            <button class="menu-btn" onclick="window.location.href='/telegram/spin-wheel?telegram_id=' + (window.userTelegramId || '')" style="background: #7c3aed; color: #ffffff; font-size: 15px; font-weight: 800; border-radius: 12px; padding: 15px;">
                🎰 Daily Spin
            </button>
        </div>
    </div>

    <!-- Stock Availability Section -->
    <div class="stock-card" id="stock_section">
        <div class="section-title">
            <i class="fa-solid fa-boxes-stacked"></i> Real-time Key Stock
        </div>
        <div class="stock-item">
            <span>🎯 Aim Carrom King (3d / 7d / 30d)</span>
            <span class="stock-count">In Stock</span>
        </div>
        <div class="stock-item">
            <span>🐍 Snake Engine (Carrom / 8 Ball)</span>
            <span class="stock-count">In Stock</span>
        </div>
        <div class="stock-item">
            <span>🎮 KOS VIP Loader</span>
            <span class="stock-count">In Stock</span>
        </div>
        <div class="stock-item">
            <span>🤖 Aim Ai Engine Mod</span>
            <span class="stock-count">In Stock</span>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div class="modal-overlay" id="checkout_modal">
        <div class="modal-sheet">
            <div class="modal-title">
                <span id="modal_product_title">Product Name</span>
                <button class="btn-close-modal" onclick="closeModal()">&times;</button>
            </div>
            
            <div class="duration-options">
                <div class="opt-btn active" onclick="selectDuration(this, '3d', 250)">
                    <span>3 Days License</span>
                    <span>₹250</span>
                </div>
                <div class="opt-btn" onclick="selectDuration(this, '7d', 450)">
                    <span>1 Week License</span>
                    <span>₹450</span>
                </div>
                <div class="opt-btn" onclick="selectDuration(this, '30d', 1050)">
                    <span>1 Month License</span>
                    <span>₹1,050</span>
                </div>
                <div class="opt-btn" onclick="selectDuration(this, '90d', 2600)">
                    <span>3 Months VIP</span>
                    <span>₹2,600</span>
                </div>
            </div>

            <button class="btn-checkout" onclick="confirmPurchase()">
                Confirm & Get Key
            </button>
        </div>
    </div>

    <script>
        // Telegram WebApp Initialization
        const tg = window.Telegram?.WebApp;
        if (tg) {
            tg.ready();
            tg.expand();

            const user = tg.initDataUnsafe?.user;
            if (user) {
                document.getElementById('user_name').innerText = `${user.first_name || 'User'} ${user.last_name || ''}`;
                document.getElementById('user_id').innerText = `@${user.username || user.id}`;
                document.getElementById('user_avatar').innerText = (user.first_name || 'U').charAt(0).toUpperCase();
            }
        }

        let selectedProduct = '';
        let selectedPrice = 250;
        let selectedDuration = '3d';

        function openProductModal(pName) {
            selectedProduct = pName;
            document.getElementById('modal_product_title').innerText = pName;
            document.getElementById('checkout_modal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('checkout_modal').classList.remove('active');
        }

        function selectDuration(elem, dur, price) {
            document.querySelectorAll('.opt-btn').forEach(b => b.classList.remove('active'));
            elem.classList.add('active');
            selectedDuration = dur;
            selectedPrice = price;
        }

        function confirmPurchase() {
            closeModal();
            if (tg) {
                tg.sendData(JSON.stringify({
                    product: selectedProduct,
                    duration: selectedDuration,
                    price: selectedPrice
                }));
                tg.close();
            } else {
                alert(`🎉 Simulated Purchase Successful!\nProduct: ${selectedProduct}\nDuration: ${selectedDuration}\nPrice: ₹${selectedPrice}`);
            }
        }

        function scrollToStock() {
            document.getElementById('stock_section').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>
