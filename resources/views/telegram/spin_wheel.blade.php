<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lucky Wheel of Fortune - Khelo Store</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&family=Rajdhani:wght@600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome & Canvas Confetti -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>
        :root {
            --bg-primary: #0a0e17;
            --bg-card: rgba(18, 26, 43, 0.85);
            --neon-gold: #f59e0b;
            --neon-cyan: #06b6d4;
            --neon-purple: #8b5cf6;
            --neon-pink: #ec4899;
            --neon-green: #10b981;
            --text-primary: #ffffff;
            --text-secondary: #94a3b8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            user-select: none;
            -webkit-user-select: none;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background: radial-gradient(circle at 50% 20%, #1e1b4b 0%, #0a0e17 70%, #030712 100%);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            padding: 16px;
            overflow-x: hidden;
        }

        /* Top Header Card */
        .header-card {
            width: 100%;
            max-width: 420px;
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ec4899, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            border: 2px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 15px rgba(236, 72, 153, 0.4);
        }

        .user-name {
            font-size: 15px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.2;
        }

        .user-tag {
            font-size: 11px;
            color: var(--neon-cyan);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .balance-badge {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.05));
            border: 1px solid rgba(245, 158, 11, 0.4);
            border-radius: 14px;
            padding: 6px 14px;
            text-align: right;
        }

        .balance-label {
            font-size: 10px;
            color: #fbbf24;
            font-weight: 700;
            text-transform: uppercase;
        }

        .balance-val {
            font-family: 'Rajdhani', sans-serif;
            font-size: 18px;
            font-weight: 800;
            color: #ffffff;
        }

        /* Title Area */
        .title-area {
            text-align: center;
            margin: 8px 0;
        }

        .title-area h1 {
            font-family: 'Rajdhani', sans-serif;
            font-size: 28px;
            font-weight: 900;
            letter-spacing: 1px;
            background: linear-gradient(135deg, #ffffff 0%, #fbbf24 50%, #f59e0b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
            text-shadow: 0 0 25px rgba(245, 158, 11, 0.3);
        }

        .title-area p {
            font-size: 12px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* Wheel Container */
        .wheel-container {
            position: relative;
            width: 320px;
            height: 320px;
            margin: 8px 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .wheel-rim {
            position: absolute;
            width: 324px;
            height: 324px;
            border-radius: 50%;
            border: 6px solid #1e293b;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.7), inset 0 0 12px rgba(0, 0, 0, 0.9);
            z-index: 2;
            pointer-events: none;
        }

        /* Pointer */
        .pointer-container {
            position: absolute;
            top: -16px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.7));
            transition: transform 0.1s ease;
        }

        .pointer-arrow {
            width: 32px;
            height: 42px;
            background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%);
            clip-path: polygon(50% 100%, 0 0, 100% 0);
            border-radius: 4px;
        }

        /* Canvas Wheel */
        #wheelCanvas {
            width: 312px;
            height: 312px;
            border-radius: 50%;
            z-index: 3;
            transition: transform cubic-bezier(0.15, 0.99, 0.25, 1);
        }

        /* Center Spin Button */
        .center-spin-btn {
            position: absolute;
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
            border: 4px solid #ffffff;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.6), inset 0 2px 4px rgba(255, 255, 255, 0.6);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 5;
            transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .center-spin-btn:hover {
            transform: scale(1.06);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.8);
        }

        .center-spin-btn:active {
            transform: scale(0.92);
        }

        .center-spin-btn span {
            font-family: 'Rajdhani', sans-serif;
            font-size: 16px;
            font-weight: 900;
            color: #ffffff;
            letter-spacing: 1px;
            line-height: 1;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
        }

        .center-spin-btn small {
            font-size: 8px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.9);
            text-transform: uppercase;
        }

        /* Cooldown Notice Box */
        .cooldown-box {
            width: 100%;
            max-width: 420px;
            background: var(--bg-card);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 18px;
            padding: 14px 18px;
            text-align: center;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
            display: none;
        }

        .cooldown-timer {
            font-family: 'Rajdhani', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: #fbbf24;
            letter-spacing: 1px;
            margin: 4px 0;
        }

        /* Bottom Actions */
        .bottom-actions {
            width: 100%;
            max-width: 420px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 10px;
        }

        .btn-action {
            width: 100%;
            padding: 14px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-spin-main {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5);
            font-size: 15px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .btn-spin-main:disabled {
            background: #334155;
            color: #94a3b8;
            box-shadow: none;
            cursor: not-allowed;
        }

        .btn-refer {
            background: rgba(6, 182, 212, 0.12);
            color: var(--neon-cyan);
            border: 1px solid rgba(6, 182, 212, 0.4);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .btn-refer:hover {
            background: rgba(6, 182, 212, 0.25);
        }

        .btn-close {
            background: rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            font-size: 14px;
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-close:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
        }

        /* Win Modal Overlay */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100;
            padding: 20px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .win-card {
            width: 100%;
            max-width: 360px;
            background: linear-gradient(180deg, #1e1b4b 0%, #0f172a 100%);
            border: 2px solid #fbbf24;
            border-radius: 24px;
            padding: 28px 20px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.9);
            animation: popUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes popUp {
            from { transform: scale(0.7); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .win-icon {
            font-size: 54px;
            margin-bottom: 12px;
            animation: bounce 1s infinite alternate;
        }

        @keyframes bounce {
            from { transform: translateY(0); }
            to { transform: translateY(-10px); }
        }

        .win-title {
            font-family: 'Rajdhani', sans-serif;
            font-size: 26px;
            font-weight: 900;
            color: #ffffff;
            text-transform: uppercase;
        }

        .win-amount {
            font-family: 'Rajdhani', sans-serif;
            font-size: 44px;
            font-weight: 900;
            color: #fbbf24;
            margin: 6px 0 14px 0;
        }

        .win-desc {
            font-size: 13px;
            color: #cbd5e1;
            margin-bottom: 20px;
            line-height: 1.4;
        }
    </style>
</head>
<body>

    <!-- Header User Card -->
    <div class="header-card">
        <div class="user-info">
            <div class="avatar" id="userAvatar">
                {{ substr($user->first_name ?? 'U', 0, 1) }}
            </div>
            <div>
                <div class="user-name" id="userName">{{ $user->first_name ?? 'Player' }}</div>
                <div class="user-tag">VIP Reseller</div>
            </div>
        </div>
        <div class="balance-badge">
            <div class="balance-label">Wallet Balance</div>
            <div class="balance-val" id="walletBalanceDisplay">₹{{ number_format($user->balance, 2) }}</div>
        </div>
    </div>

    <!-- Title Area -->
    <div class="title-area">
        <h1>Lucky Wheel</h1>
        <p>Spin daily to win real wallet balance!</p>
    </div>

    <!-- Wheel Visual -->
    <div class="wheel-container">
        <div class="wheel-rim"></div>
        <div class="pointer-container" id="wheelPointer">
            <div class="pointer-arrow"></div>
        </div>
        <canvas id="wheelCanvas" width="600" height="600"></canvas>
        <div class="center-spin-btn" id="centerSpinBtn" onclick="triggerSpin()">
            <span>SPIN</span>
            <small>Free</small>
        </div>
    </div>

    <!-- Cooldown Box (Hidden in test mode) -->
    <div class="cooldown-box" id="cooldownBox">
        <div style="font-size: 13px; color: var(--text-secondary);">Today's Free Spin Used</div>
        <div class="cooldown-timer" id="cooldownTimerDisplay">{{ $user->formattedTimeUntilNextSpin() }}</div>
        <div style="font-size: 11px; color: #38bdf8;">Come back daily to win free wallet balance!</div>
    </div>

    <!-- Bottom Action Buttons -->
    <div class="bottom-actions">
        <button class="btn-action btn-spin-main" id="mainSpinBtn" onclick="triggerSpin()">
            <i class="fa-solid fa-dice"></i> Spin the Wheel
        </button>
        <a href="https://t.me/{{ env('TELEGRAM_BOT_USERNAME', 'YourBot') }}" class="btn-action btn-close" onclick="closeMiniApp(event)">
            <i class="fa-solid fa-arrow-left"></i> Return to Telegram Chat
        </a>
    </div>

    <!-- Win Modal Popup -->
    <div class="modal-overlay" id="winModal">
        <div class="win-card">
            <div class="win-icon" id="winModalIcon">🎉</div>
            <div class="win-title" id="winModalTitle">CONGRATULATIONS!</div>
            <div class="win-amount" id="winModalAmount">+₹10.00</div>
            <div class="win-desc">Your prize has been credited directly to your Khelo Store Wallet! 🚀</div>
            <button class="btn-action btn-spin-main" onclick="closeWinModal()">
                <i class="fa-solid fa-check"></i> Great! Claim Reward
            </button>
        </div>
    </div>

    <script>
        const tg = window.Telegram?.WebApp;
        if (tg) {
            try {
                tg.expand();
                tg.enableClosingConfirmation();
                if (tg.initDataUnsafe?.user) {
                    const tgUser = tg.initDataUnsafe.user;
                    if (tgUser.first_name) {
                        document.getElementById('userName').innerText = tgUser.first_name;
                        document.getElementById('userAvatar').innerText = tgUser.first_name.charAt(0).toUpperCase();
                    }
                }
            } catch (e) {}
        }

        // Wheel Segments Config (8 segments matching realistic weighted odds)
        const segments = [
            { label: "₹50", sub: "JACKPOT", color: "#ec4899", textColor: "#ffffff", amount: 50, icon: "💎" },
            { label: "₹2", sub: "Lucky", color: "#3b82f6", textColor: "#ffffff", amount: 2, icon: "🎁" },
            { label: "₹3", sub: "Bonus", color: "#10b981", textColor: "#ffffff", amount: 3, icon: "✨" },
            { label: "₹5", sub: "Power", color: "#8b5cf6", textColor: "#ffffff", amount: 5, icon: "⚡" },
            { label: "₹7", sub: "Super", color: "#f59e0b", textColor: "#ffffff", amount: 7, icon: "🔥" },
            { label: "₹10", sub: "Mega", color: "#06b6d4", textColor: "#ffffff", amount: 10, icon: "🧿" },
            { label: "₹15", sub: "VIP", color: "#6366f1", textColor: "#ffffff", amount: 15, icon: "👑" },
            { label: "₹25", sub: "Grand", color: "#14b8a6", textColor: "#ffffff", amount: 25, icon: "🚀" }
        ];

        const canvas = document.getElementById('wheelCanvas');
        const ctx = canvas.getContext('2d');
        const numSegments = segments.length;
        const arc = (2 * Math.PI) / numSegments;
        let currentRotation = 0;
        let isSpinning = false;
        let canSpin = {{ $user->canSpinToday() ? 'true' : 'false' }};
        let secondsLeft = {{ $user->secondsUntilNextSpin() }};
        let timerInterval = null;

        // Draw High-Resolution Wheel
        function drawWheel() {
            const centerX = canvas.width / 2;
            const centerY = canvas.height / 2;
            const radius = centerX - 10;

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            for (let i = 0; i < numSegments; i++) {
                const angle = i * arc;
                const seg = segments[i];

                // Slice Background
                ctx.beginPath();
                ctx.fillStyle = seg.color;
                ctx.moveTo(centerX, centerY);
                ctx.arc(centerX, centerY, radius, angle, angle + arc);
                ctx.lineTo(centerX, centerY);
                ctx.fill();

                // Slice Border
                ctx.lineWidth = 3;
                ctx.strokeStyle = '#0f172a';
                ctx.stroke();

                // Slice Text / Icons
                ctx.save();
                ctx.translate(centerX, centerY);
                ctx.rotate(angle + arc / 2);
                ctx.textAlign = "right";
                ctx.fillStyle = seg.textColor;
                
                // Icon & Amount
                ctx.font = "bold 32px 'Rajdhani', sans-serif";
                ctx.fillText(seg.label, radius - 30, 6);

                ctx.font = "bold 16px 'Outfit', sans-serif";
                ctx.fillStyle = "rgba(255, 255, 255, 0.85)";
                ctx.fillText(seg.sub, radius - 32, 24);

                ctx.restore();
            }

            // Outer decorative pins
            for (let i = 0; i < numSegments * 2; i++) {
                const pinAngle = i * (Math.PI / numSegments);
                const pinX = centerX + (radius - 4) * Math.cos(pinAngle);
                const pinY = centerY + (radius - 4) * Math.sin(pinAngle);

                ctx.beginPath();
                ctx.arc(pinX, pinY, 4, 0, 2 * Math.PI);
                ctx.fillStyle = "#ffffff";
                ctx.fill();
            }
        }

        drawWheel();

        function formatTime(totalSeconds) {
            if (totalSeconds <= 0) return "Ready!";
            const h = Math.floor(totalSeconds / 3600);
            const m = Math.floor((totalSeconds % 3600) / 60);
            const s = totalSeconds % 60;
            return `${String(h).padStart(2, '0')}h ${String(m).padStart(2, '0')}m ${String(s).padStart(2, '0')}s`;
        }

        function startCountdown() {
            if (timerInterval) clearInterval(timerInterval);
            timerInterval = setInterval(() => {
                if (secondsLeft > 0) {
                    secondsLeft--;
                    const timerEl = document.getElementById('cooldownTimerDisplay');
                    if (timerEl) timerEl.innerText = formatTime(secondsLeft);
                    const mainBtn = document.getElementById('mainSpinBtn');
                    if (mainBtn && !canSpin) {
                        mainBtn.innerHTML = `<i class="fa-solid fa-lock"></i> Next Spin: ${formatTime(secondsLeft)}`;
                    }
                } else {
                    canSpin = true;
                    clearInterval(timerInterval);
                    updateUIState();
                }
            }, 1000);
        }

        // Update UI State (Clean Professional Locking without alerts)
        function updateUIState() {
            const mainBtn = document.getElementById('mainSpinBtn');
            const centerBtn = document.getElementById('centerSpinBtn');
            const cooldownBox = document.getElementById('cooldownBox');

            if (canSpin) {
                cooldownBox.style.display = 'none';
                mainBtn.disabled = false;
                mainBtn.style.background = 'linear-gradient(135deg, #f59e0b, #d97706)';
                mainBtn.style.color = '#ffffff';
                mainBtn.style.cursor = 'pointer';
                mainBtn.style.boxShadow = '0 4px 14px rgba(0, 0, 0, 0.5)';
                mainBtn.innerHTML = '<i class="fa-solid fa-dice"></i> Spin the Wheel';

                centerBtn.style.opacity = '1';
                centerBtn.style.pointerEvents = 'auto';
                centerBtn.style.cursor = 'pointer';
                centerBtn.innerHTML = '<span>SPIN</span><small>Free</small>';
            } else {
                cooldownBox.style.display = 'block';
                const timerEl = document.getElementById('cooldownTimerDisplay');
                if (timerEl) timerEl.innerText = formatTime(secondsLeft);

                mainBtn.disabled = true;
                mainBtn.style.background = 'linear-gradient(135deg, #1e293b, #0f172a)';
                mainBtn.style.color = '#94a3b8';
                mainBtn.style.cursor = 'not-allowed';
                mainBtn.style.boxShadow = 'none';
                mainBtn.style.border = '1px solid rgba(255, 255, 255, 0.1)';
                mainBtn.innerHTML = `<i class="fa-solid fa-lock"></i> Next Spin: ${formatTime(secondsLeft)}`;

                centerBtn.style.opacity = '0.45';
                centerBtn.style.pointerEvents = 'none';
                centerBtn.style.cursor = 'not-allowed';
                centerBtn.innerHTML = '<span style="font-size:13px;"><i class="fa-solid fa-lock"></i></span><small>Used</small>';

                startCountdown();
            }
        }

        updateUIState();

        // Spin Action
        async function triggerSpin() {
            if (!canSpin || isSpinning) return;
            isSpinning = true;

            if (tg && tg.HapticFeedback) {
                try { tg.HapticFeedback.impactOccurred('medium'); } catch(e) {}
            }

            // Disable buttons during spin
            const mainBtn = document.getElementById('mainSpinBtn');
            mainBtn.disabled = true;
            mainBtn.innerText = "Spinning...";

            try {
                const response = await fetch("{{ route('telegram.spin-wheel.api') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        telegram_id: "{{ $user->telegram_id }}",
                        initData: tg ? tg.initData : ''
                    })
                });

                const data = await response.json();

                if (!data.success) {
                    canSpin = false;
                    isSpinning = false;
                    secondsLeft = 86400;
                    updateUIState();
                    return;
                }

                const targetSlice = data.winning_slice !== undefined ? data.winning_slice : 1;
                const wonAmount = data.amount;
                const newBalance = data.new_balance;

                // Calculate target angle to align pointer (top center = 270 deg or 1.5 * PI)
                const degreesPerSlice = 360 / numSegments;
                const sliceMiddleDeg = (targetSlice * degreesPerSlice) + (degreesPerSlice / 2);
                const targetDegree = (270 - sliceMiddleDeg + 360) % 360;

                // Add 5-7 full rotations for realistic spin duration
                const fullSpins = (5 + Math.floor(Math.random() * 2)) * 360;
                const totalRotation = currentRotation + fullSpins + (targetDegree - (currentRotation % 360) + 360) % 360;

                currentRotation = totalRotation;

                canvas.style.transition = "transform 4.5s cubic-bezier(0.15, 0.99, 0.25, 1)";
                canvas.style.transform = `rotate(${totalRotation}deg)`;

                // Pointer tick bounce animation
                const tickInterval = setInterval(() => {
                    const pointer = document.getElementById('wheelPointer');
                    pointer.style.transform = 'translateX(-50%) rotate(' + (Math.random() * 12 - 6) + 'deg)';
                }, 100);

                setTimeout(() => {
                    clearInterval(tickInterval);
                    document.getElementById('wheelPointer').style.transform = 'translateX(-50%) rotate(0deg)';
                    isSpinning = false;
                    canSpin = false;
                    secondsLeft = 86400; // 24 hours lock

                    // Update balance
                    document.getElementById('walletBalanceDisplay').innerText = '₹' + parseFloat(newBalance).toFixed(2);
                    updateUIState();

                    // Trigger Confetti Blast
                    confetti({
                        particleCount: 100,
                        spread: 70,
                        origin: { y: 0.6 }
                    });

                    if (tg && tg.HapticFeedback) {
                        try { tg.HapticFeedback.notificationOccurred('success'); } catch(e) {}
                    }

                    // Show Win Modal
                    document.getElementById('winModalAmount').innerText = '+₹' + parseFloat(wonAmount).toFixed(2);
                    document.getElementById('winModalTitle').innerText = wonAmount >= 50 ? '💎 JACKPOT WINNER! 💎' : '🎉 CONGRATULATIONS!';
                    document.getElementById('winModal').style.display = 'flex';

                    // Play Meme Audio Sound in WebApp
                    try {
                        const winAudio = new Audio('/audio/paisa_hi_paisa.mp3');
                        winAudio.play().catch(e => console.log('Audio playback info', e));
                    } catch(e) {}

                    // Trigger Telegram Bot chat notification & voice note AFTER wheel stops spinning!
                    fetch("{{ route('telegram.spin-wheel.notify') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            telegram_id: "{{ $user->telegram_id }}",
                            amount: wonAmount,
                            label: data.label
                        })
                    }).catch(e => console.log('Notify error', e));

                }, 4600);

            } catch (err) {
                console.error("Spin error:", err);
                isSpinning = false;
                canSpin = false;
                updateUIState();
            }
        }

        function closeWinModal() {
            document.getElementById('winModal').style.display = 'none';
            canSpin = false;
            isSpinning = false;
            updateUIState();
        }

        function openReferralInfo(e) {
            if (e) e.preventDefault();

            try {
                if (window.Telegram && window.Telegram.WebApp && window.Telegram.WebApp.initData) {
                    window.Telegram.WebApp.close();
                    return;
                }
            } catch (err) {}

            window.location.href = "https://t.me/{{ env('TELEGRAM_BOT_USERNAME', 'YourBot') }}?start=refer";
        }

        function closeMiniApp(e) {
            if (e) e.preventDefault();

            // 1. If inside native Telegram Mini App container
            try {
                if (window.Telegram && window.Telegram.WebApp && window.Telegram.WebApp.initData) {
                    window.Telegram.WebApp.close();
                    return;
                }
            } catch (err) {}

            // 2. Try closing window if opened as popup
            try {
                window.close();
            } catch (err) {}

            // 3. Fallback: redirect back to Telegram Bot chat
            window.location.href = "https://t.me/{{ env('TELEGRAM_BOT_USERNAME', 'YourBot') }}";
        }
    </script>
</body>
</html>
