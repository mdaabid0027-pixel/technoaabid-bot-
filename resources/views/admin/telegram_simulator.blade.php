@extends('admin.layout')

@section('title', 'Telegram Bot Simulator')
@section('header_title', 'Telegram Bot Simulator')

@section('styles')
<style>
    /* Main Layout */
    .simulator-container {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 30px;
        height: calc(100vh - 180px);
        min-height: 550px;
    }

    /* Left Control Sidebar */
    .control-sidebar {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .config-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
    }

    .config-title {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 16px;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .config-title i {
        color: var(--primary);
    }

    .user-select-container {
        position: relative;
        margin-bottom: 20px;
    }

    .user-profile-details {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px dashed var(--border-color);
    }

    .detail-item {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
    }

    .detail-label {
        color: var(--text-secondary);
    }

    .detail-value {
        font-weight: 600;
        color: var(--text-primary);
    }

    .balance-badge {
        color: var(--success);
        background: rgba(16, 185, 129, 0.1);
        padding: 2px 8px;
        border-radius: 6px;
        font-weight: 700;
    }

    /* Telegram Chat Window */
    .chat-window {
        background: #f3f7fa;
        border: 1px solid var(--border-color);
        border-radius: 20px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
    }

    /* Chat Header */
    .chat-header {
        background: #2481cc;
        color: #ffffff;
        padding: 16px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        z-index: 10;
    }

    .chat-bot-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .bot-avatar {
        width: 40px;
        height: 40px;
        background: #ffffff;
        color: #2481cc;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: bold;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .bot-status-container {
        display: flex;
        flex-direction: column;
    }

    .bot-name {
        font-weight: 600;
        font-size: 16px;
    }

    .bot-status {
        font-size: 12px;
        opacity: 0.85;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .bot-status::before {
        content: '';
        display: inline-block;
        width: 6px;
        height: 6px;
        background-color: #2ec4b6;
        border-radius: 50%;
    }

    .chat-actions {
        display: flex;
        gap: 8px;
    }

    .btn-chat-action {
        background: rgba(255, 255, 255, 0.15);
        border: none;
        color: #ffffff;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-chat-action:hover {
        background: rgba(255, 255, 255, 0.25);
    }

    /* Chat Messages Box */
    .chat-messages {
        flex-grow: 1;
        padding: 24px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 16px;
        background-image: radial-gradient(#d1e1f0 1.2px, #eef4f9 1.2px);
        background-size: 24px 24px;
    }

    /* Date Dividers */
    .date-divider {
        align-self: center;
        background: rgba(0, 0, 0, 0.08);
        color: rgba(0, 0, 0, 0.6);
        font-size: 12px;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 12px;
        margin: 8px 0;
    }

    /* Chat Bubbles */
    .msg-bubble {
        min-width: 340px;
        max-width: 88%;
        padding: 14px 18px;
        border-radius: 14px;
        font-size: 14.5px;
        line-height: 1.6;
        position: relative;
        word-break: break-word;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07);
        display: flex;
        flex-direction: column;
    }

    .msg-user {
        align-self: flex-end;
        background: #e1f3fc;
        color: #0f172a;
        border-bottom-right-radius: 2px;
    }

    .msg-bot {
        align-self: flex-start;
        background: #ffffff;
        color: #0f172a;
        border-bottom-left-radius: 2px;
    }

    .msg-time {
        font-size: 10px;
        color: var(--text-secondary);
        align-self: flex-end;
        margin-top: 6px;
        opacity: 0.8;
    }

    /* Inline Keyboards */
    .inline-keyboard {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 12px;
        width: 100%;
        max-width: 100%;
    }

    .keyboard-row {
        display: flex;
        gap: 8px;
        width: 100%;
    }

    .btn-inline {
        flex: 1;
        background: #eef6fc;
        border: 1px solid rgba(36, 129, 204, 0.2);
        color: #2481cc;
        padding: 12px 18px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s ease;
        text-align: center;
        box-shadow: 0 1px 3px rgba(36, 129, 204, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
    }

    .btn-inline:hover {
        background: #2481cc;
        color: #ffffff;
        border-color: #2481cc;
    }

    /* Chat Input Area */
    .chat-input-bar {
        background: #ffffff;
        border-top: 1px solid var(--border-color);
        padding: 16px 24px;
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .chat-input-field {
        flex-grow: 1;
        border: 1px solid #cbd5e1;
        padding: 12px 18px;
        border-radius: 24px;
        font-size: 15px;
        outline: none;
        transition: all 0.2s ease;
    }

    .chat-input-field:focus {
        border-color: #2481cc;
        box-shadow: 0 0 0 3px rgba(36, 129, 204, 0.15);
    }

    .btn-send {
        width: 44px;
        height: 44px;
        background: #2481cc;
        color: #ffffff;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px var(--primary-glow);
    }

    .btn-send:hover {
        background: #1b6dae;
        transform: scale(1.05);
    }

    .btn-menu-bar {
        width: 44px;
        height: 44px;
        background: #2481cc;
        color: #ffffff;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .btn-menu-bar:hover {
        background: #1b6dae;
        transform: scale(1.05);
    }

    .cmd-popup-menu {
        display: none;
        position: absolute;
        bottom: 75px;
        left: 24px;
        width: 280px;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(0, 0, 0, 0.08);
        overflow: hidden;
        z-index: 100;
        animation: popupFade 0.2s ease;
    }

    @keyframes popupFade {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .cmd-popup-item {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .cmd-popup-item:last-child {
        border-bottom: none;
    }

    .cmd-popup-item:hover {
        background: #f0f9ff;
    }

    .cmd-popup-title {
        font-weight: 700;
        font-size: 14px;
        color: #2481cc;
    }

    .cmd-popup-desc {
        font-size: 12px;
        color: #64748b;
        margin-top: 2px;
    }

    /* Simulator Overlay */
    .loading-bubble {
        align-self: flex-start;
        background: #ffffff;
        padding: 10px 16px;
        border-radius: 14px;
        border-bottom-left-radius: 2px;
        display: flex;
        align-items: center;
        gap: 4px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .loading-dot {
        width: 6px;
        height: 6px;
        background: var(--text-secondary);
        border-radius: 50%;
        animation: loadingPulse 1s infinite alternate;
    }

    .loading-dot:nth-child(2) {
        animation-delay: 0.2s;
    }

    .loading-dot:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes loadingPulse {
        0% { opacity: 0.3; transform: scale(0.8); }
        100% { opacity: 1; transform: scale(1.2); }
    }

    /* Custom Reseller Modal Form */
    .custom-user-form {
        display: none;
        padding-top: 16px;
        margin-top: 16px;
        border-top: 1px solid var(--border-color);
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Mobile View Optimization */
    @media (max-width: 768px) {
        .simulator-container {
            grid-template-columns: 1fr;
            gap: 16px;
            height: auto;
            min-height: auto;
        }

        .chat-window {
            height: 600px;
            border-radius: 16px;
        }

        .chat-messages {
            padding: 16px;
        }

        .msg-bubble {
            max-width: 88%;
        }

        .keyboard-row {
            flex-wrap: wrap;
        }

        .btn-inline {
            flex: 1 1 calc(50% - 6px);
            min-width: 100px;
            padding: 8px 10px;
            font-size: 12px;
        }

        .cmd-popup-menu {
            left: 12px;
            right: 12px;
            width: auto;
        }
    }
</style>
@endsection

@section('content')
<div class="simulator-container">
    
    <!-- Left Configuration Sidebar -->
    <div class="control-sidebar">
        
        <!-- User Selection Card -->
        <div class="config-card">
            <div class="config-title">
                <i class="fa-solid fa-user-gear"></i>
                <span>Simulated Reseller</span>
            </div>
            
            <div class="user-select-container">
                <label for="user_select" class="detail-label" style="display:block; margin-bottom:8px;">Select Reseller Profile</label>
                <select id="user_select" class="form-control">
                    <option value="">-- Select Reseller --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" 
                                data-telegram-id="{{ $u->telegram_id }}"
                                data-first-name="{{ $u->first_name }}"
                                data-last-name="{{ $u->last_name }}"
                                data-username="{{ $u->username }}">
                            {{ $u->first_name }} {{ $u->last_name }} ({{ $u->username ?: 'No username' }})
                        </option>
                    @endforeach
                    <option value="new_user">+ Create New Simulator User</option>
                </select>
            </div>

            <!-- New User Configuration Form -->
            <div id="new_user_form" class="custom-user-form">
                <div class="form-group">
                    <label for="new_first_name">First Name</label>
                    <input type="text" id="new_first_name" class="form-control" placeholder="John">
                </div>
                <div class="form-group">
                    <label for="new_last_name">Last Name</label>
                    <input type="text" id="new_last_name" class="form-control" placeholder="Doe">
                </div>
                <div class="form-group">
                    <label for="new_username">Username</label>
                    <input type="text" id="new_username" class="form-control" placeholder="johndoe_bot">
                </div>
                <div class="form-group">
                    <label for="new_telegram_id">Telegram ID (Randomly Generated)</label>
                    <input type="number" id="new_telegram_id" class="form-control" readonly>
                </div>
                <button type="button" id="btn_create_user" class="btn btn-primary" style="width:100%;">
                    <i class="fa-solid fa-user-plus"></i> Simulate Start
                </button>
            </div>

            <!-- Profile Details Display -->
            <div id="profile_details" style="display: none;">
                <div class="user-profile-details">
                    <div class="detail-item">
                        <span class="detail-label">First Name:</span>
                        <span class="detail-value" id="disp_first_name">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Telegram ID:</span>
                        <span class="detail-value" id="disp_telegram_id">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Wallet Balance:</span>
                        <span class="detail-value"><span class="balance-badge" id="disp_balance">$0.00</span></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Reseller Rank:</span>
                        <span class="detail-value" id="disp_rank">Bronze</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Keys Purchased:</span>
                        <span class="detail-value" id="disp_keys">0</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Lifetime Spend:</span>
                        <span class="detail-value" id="disp_spend">$0.00</span>
                    </div>
                </div>

                <!-- Admin Action Topup -->
                <div style="margin-top: 20px; padding-top: 16px; border-top: 1px dashed var(--border-color);">
                    <label class="detail-label" style="display:block; margin-bottom:8px;">Admin Actions</label>
                    <button type="button" id="btn_quick_credit" class="btn btn-primary" style="width:100%; background: var(--success); color:#fff;">
                        <i class="fa-solid fa-wallet"></i> Quick Add $50.00
                    </button>
                </div>
            </div>

        </div>

    </div>

    <!-- Right Chat Window -->
    <div class="chat-window">
        
        <!-- Header -->
        <div class="chat-header">
            <div class="chat-bot-info">
                <div class="bot-avatar">KS</div>
                <div class="bot-status-container">
                    <span class="bot-name">Khelo Store Bot</span>
                    <span class="bot-status">online</span>
                </div>
            </div>
            <div class="chat-actions">
                <button type="button" class="btn-chat-action" id="btn_reset_chat">
                    <i class="fa-solid fa-rotate-left"></i> Reset Chat
                </button>
                <button type="button" class="btn-chat-action" id="btn_start_bot">
                    <i class="fa-solid fa-play"></i> /start
                </button>
            </div>
        </div>

        <!-- Message History -->
        <div class="chat-messages" id="chat_messages">
            <div class="date-divider">Today</div>
            
            <div class="msg-bubble msg-bot">
                👋 Select a reseller profile from the left sidebar to start simulating the Telegram bot!
                <div class="msg-time">System</div>
            </div>
        </div>

        <!-- Input Bar -->
        <div class="chat-input-bar" style="position: relative;">
            <button type="button" id="btn_menu_popup" class="btn-menu-bar" title="Bot Commands Menu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <!-- Telegram Commands Popup Menu -->
            <div id="cmd_popup_menu" class="cmd-popup-menu">
                <div class="cmd-popup-item" data-cmd="/start">
                    <div class="cmd-popup-title">/start</div>
                    <div class="cmd-popup-desc">Panel Restart 🔄</div>
                </div>
                <div class="cmd-popup-item" data-cmd="/download">
                    <div class="cmd-popup-title">/download</div>
                    <div class="cmd-popup-desc">All App Download 📥</div>
                </div>
                <div class="cmd-popup-item" data-cmd="/pricelist">
                    <div class="cmd-popup-title">/pricelist</div>
                    <div class="cmd-popup-desc">Check All Prices 💵</div>
                </div>
            </div>

            <input type="text" id="chat_input" class="chat-input-field" placeholder="Type a message or command (e.g. /start)..." disabled>
            <button type="button" id="btn_send_message" class="btn-send" disabled>
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Elements
        const userSelect = document.getElementById('user_select');
        const newUserForm = document.getElementById('new_user_form');
        const profileDetails = document.getElementById('profile_details');
        const btnCreateUser = document.getElementById('btn_create_user');
        
        const newFirstName = document.getElementById('new_first_name');
        const newLastName = document.getElementById('new_last_name');
        const newUsername = document.getElementById('new_username');
        const newTelegramId = document.getElementById('new_telegram_id');

        const dispFirstName = document.getElementById('disp_first_name');
        const dispTelegramId = document.getElementById('disp_telegram_id');
        const dispBalance = document.getElementById('disp_balance');
        const dispRank = document.getElementById('disp_rank');
        const dispKeys = document.getElementById('disp_keys');
        const dispSpend = document.getElementById('disp_spend');
        
        const btnQuickCredit = document.getElementById('btn_quick_credit');
        const btnResetChat = document.getElementById('btn_reset_chat');
        const btnStartBot = document.getElementById('btn_start_bot');

        const chatMessages = document.getElementById('chat_messages');
        const chatInput = document.getElementById('chat_input');
        const btnSendMessage = document.getElementById('btn_send_message');
        const btnMenuPopup = document.getElementById('btn_menu_popup');
        const cmdPopupMenu = document.getElementById('cmd_popup_menu');

        if (btnMenuPopup && cmdPopupMenu) {
            btnMenuPopup.addEventListener('click', (e) => {
                e.stopPropagation();
                cmdPopupMenu.style.display = (cmdPopupMenu.style.display === 'block') ? 'none' : 'block';
            });

            document.addEventListener('click', () => {
                cmdPopupMenu.style.display = 'none';
            });

            document.querySelectorAll('.cmd-popup-item').forEach(item => {
                item.addEventListener('click', () => {
                    const cmd = item.getAttribute('data-cmd');
                    if (cmd) {
                        sendTextMessage(cmd);
                    }
                    cmdPopupMenu.style.display = 'none';
                });
            });
        }

        // State Variables
        let currentUserId = null;
        let currentTelegramId = null;
        let currentUsername = null;
        let currentFirstName = null;
        let currentLastName = null;

        // CSRF Token header config
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Formats Telegram Bot Markdown to simple HTML
        function formatMarkdown(text) {
            if (!text) return '';
            // Basic HTML encoding to prevent XSS
            let escaped = text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;");
            
            // Markdown conversions
            escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            escaped = escaped.replace(/\*(.*?)\*/g, '<em>$1</em>');
            escaped = escaped.replace(/`(.*?)`/g, '<code style="background: rgba(0,0,0,0.06); padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 14px; font-weight: bold; border: 1px solid rgba(0,0,0,0.1);">$1</code>');
            escaped = escaped.replace(/\n/g, '<br>');
            return escaped;
        }

        // Add user chat bubble to screen
        function addUserMessage(text) {
            const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const bubble = document.createElement('div');
            bubble.className = 'msg-bubble msg-user';
            bubble.innerHTML = `${formatMarkdown(text)}<div class="msg-time">${time}</div>`;
            chatMessages.appendChild(bubble);
            scrollToBottom();
        }

        // Add bot chat bubble to screen with optional inline buttons
        function addBotMessage(text, replyMarkup) {
            const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const bubble = document.createElement('div');
            bubble.className = 'msg-bubble msg-bot';
            
            let messageHtml = `${formatMarkdown(text)}`;

            // Build Inline or Reply Keyboards if present
            const targetKeyboard = (replyMarkup && replyMarkup.keyboard) ? replyMarkup.keyboard : (replyMarkup && replyMarkup.inline_keyboard ? replyMarkup.inline_keyboard : null);
            const isReplyKb = replyMarkup && replyMarkup.keyboard;

            if (targetKeyboard) {
                const keyboardDiv = document.createElement('div');
                keyboardDiv.className = 'inline-keyboard';
                
                targetKeyboard.forEach(row => {
                    const rowDiv = document.createElement('div');
                    rowDiv.className = 'keyboard-row';
                    
                    row.forEach(button => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'btn-inline';
                        btn.innerText = button.text;

                        // Exact vibrant colors matching user's custom Telegram menu screenshot
                        if (button.text.includes('Yes')) {
                            btn.style.background = '#22c55e'; // Bright Green
                            btn.style.color = '#ffffff';
                            btn.style.border = 'none';
                        } else if (button.text.includes('No')) {
                            btn.style.background = '#ff3547'; // Bright Red
                            btn.style.color = '#ffffff';
                            btn.style.border = 'none';
                        } else if (button.text.includes('Aim Carrom')) {
                            btn.style.background = '#ff3547'; // Vibrant Red
                            btn.style.color = '#ffffff';
                            btn.style.border = 'none';
                        } else if (button.text.includes('Snake Engine') || button.text.includes('Snake')) {
                            btn.style.background = '#2b82d9'; // Telegram Blue
                            btn.style.color = '#ffffff';
                            btn.style.border = 'none';
                        } else if (button.text.includes('KOS') || button.text.includes('Aim Ai')) {
                            btn.style.background = '#22c55e'; // Bright Green
                            btn.style.color = '#ffffff';
                            btn.style.border = 'none';
                        } else if (button.text.includes('bitAIM')) {
                            btn.style.background = '#e2e8f0'; // Light Gray
                            btn.style.color = '#0f172a';
                            btn.style.border = 'none';
                        } else if (button.text.includes('Check Stock')) {
                            btn.style.background = '#2b82d9'; // Telegram Blue
                            btn.style.color = '#ffffff';
                            btn.style.border = 'none';
                        } else if (button.text.includes('Check Balance')) {
                            btn.style.background = '#ff3547'; // Red
                            btn.style.color = '#ffffff';
                            btn.style.border = 'none';
                        } else if (button.text.includes('Add Balance') || button.text.includes('Verify Payment')) {
                            btn.style.background = '#22c55e'; // Bright Green
                            btn.style.color = '#ffffff';
                            btn.style.border = 'none';
                        } else if (button.text.includes('8 Ball Pool')) {
                            btn.style.background = '#2b82d9'; // Blue
                            btn.style.color = '#ffffff';
                            btn.style.border = 'none';
                        } else if (button.text.includes('Carrom Pool')) {
                            btn.style.background = '#22c55e'; // Green
                            btn.style.color = '#ffffff';
                            btn.style.border = 'none';
                        } else if (button.text.includes('Soccer Star')) {
                            btn.style.background = '#ff3547'; // Red
                            btn.style.color = '#ffffff';
                            btn.style.border = 'none';
                        } else if (button.text.includes('Normal Mod')) {
                            btn.style.background = '#2b82d9'; // Blue
                            btn.style.color = '#ffffff';
                            btn.style.border = 'none';
                        } else if (button.text.includes('Queue Mod')) {
                            btn.style.background = '#22c55e'; // Green
                            btn.style.color = '#ffffff';
                            btn.style.border = 'none';
                        } else if (button.text === '3 Days' || button.text === '1 Week') {
                            btn.style.background = '#2b82d9'; // Blue
                            btn.style.color = '#ffffff';
                            btn.style.border = 'none';
                        } else if (button.text === '7 Days' || button.text === '10 Days' || button.text === '1 Month') {
                            btn.style.background = '#22c55e'; // Green
                            btn.style.color = '#ffffff';
                            btn.style.border = 'none';
                        } else if (button.text === '30 Days' || button.text === '3 Months') {
                            btn.style.background = '#ff3547'; // Red
                            btn.style.color = '#ffffff';
                            btn.style.border = 'none';
                        } else if (button.text === '90 Days' || button.text.includes('Life Time')) {
                            btn.style.background = '#2b82d9'; // Blue
                            btn.style.color = '#ffffff';
                            btn.style.border = 'none';
                        }

                        btn.addEventListener('click', () => {
                            if (isReplyKb) {
                                sendTextMessage(button.text);
                            } else {
                                triggerCallback(button.callback_data, button.text);
                            }
                        });
                        rowDiv.appendChild(btn);
                    });
                    
                    keyboardDiv.appendChild(rowDiv);
                });
                
                messageHtml += `<div class="msg-time" style="margin-top:10px;">${time}</div>`;
                bubble.innerHTML = messageHtml;
                bubble.appendChild(keyboardDiv);
            } else {
                messageHtml += `<div class="msg-time">${time}</div>`;
                bubble.innerHTML = messageHtml;
            }

            chatMessages.appendChild(bubble);
            scrollToBottom();
        }

        // Display typing status
        function showLoading() {
            const loader = document.createElement('div');
            loader.className = 'loading-bubble';
            loader.id = 'chat_loader';
            loader.innerHTML = '<div class="loading-dot"></div><div class="loading-dot"></div><div class="loading-dot"></div>';
            chatMessages.appendChild(loader);
            scrollToBottom();
        }

        // Hide typing status
        function hideLoading() {
            const loader = document.getElementById('chat_loader');
            if (loader) {
                loader.remove();
            }
        }

        // Scroll to the bottom of the message container
        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Refresh Left Details Panel
        function refreshUserProfile() {
            if (!currentUserId) return;
            
            fetch(`/admin/telegram-simulator/user/${currentUserId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        const u = data.user;
                        dispFirstName.innerText = u.first_name;
                        dispTelegramId.innerText = u.telegram_id;
                        dispBalance.innerText = `$${u.balance}`;
                        dispRank.innerText = u.rank;
                        dispKeys.innerText = u.keys_count;
                        dispSpend.innerText = `$${u.total_spend}`;
                    }
                });
        }

        // Trigger bot execution endpoint
        function sendWebhookPayload(payload) {
            showLoading();
            
            fetch('/admin/telegram-simulator/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Telegram-Simulate': 'true',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                hideLoading();
                if (data.status === 'success') {
                    // Iterate and display responses
                    data.responses.forEach(resp => {
                        if (resp.method === 'sendMessage') {
                            addBotMessage(resp.payload.text, resp.payload.reply_markup);
                        }
                    });
                    
                    // Refresh profile details since database might have changed
                    refreshUserProfile();
                }
            })
            .catch(err => {
                hideLoading();
                addBotMessage("❌ Simulation Server Error. Please verify server status.");
                console.error(err);
            });
        }

        // Trigger text messages sending
        function sendTextMessage(text) {
            if (!text.trim()) return;
            addUserMessage(text);

            const payload = {
                message: {
                    chat: { id: currentTelegramId },
                    from: {
                        id: currentTelegramId,
                        username: currentUsername,
                        first_name: currentFirstName,
                        last_name: currentLastName
                    },
                    text: text
                }
            };

            sendWebhookPayload(payload);
        }

        // Trigger inline keyboard callbacks
        function triggerCallback(callbackData, buttonText) {
            // Note: Telegram button clicks do not display user message bubble,
            // but for simulator feedback we show inline loader in chat.
            const payload = {
                callback_query: {
                    id: 'sim_cb_' + Date.now(),
                    from: {
                        id: currentTelegramId,
                        username: currentUsername,
                        first_name: currentFirstName,
                        last_name: currentLastName
                    },
                    message: {
                        chat: { id: currentTelegramId },
                        message_id: 1
                    },
                    data: callbackData
                }
            };

            sendWebhookPayload(payload);
        }

        // Selector Change Event
        userSelect.addEventListener('change', () => {
            const val = userSelect.value;
            
            if (val === 'new_user') {
                newUserForm.style.display = 'block';
                profileDetails.style.display = 'none';
                disableChat();
                
                // Set randomly generated Telegram ID
                newTelegramId.value = Math.floor(100000000 + Math.random() * 900000000);
            } else if (val !== '') {
                newUserForm.style.display = 'none';
                profileDetails.style.display = 'block';
                
                const opt = userSelect.options[userSelect.selectedIndex];
                currentUserId = val;
                currentTelegramId = opt.getAttribute('data-telegram-id');
                currentUsername = opt.getAttribute('data-username');
                currentFirstName = opt.getAttribute('data-first-name');
                currentLastName = opt.getAttribute('data-last-name');
                
                enableChat();
                refreshUserProfile();
                
                // Clear chat and show greeting
                chatMessages.innerHTML = '<div class="date-divider">Today</div>';
                addBotMessage(`👋 Profile loaded: **${currentFirstName} ${currentLastName}**. Click **/start** to interact.`);
            } else {
                newUserForm.style.display = 'none';
                profileDetails.style.display = 'none';
                disableChat();
            }
        });

        // Create/Simulate New User Click
        btnCreateUser.addEventListener('click', () => {
            const fName = newFirstName.value.trim();
            const lName = newLastName.value.trim();
            const uName = newUsername.value.trim();
            const tId = newTelegramId.value;

            if (!fName) {
                alert('Please enter a First Name.');
                return;
            }

            // Mock values to start webhook payload directly
            currentTelegramId = parseInt(tId);
            currentUsername = uName || null;
            currentFirstName = fName;
            currentLastName = lName || '';

            // Setup mock profiles screen
            dispFirstName.innerText = currentFirstName;
            dispTelegramId.innerText = currentTelegramId;
            dispBalance.innerText = '$0.00';
            dispRank.innerText = 'Bronze';
            dispKeys.innerText = '0';
            dispSpend.innerText = '$0.00';

            newUserForm.style.display = 'none';
            profileDetails.style.display = 'block';
            enableChat();

            // Clear chat and trigger webhook `/start` to force firstOrCreate DB action
            chatMessages.innerHTML = '<div class="date-divider">Today</div>';
            sendTextMessage('/start');

            // Set up timeout to reload user select and select the newly created user from DB
            setTimeout(() => {
                fetch('/admin/telegram-simulator')
                    .then(res => res.text())
                    .then(html => {
                        // Parse list again to capture DB update
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newSelect = doc.getElementById('user_select');
                        userSelect.innerHTML = newSelect.innerHTML;
                        
                        // Select the matching telegram ID option
                        for (let i = 0; i < userSelect.options.length; i++) {
                            if (userSelect.options[i].getAttribute('data-telegram-id') == currentTelegramId) {
                                userSelect.selectedIndex = i;
                                currentUserId = userSelect.value;
                                break;
                            }
                        }
                        refreshUserProfile();
                    });
            }, 1000);
        });

        // Quick deposit credit balance
        btnQuickCredit.addEventListener('click', () => {
            if (!currentUserId) return;
            
            btnQuickCredit.disabled = true;
            
            fetch(`/admin/users/${currentUserId}/adjust-balance`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    amount: 50.00,
                    type: 'credit',
                    description: 'Simulator Quick Balance Add'
                })
            })
            .then(res => res.json())
            .then(data => {
                btnQuickCredit.disabled = false;
                addBotMessage("💰 **Credit Added:** Administrator manual top-up of **$50.00** succeeded.");
                refreshUserProfile();
            })
            .catch(err => {
                btnQuickCredit.disabled = false;
                // Since redirect back() occurs, we reload state as fallback
                refreshUserProfile();
            });
        });

        // Sending messaging interactions
        btnSendMessage.addEventListener('click', () => {
            const text = chatInput.value;
            if (!text.trim()) return;
            sendTextMessage(text);
            chatInput.value = '';
        });

        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                const text = chatInput.value;
                if (!text.trim()) return;
                sendTextMessage(text);
                chatInput.value = '';
            }
        });

        // Reset Chat
        btnResetChat.addEventListener('click', () => {
            if (!currentTelegramId) {
                alert('Please select a Reseller Profile from the left sidebar first.');
                return;
            }
            chatMessages.innerHTML = '<div class="date-divider">Today</div>';
            addBotMessage("🧹 Chat messages cleared. Send **/start** to restart the bot menu.");
        });

        // Send /start command quickly
        btnStartBot.addEventListener('click', () => {
            if (!currentTelegramId) {
                alert('Please select a Reseller Profile from the left sidebar first.');
                return;
            }
            sendTextMessage('/start');
        });

        // Helpers for enable/disable input actions
        function enableChat() {
            chatInput.disabled = false;
            btnSendMessage.disabled = false;
        }

        function disableChat() {
            chatInput.disabled = true;
            btnSendMessage.disabled = true;
            profileDetails.style.display = 'none';
        }
    });
</script>
@endsection
