<?php

namespace App\Http\Controllers;

use App\Models\TelegramUser;
use App\Models\Product;
use App\Models\Key;
use App\Models\Transaction;
use App\Models\Spin;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TelegramController extends Controller
{
    /**
     * Handle incoming Telegram webhook updates.
     */
    public function handle(Request $request)
    {
        $update = $request->all();
        if (empty($update)) {
            $update = $request->json()->all();
        }

        // Log the incoming message for debugging purposes
        Log::info('Telegram Update Received:', $update ?: []);

        // 1. Handle regular text messages
        if (isset($update['message'])) {
            $message = $update['message'];
            $chatId = $message['chat']['id'] ?? null;
            $text = trim($message['text'] ?? '');

            if ($chatId) {
                // Auto create user profile if not exists
                $isNew = !TelegramUser::where('telegram_id', $chatId)->exists();
                $user = TelegramUser::firstOrCreate(
                    ['telegram_id' => $chatId],
                    [
                        'username' => $message['from']['username'] ?? null,
                        'first_name' => $message['from']['first_name'] ?? null,
                        'last_name' => $message['from']['last_name'] ?? null,
                        'balance' => 0.00
                    ]
                );

                // Handle referral code on /start ref_XXXX
                if (str_starts_with($text, '/start ref_') || str_starts_with($text, '/start ')) {
                    $refParam = trim(str_replace(['/start ref_', '/start '], '', $text));
                    if (!empty($refParam) && is_numeric($refParam) && (int)$refParam !== (int)$chatId) {
                        $referrer = TelegramUser::where('telegram_id', (int)$refParam)->first();
                        if ($referrer && !$user->referrer_id && $user->id !== $referrer->id) {
                            $user->referrer_id = $referrer->id;
                            $user->save();

                            $referrer->increment('referral_count');

                            Referral::firstOrCreate(
                                [
                                    'referrer_id' => $referrer->id,
                                    'referred_user_id' => $user->id,
                                ],
                                [
                                    'reward_amount' => 30.00,
                                    'is_rewarded' => false
                                ]
                            );

                            // Notify referrer
                            $refName = $user->first_name ?: 'A new friend';
                            $this->sendTelegramApi('sendMessage', [
                                'chat_id' => $referrer->telegram_id,
                                'text' => "👤 **New Friend Joined!** 🤝\n\n**{$refName}** ne aapke referral link se join kiya hai!\nJab wo apna pehla wallet deposit karenge, aapko **5% Direct Cashback** milega! 💰",
                                'parse_mode' => 'Markdown'
                            ]);
                        }
                    }
                }

                // If user is blocked, do not respond
                if ($user->is_blocked) {
                    return response()->json(['status' => 'blocked']);
                }

                // 1.1 Handle Document / APK Uploads
                if (isset($message['document'])) {
                    $this->handleDocumentUpload($chatId, $user, $message['document']);
                    return response()->json(['status' => 'document_processed']);
                }

                // 1.2 Handle Photo / Banner Uploads
                if (isset($message['photo'])) {
                    $this->handlePhotoUpload($chatId, $user, $message['photo']);
                    return response()->json(['status' => 'photo_processed']);
                }

                $this->handleTextMessage($chatId, $user, $text);
            }
        }

        // 2. Handle callback queries (when inline buttons are clicked)
        if (isset($update['callback_query'])) {
            $callbackQuery = $update['callback_query'];
            $callbackQueryId = $callbackQuery['id'];
            $chatId = $callbackQuery['message']['chat']['id'] ?? null;
            $data = $callbackQuery['data'] ?? '';

            if ($chatId && $callbackQueryId) {
                // Find user
                $user = TelegramUser::where('telegram_id', $chatId)->first();

                if ($user && !$user->is_blocked) {
                    $this->handleCallback($chatId, $data, $callbackQueryId, $user);
                }
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Render Telegram Mini App (Web App) interface.
     */
    public function webApp()
    {
        return view('telegram.webapp');
    }

    /**
     * Render Interactive 3D Spinning Wheel Mini App.
     */
    public function spinWheelView(Request $request)
    {
        $tgId = $request->query('telegram_id') ?? $request->input('telegram_id');
        $user = null;

        if ($tgId) {
            $user = TelegramUser::where('telegram_id', $tgId)->first();
        }

        if (!$user) {
            $user = TelegramUser::first() ?? new TelegramUser([
                'first_name' => 'Player',
                'balance' => 0.00,
            ]);
        }

        return view('telegram.spin_wheel', compact('user'));
    }

    /**
     * API to process live spin from the Mini App.
     */
    public function spinWheelApi(Request $request)
    {
        $tgId = $request->input('telegram_id');
        $user = TelegramUser::where('telegram_id', $tgId)->first();

        if (!$user) {
            $user = TelegramUser::first();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User account not found.'
            ]);
        }

        return DB::transaction(function () use ($user) {
            $lockedUser = TelegramUser::where('id', $user->id)->lockForUpdate()->first();

            if (!$lockedUser->canSpinToday()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Today\'s free spin already used!',
                    'time_left' => $lockedUser->formattedTimeUntilNextSpin()
                ]);
            }

            // Segments Mapping (8 unique slices):
            // Slice 0: ₹50 (Jackpot) - 0%  (LOCKED / OWNER SAFEGUARD)
            // Slice 1: ₹2 (Lucky)   - 45% (rand 1 to 45)
            // Slice 2: ₹3 (Bonus)   - 25% (rand 46 to 70)
            // Slice 3: ₹5 (Power)   - 15% (rand 71 to 85)
            // Slice 4: ₹7 (Super)   - 10% (rand 86 to 95)
            // Slice 5: ₹10 (Mega)   - 5%  (rand 96 to 100)
            // Slice 6: ₹15 (VIP)    - 0%  (LOCKED / OWNER SAFEGUARD)
            // Slice 7: ₹25 (Grand)  - 0%  (LOCKED / OWNER SAFEGUARD)
            $rand = rand(1, 100);
            if ($rand <= 45) {
                $winningSlice = 1;
                $won = 2.00;
                $tier = "🎁 Lucky Starter";
            } elseif ($rand <= 70) {
                $winningSlice = 2;
                $won = 3.00;
                $tier = "✨ Lucky Bonus";
            } elseif ($rand <= 85) {
                $winningSlice = 3;
                $won = 5.00;
                $tier = "⚡ Power Prize";
            } elseif ($rand <= 95) {
                $winningSlice = 4;
                $won = 7.00;
                $tier = "🔥 Super Win";
            } else {
                $winningSlice = 5;
                $won = 10.00;
                $tier = "🧿 Mega Prize";
            }

            $lockedUser->balance += $won;
            $lockedUser->last_spin_at = now();
            $lockedUser->total_spins = ($lockedUser->total_spins ?? 0) + 1;
            $lockedUser->total_spin_won = ($lockedUser->total_spin_won ?? 0) + $won;
            $lockedUser->save();

            Spin::create([
                'telegram_user_id' => $lockedUser->id,
                'amount_won' => $won,
                'reward_label' => "{$tier} (₹{$won})"
            ]);

            Transaction::create([
                'telegram_user_id' => $lockedUser->id,
                'type' => 'credit',
                'amount' => $won,
                'description' => "Lucky Wheel Live Spin: +₹{$won}"
            ]);

            return response()->json([
                'success' => true,
                'winning_slice' => $winningSlice,
                'amount' => $won,
                'label' => $tier,
                'new_balance' => (float)$lockedUser->balance
            ]);
        });
    }

    /**
     * Send Telegram notification & Voice Reaction AFTER wheel finishes spinning on screen.
     */
    public function spinWheelNotifyApi(Request $request)
    {
        $tgId = $request->input('telegram_id');
        $user = TelegramUser::where('telegram_id', $tgId)->first();
        if (!$user) return response()->json(['success' => false]);

        $won = (float) $request->input('amount', 0);
        $tier = $request->input('label', '✨ Lucky Prize');

        $wonClean = ($won == (int)$won) ? (int)$won : number_format($won, 2);
        $balClean = ($user->balance == (int)$user->balance) ? (int)$user->balance : number_format($user->balance, 2);

        $spinNotify = "🎉 **LUCKY WHEEL PRIZE WON!** 🎡\n\n" .
                      "{$tier}\n" .
                      "💰 **Prize Won:** `+₹{$wonClean}`\n" .
                      "💵 **Your Balance:** ₹{$balClean}\n\n" .
                      "⏰ Next free spin available in 24 hours!";

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $user->telegram_id,
            'text' => $spinNotify,
            'parse_mode' => 'Markdown'
        ]);
        $sound = \App\Http\Controllers\Admin\AdminSoundController::getEventSound('spin_win');
        if ($sound) {
            $this->sendMemeVoiceReaction($user->telegram_id, $sound);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle incoming document/APK uploads from Admin or User.
     */
    protected function handleDocumentUpload($chatId, TelegramUser $user, array $doc)
    {
        $fileId = $doc['file_id'] ?? '';
        $fileName = $doc['file_name'] ?? 'application.apk';
        $fileSizeMB = isset($doc['file_size']) ? round($doc['file_size'] / (1024 * 1024), 2) : 0;

        // Save temporary upload context for 1 hour
        Cache::put("temp_uploaded_doc_{$chatId}", [
            'file_id' => $fileId,
            'file_name' => $fileName,
            'size' => $fileSizeMB
        ], 3600);

        // Auto-save latest uploaded APK
        Cache::forever('latest_uploaded_apk_file_id', $fileId);
        Cache::forever('latest_uploaded_apk_file_name', $fileName);

        $promptText = "📥 **APK File Received Successfully!** 🎉\n\n" .
            "📦 **File Name:** `{$fileName}`\n" .
            "⚖️ **Size:** {$fileSizeMB} MB\n" .
            "🔑 **File ID:**\n`{$fileId}`\n\n" .
            "👇 **Ye APK kis game ke Download Button se jodni hai? Select karein:**";

        $bindKeyboard = [
            'inline_keyboard' => [
                [ ['text' => '🎯 Aim Carrom King', 'callback_data' => 'bind_apk_aim_carrom'] ],
                [ ['text' => '🐍 Snake Engine', 'callback_data' => 'bind_apk_snake'] ],
                [ ['text' => '🎮 KOS', 'callback_data' => 'bind_apk_kos'] ],
                [ ['text' => '🤖 Aim Ai Engine', 'callback_data' => 'bind_apk_aim_ai'] ],
                [ ['text' => '🚀 bitAIM+', 'callback_data' => 'bind_apk_bitaim'] ],
            ]
        ];

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $promptText,
            'parse_mode' => 'Markdown',
            'reply_markup' => $bindKeyboard
        ]);
    }

    /**
     * Handle incoming photo uploads for in-game item showcases.
     */
    protected function handlePhotoUpload($chatId, TelegramUser $user, array $photos)
    {
        $bestPhoto = end($photos);
        $fileId = $bestPhoto['file_id'] ?? '';

        Cache::put("temp_uploaded_photo_{$chatId}", $fileId, 3600);

        $promptText = "📸 **Photo / Showcase Banner Received!** 🎉\n\n" .
                      "🔑 **File ID:**\n`{$fileId}`\n\n" .
                      "👇 **Ye Photo kis In-Game item ke sath jodni hai? Select karein:**";

        $bindKeyboard = [
            'inline_keyboard' => [
                [ ['text' => '🧿 STRIKER PRICE', 'callback_data' => 'bind_photo_striker'] ],
                [ ['text' => '🔥 TRAIL PRICE', 'callback_data' => 'bind_photo_trail'] ],
                [ ['text' => '💎 GEMS PRICE', 'callback_data' => 'bind_photo_gems'] ],
                [ ['text' => '🟡 COIN PRICE', 'callback_data' => 'bind_photo_coins'] ],
            ]
        ];

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $promptText,
            'parse_mode' => 'Markdown',
            'reply_markup' => $bindKeyboard
        ]);
    }

    /**
     * Send the main welcome menu.
     */
    /**
     * Send the main welcome menu via rich interactive Inline Chat Card.
     */
    protected function sendWelcomeMenu($chatId, TelegramUser $user, $customText = null)
    {
        Cache::forget("tg_user_{$chatId}_product");
        Cache::forget("tg_user_{$chatId}_duration");
        Cache::forget("tg_user_{$chatId}_pending");
        Cache::forget("tg_user_{$chatId}_awaiting_email");
        Cache::forget("tg_user_{$chatId}_awaiting_utr");

        $welcomeText = $customText ?: "🎮 **KHELO STORE OFFICIAL** 🎮\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━";

        $inlineKeyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🤖  Aim Ai Engine', 'callback_data' => 'open_tool_aim_ai'],
                    ['text' => '🎮  KOS Engine', 'callback_data' => 'open_tool_kos']
                ],
                [
                    ['text' => '🐍  Snake Engine', 'callback_data' => 'open_tool_snake'],
                    ['text' => '🎯  Aim Carrom King', 'callback_data' => 'open_tool_aim_carrom']
                ],
                [
                    ['text' => '🚀  bitAIM+', 'callback_data' => 'open_tool_bitaim'],
                    ['text' => '🛍️  In-Game Items', 'callback_data' => 'ingame_menu']
                ],
                [
                    ['text' => '📦  Check Stock', 'callback_data' => 'check_stock_menu'],
                    ['text' => '💰  Check Balance', 'callback_data' => 'check_balance_user']
                ],
                [
                    ['text' => '💳  Add Balance', 'callback_data' => 'add_balance_qr'],
                    ['text' => '🔑  My Active Keys', 'callback_data' => 'my_keys_dashboard']
                ]
            ]
        ];

        // Send direct inline chat card
        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $welcomeText,
            'parse_mode' => 'Markdown',
            'reply_markup' => $inlineKeyboard
        ]);
    }

    /**
     * Handle incoming text messages from Custom Reply Keyboards.
     */
    protected function handleTextMessage($chatId, TelegramUser $user, $text)
    {
        // 0. Handle email submission for bitAIM+
        if (Cache::get("tg_user_{$chatId}_awaiting_email") || (filter_var(trim($text), FILTER_VALIDATE_EMAIL) && Cache::has("tg_user_{$chatId}_bitaim_purchase"))) {
            $email = trim($text);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Cache::forget("tg_user_{$chatId}_awaiting_email");
                $bitaimInfo = Cache::get("tg_user_{$chatId}_bitaim_purchase", []);
                Cache::forget("tg_user_{$chatId}_bitaim_purchase");

                try {
                    $user->email = $email;
                    $user->save();
                } catch (\Throwable $e) {}

                $rawDur = $bitaimInfo['duration'] ?? '7d';
                $durClean = match($rawDur) {
                    '7d' => '1 Week (7 Days)',
                    '30d' => '1 Month (30 Days)',
                    '90d' => '3 Months (90 Days)',
                    'lifetime' => 'Lifetime Access',
                    default => $rawDur
                };

                // Update recent transaction description so admin sees email in Dashboard
                try {
                    Transaction::where('telegram_user_id', $user->id)
                        ->where('type', 'debit')
                        ->where('description', 'like', 'Purchased bitAIM+%')
                        ->latest()
                        ->first()
                        ?->update(['description' => "Purchased bitAIM+ ({$rawDur}) [Email: {$email}]"]);
                } catch (\Throwable $e) {}

                // Send email alert to admin
                try {
                    \App\Services\OrderAlertService::sendSaleAlert([
                        'type' => 'bitAIM+ SUBSCRIPTION ORDER',
                        'product_name' => 'bitAIM+ Carrom',
                        'duration' => $durClean,
                        'license_key' => "Email: {$email}",
                        'price' => $bitaimInfo['price'] ?? 59,
                        'cost' => 0,
                        'user_name' => $user->first_name ?: ($user->username ?: 'Customer'),
                        'user_username' => $user->username
                    ]);
                } catch (\Throwable $e) {}

                // Send instant alert to Admin on Telegram with 1-click Activation button
                try {
                    $adminTgId = env('ADMIN_TELEGRAM_ID', '');
                    if ($adminTgId) {
                        $adminAlertText = "🔔 **NEW bitAIM+ ORDER RECEIVED!** 🚀\n" .
                                          "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                                          "👤 **Customer:** " . ($user->first_name ?: 'Customer') . ($user->username ? " (@{$user->username})" : "") . "\n" .
                                          "🆔 **Telegram ID:** `{$user->telegram_id}`\n" .
                                          "📧 **Account Email:** `{$email}`\n" .
                                          "⏳ **Plan:** {$durClean} (₹" . ($bitaimInfo['price'] ?? 59) . ")\n" .
                                          "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                                          "Activation complete hone par neeche button dabayein:";

                        $adminKeyboard = [
                            'inline_keyboard' => [
                                [
                                    ['text' => '✅ Mark Activated & Notify Customer', 'callback_data' => "activate_bitaim_{$user->id}"]
                                ]
                            ]
                        ];

                        $this->sendTelegramApi('sendMessage', [
                            'chat_id' => $adminTgId,
                            'text' => $adminAlertText,
                            'parse_mode' => 'Markdown',
                            'reply_markup' => $adminKeyboard
                        ]);
                    }
                } catch (\Throwable $e) {}

                $confirmMsg = "✅ **Email Registered Successfully!**\n\n" .
                              "Your bitAIM+ subscription will be activated on `{$email}` within 2 hours. 🚀";

                $this->sendTelegramApi('sendMessage', [
                    'chat_id' => $chatId,
                    'text' => $confirmMsg,
                    'parse_mode' => 'Markdown',
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [ ['text' => '🏠 Main Menu', 'callback_data' => 'main_menu'] ]
                        ]
                    ]
                ]);
                return;
            } else {
                $this->sendTelegramApi('sendMessage', [
                    'chat_id' => $chatId,
                    'text' => "⚠️ **INVALID EMAIL FORMAT!**\n\nKripya sahi email address enter karein (e.g. `name@gmail.com`):",
                    'parse_mode' => 'Markdown'
                ]);
                return;
            }
        }

        if ($text === '/start' || $text === '🔙 Back to Main Menu') {
            $this->sendWelcomeMenu($chatId, $user);
            return;
        }

        if ($text === '🛍️ Carrom In-Game Items' || $text === '/items' || $text === '/carrom' || $text === '🛍️ In-Game Items') {
            $this->sendInGameItemsMenu($chatId, $user);
            return;
        }

        if ($text === '/download' || $text === '📥 Download' || $text === '📥 All App Download') {
            $downloadText = "📥 SELECT THE DOWNLOAD APPS BUTTON GIVIN BELOW 📥";

            $downloadKeyboard = [
                'inline_keyboard' => [
                    [ ['text' => '✅ Aim Ai Engine', 'callback_data' => 'dl_aim_ai'] ],
                    [ ['text' => '✅ Aim Carrom King', 'callback_data' => 'dl_aim_carrom'] ],
                    [ ['text' => '✅ KOS', 'callback_data' => 'dl_kos'] ],
                    [ ['text' => '✅ Snake Engine', 'callback_data' => 'dl_snake'] ],
                    [ ['text' => '🏠 Back to Menu', 'callback_data' => 'dl_back_menu'] ],
                ]
            ];

            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => $downloadText,
                'reply_markup' => $downloadKeyboard
            ]);
            return;
        }

        if ($text === '/pricelist' || $text === '/prices' || $text === '📋 Price List' || $text === 'Price List' || $text === 'Prices' || $text === 'prices') {
            $this->sendPriceListCard($chatId, $user);
            return;
        }

        if ($text === '/start spin' || $text === '/spin' || $text === '🎰 Daily Spin' || $text === 'Daily Spin' || $text === '🎰 Spin Wheel' || $text === 'Spin') {
            $this->sendSpinWheelMenu($chatId, $user);
            return;
        }

        if ($text === '/start refer' || $text === '/start referral' || $text === '/refer' || $text === '/referral' || $text === '👥 Refer & Earn' || $text === 'Refer & Earn' || $text === '👥 Refer' || $text === 'Refer' || $text === 'refer') {
            $this->sendReferralDashboard($chatId, $user);
            return;
        }

        if ($text === '/mykeys' || $text === '🔑 My Keys' || $text === 'My Keys' || $text === '📦 My Keys' || $text === '📦 Active Keys') {
            $this->sendMyKeysDashboard($chatId, $user);
            return;
        }

        if ($text === '/balance' || $text === '💰 Check Balance' || $text === 'Check Balance' || $text === 'Balance' || $text === 'balance') {
            $this->sendUserBalanceCard($chatId, $user);
            return;
        }

        if ($text === '/stock' || $text === '📦 Check Stock' || $text === 'Check Stock' || $text === 'Stock' || $text === 'stock') {
            $this->sendCheckStockMenu($chatId, $user);
            return;
        }

        if ($text === '/proof' || $text === '/checkproof' || $text === 'Check Proof' || $text === '🧾 Check Proof' || $text === '✅ Check Proof' || $text === 'Check Proofs') {
            $this->sendProofInfo($chatId, $user);
            return;
        }

        if ($text === '/tutorial' || $text === '/video' || $text === '/guide' || $text === '🎥 Video Guide' || $text === 'Video Guide' || $text === 'How to buy' || $text === 'how to buy') {
            $videoPath = public_path('videos/tutorial.mp4');
            $caption = "🎬 **HOW TO BUY KEYS & ADD BALANCE - FULL VIDEO GUIDE** 🚀\n\n" .
                       "Is video ko dekhkar aap asani se:\n" .
                       "1️⃣ Wallet me UPI QR se instant balance add kar sakte hain\n" .
                       "2️⃣ Apni manpasand VIP Game Key 1-second me buy kar sakte hain\n" .
                       "3️⃣ Mod APK download karke play kar sakte hain!\n\n" .
                       "👇 Kisi bhi game ki key khareedne ke liye /start dabayein!";

            $keyboard = [
                'inline_keyboard' => [
                    [ ['text' => '🛍️ Open Store Menu', 'callback_data' => 'dl_back_menu'] ],
                    [ ['text' => '💳 Add Balance (QR)', 'callback_data' => 'add_balance_qr'] ],
                ]
            ];

            $this->sendTelegramApi('sendVideo', [
                'chat_id' => $chatId,
                'video' => $videoPath,
                'caption' => $caption,
                'parse_mode' => 'Markdown',
                'reply_markup' => $keyboard,
                'supports_streaming' => true
            ]);
            return;
        }

        // 1. Direct Product Selection via Text / Buttons
        $clean = strtolower($text);
        if (str_contains($clean, 'carrom king') || str_contains($clean, 'aim carrom')) {
            $this->sendProductInlineCard($chatId, $user, 'aim_carrom');
            return;
        }
        if (str_contains($clean, 'snake')) {
            $this->sendProductInlineCard($chatId, $user, 'snake');
            return;
        }
        if (str_contains($clean, 'kos')) {
            $this->sendProductInlineCard($chatId, $user, 'kos');
            return;
        }
        if (str_contains($clean, 'aim ai') || str_contains($clean, 'aimai')) {
            $this->sendProductInlineCard($chatId, $user, 'aim_ai');
            return;
        }
        if (str_contains($clean, 'bitaim')) {
            $this->sendProductInlineCard($chatId, $user, 'bitaim');
            return;
        }

        // 2. Duration Selection
        $durationMap = [
            '1 Day' => '1d',
            '1 Week' => '7d',
            '1 Month' => '30d',
            '3 Months' => '90d',
            '💎 Life Time' => 'lifetime',
            '3 Days' => '3d',
            '7 Days' => '7d',
            '10 Days' => '10d',
            '15 Days' => '15d',
            '30 Days' => '30d',
            '90 Days' => '90d',
        ];

        if (isset($durationMap[$text])) {
            $durCode = $durationMap[$text];
            Cache::put("tg_user_{$chatId}_duration", $durCode, 1800);
            $productName = Cache::get("tg_user_{$chatId}_product", "Aim Carrom King");

            $isAimCarrom = str_contains(strtolower($productName), 'carrom') && !str_contains(strtolower($productName), 'bitaim');
            $isSnake = str_contains(strtolower($productName), 'snake');
            $isKos = str_contains(strtolower($productName), 'kos');

            if ($isAimCarrom) {
                // Show Normal Mod / Queue Mod menu
                $this->sendTelegramApi('sendMessage', [
                    'chat_id' => $chatId,
                    'text' => "Choose Game Mod:",
                    'parse_mode' => 'Markdown',
                    'reply_markup' => [
                        'keyboard' => [
                            [ ['text' => 'Normal Mod'], ['text' => 'Queue Mod'] ],
                            [ ['text' => '🔙 Back to Main Menu'] ]
                        ],
                        'resize_keyboard' => true
                    ]
                ]);
            } elseif ($isSnake) {
                // Show Choose Game menu for Snake
                $this->sendTelegramApi('sendMessage', [
                    'chat_id' => $chatId,
                    'text' => "Choose Game:",
                    'parse_mode' => 'Markdown',
                    'reply_markup' => [
                        'keyboard' => [
                            [ ['text' => '8 Ball Pool'] ],
                            [ ['text' => 'Carrom Pool'] ],
                            [ ['text' => 'Soccer Star'] ],
                            [ ['text' => '🔙 Back to Main Menu'] ]
                        ],
                        'resize_keyboard' => true
                    ]
                ]);
            } elseif ($isKos) {
                // Show Choose Game menu for KOS
                $this->sendTelegramApi('sendMessage', [
                    'chat_id' => $chatId,
                    'text' => "Choose Game:",
                    'parse_mode' => 'Markdown',
                    'reply_markup' => [
                        'keyboard' => [
                            [ ['text' => 'Carrom Pool'], ['text' => '8 Ball Pool'] ],
                            [ ['text' => '🔙 Back to Main Menu'] ]
                        ],
                        'resize_keyboard' => true
                    ]
                ]);
            } else {
                // Ask Confirmation Question
                $this->askConfirmationQuestion($chatId, $user, $productName, $durCode, null);
            }
            return;
        }

        // 3. Game Selection (Snake Engine & KOS)
        $gameOptions = ['8 Ball Pool', 'Carrom Pool', 'Soccer Star'];
        if (in_array($text, $gameOptions)) {
            $productName = Cache::get("tg_user_{$chatId}_product", "KOS");
            $durCode = Cache::get("tg_user_{$chatId}_duration", "1d");
            
            $this->askConfirmationQuestion($chatId, $user, $productName, $durCode, $text);
            return;
        }

        // 4. Mod Selection (Normal Mod / Queue Mod)
        if ($text === 'Normal Mod' || $text === 'Queue Mod') {
            $productName = Cache::get("tg_user_{$chatId}_product", "Aim Carrom King");
            $durCode = Cache::get("tg_user_{$chatId}_duration", "7d");
            $modType = strtolower(str_replace(' Mod', '', $text)); // normal or queue

            $this->askConfirmationQuestion($chatId, $user, $productName, $durCode, $modType);
            return;
        }

        // 5. Confirmation Handling (Yes / No)
        if ($text === '✅ Yes') {
            $pending = Cache::pull("tg_user_{$chatId}_pending");
            if (!$pending) {
                // Silently ignore duplicate clicks so user balance is never deducted twice!
                return;
            }

            $this->executePendingCheckout($chatId, $user, $pending);
            return;
        }

        if ($text === '❌ No') {
            Cache::forget("tg_user_{$chatId}_pending");
            $cancelMsg = "✅ **Purchase cancelled.**\n\nNo balance was deducted.";
            $this->sendWelcomeMenu($chatId, $user, $cancelMsg);
            return;
        }

        // 6. Check Stock Menu
        if ($text === '📦 Check Stock') {
            Cache::put("tg_user_{$chatId}_in_stock_menu", true, 1800);

            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => "📦 CHECK STOCK 📦\n\nSelect game:",
                'reply_markup' => [
                    'keyboard' => [
                        [ ['text' => 'Aim Carrom King'] ],
                        [ ['text' => 'Snake Engine'] ],
                        [ ['text' => 'KOS'] ],
                        [ ['text' => 'Aim Ai Engine'] ],
                        [ ['text' => '🔙 Back to Main Menu'] ]
                    ],
                    'resize_keyboard' => true
                ]
            ]);
            return;
        }

        // Stock Game Selection Output
        $stockGamesMap = [
            'Aim Carrom King' => 'Aim Carrom King',
            'Snake Engine' => 'Snake Engine',
            'KOS' => 'KOS',
            'Aim Ai Engine' => 'Aim Ai Engine',
        ];

        if (isset($stockGamesMap[$text])) {
            $this->sendGameStockInfo($chatId, $user, $stockGamesMap[$text]);
            return;
        }

        // 8. Check Balance
        if ($text === '💰 Check Balance') {
            $fullName = trim("{$user->first_name} {$user->last_name}");
            $bal = number_format($user->balance, 0, '.', '');
            $replyText = "👤 {$fullName}\n💰 Your balance: ₹{$bal}";

            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => $replyText
            ]);
            return;
        }

        // 9. Add Balance Menu (Direct Dynamic QR Code & 1-Click Verify)
        if ($text === '💳 Add Balance') {
            $this->sendDirectAddBalanceQR($chatId, $user);
            return;
        }

        // Handle UTR Input for Add Balance
        $isAwaitingUtr = Cache::get("tg_user_{$chatId}_awaiting_utr");
        $cleanText = preg_replace('/[^0-9A-Za-z_-]/', '', trim($text));
        
        if ($isAwaitingUtr || (is_numeric($cleanText) && strlen($cleanText) >= 10 && strlen($cleanText) <= 24)) {
            // Anti-duplicate protection: Check if this UTR was already credited
            $alreadyUsed = Transaction::where('description', 'LIKE', "%{$cleanText}%")->exists();
            if ($alreadyUsed) {
                Cache::forget("tg_user_{$chatId}_awaiting_utr");
                $dupMsg = "⚠️ **TRANSACTION ALREADY CREDITED!**\n\nYeh UTR / Reference ID (`{$cleanText}`) pehle hi aapke wallet me add ho chuki hai. Duplicate transaction allowed nahi hai.";
                $this->sendTelegramApi('sendMessage', [
                    'chat_id' => $chatId,
                    'text' => $dupMsg,
                    'parse_mode' => 'Markdown',
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [ ['text' => '💳 Add Balance via QR', 'callback_data' => 'add_balance_qr'] ],
                            [ ['text' => '🏠 Main Menu', 'callback_data' => 'main_menu'] ]
                        ]
                    ]
                ]);
                return;
            }

            // Attempt Paytm API verification for exact paid amount
            $paidAmount = 0;
            $verified = false;
            $merchantId = env('PAYTM_MERCHANT_ID', '');
            $merchantKey = env('PAYTM_MERCHANT_KEY', '');
            $verifyApiUrl = env('PAYTM_VERIFY_API', 'https://anujbots.xyz/paytm/verify.php');

            try {
                $res = \Illuminate\Support\Facades\Http::timeout(8)->get($verifyApiUrl, [
                    'merchantid' => $merchantId,
                    'merchantkey' => $merchantKey,
                    'orderid' => $cleanText,
                ]);
                if ($res->successful()) {
                    $vData = $res->json();
                    if (!empty($vData['success']) && ($vData['status'] ?? '') === 'TXN_SUCCESS' && !empty($vData['amount'])) {
                        $paidAmount = (float)$vData['amount'];
                        if ($paidAmount > 0) {
                            $verified = true;
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error("Payment verify API exception: " . $e->getMessage());
            }

            // Reject if payment not verified by gateway
            if (!$verified || $paidAmount <= 0) {
                if ($isAwaitingUtr) {
                    $failedMsg = "❌ **PAYMENT VERIFICATION FAILED!** ⚠️\n" .
                                 "━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                                 "🆔 **UTR / Order ID:** `{$cleanText}`\n\n" .
                                 "⚠️ Is UTR ka payment record gateway par verify nahi hua ya invalid hai.\n\n" .
                                 "📌 **Tips:**\n" .
                                 "• Kripya Paytm/PhonePe/GPay receipt se sahi **12-digit UPI UTR No.** enter karein.\n" .
                                 "• Agar abhi pay kiya hai, toh 1-2 minute baad dobara check karein.\n" .
                                 "• Kisi problem ke liye Admin support se contact karein.";

                    $this->sendTelegramApi('sendMessage', [
                        'chat_id' => $chatId,
                        'text' => $failedMsg,
                        'parse_mode' => 'Markdown',
                        'reply_markup' => [
                            'inline_keyboard' => [
                                [ ['text' => '🔄 Try Again (Enter UTR)', 'callback_data' => 'verify_payment'] ],
                                [ ['text' => '💳 Show QR Code Again', 'callback_data' => 'add_balance_qr'] ],
                                [ ['text' => '🏠 Main Menu', 'callback_data' => 'main_menu'] ]
                            ]
                        ]
                    ]);
                }
                return;
            }

            // Clear cache state after successful verification
            Cache::forget("tg_user_{$chatId}_awaiting_utr");

            // Atomic balance update inside DB transaction with user row lock
            DB::transaction(function () use ($user, $paidAmount, $cleanText) {
                $lockedUser = TelegramUser::where('id', $user->id)->lockForUpdate()->first();
                $lockedUser->balance += $paidAmount;
                $lockedUser->save();

                Transaction::create([
                    'telegram_user_id' => $lockedUser->id,
                    'type' => 'credit',
                    'amount' => $paidAmount,
                    'description' => "UPI Deposit (Verified UTR: {$cleanText})"
                ]);
            });

            $user->refresh();

            // Reward Referrer if this is the user's 1st deposit (5% Cashback)
            try {
                \App\Models\Referral::checkAndRewardDeposit($user, (float)$paidAmount);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Referral reward error: " . $e->getMessage());
            }

            $balClean = ($user->balance == (int)$user->balance) ? (int)$user->balance : number_format($user->balance, 2);
            $amtClean = ($paidAmount == (int)$paidAmount) ? (int)$paidAmount : number_format($paidAmount, 2);

            $successMsg = "🎉 **PAYMENT VERIFIED & CREDITED!** 💸\n" .
                          "━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                          "💰 **Amount Added:** `+₹{$amtClean}`\n" .
                          "💳 **New Wallet Balance:** `₹{$balClean}`\n" .
                          "🆔 **UTR / Ref No:** `{$cleanText}`\n\n" .
                          "Aapka balance instant add ho gaya hai! Ab aap koi bhi gaming key khareed sakte hain. 🎮🚀";

            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => $successMsg,
                'parse_mode' => 'Markdown',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [ ['text' => '🛍️ Buy Game Keys', 'callback_data' => 'main_menu'] ],
                        [ ['text' => '🏠 Main Menu', 'callback_data' => 'main_menu'] ]
                    ]
                ]
            ]);

            // Send admin deposit alert email
            try {
                \App\Services\OrderAlertService::sendSaleAlert([
                    'type' => 'UPI DEPOSIT (UTR VERIFIED)',
                    'product_name' => "Wallet Top-Up (UTR: {$cleanText})",
                    'duration' => 'Deposit',
                    'license_key' => "UTR: {$cleanText}",
                    'price' => $paidAmount,
                    'cost' => 0,
                    'user_name' => $user->first_name,
                    'user_username' => $user->username
                ]);
            } catch (\Throwable $e) {}

            return;
        }

        // Fallback: show welcome menu again
        $this->sendWelcomeMenu($chatId, $user, "Please choose an option from the menu below:");
    }

    /**
     * Ask confirmation question before checkout.
     */
    protected function askConfirmationQuestion($chatId, TelegramUser $user, $productName, $duration, $subOption = null)
    {
        $product = Product::where('name', $productName)->first();

        if (!$product) {
            $this->sendWelcomeMenu($chatId, $user, "❌ Product not found.");
            return;
        }

        $price = (float) $this->getExactProductPrice($productName, $duration, $subOption, $user->id);

        $durationLabel = match($duration) {
            '1d' => '1 Day(s)',
            '3d' => '3 Day(s)',
            '7d' => '7 Day(s)',
            '10d' => '10 Day(s)',
            '15d' => '15 Day(s)',
            '30d' => '30 Day(s)',
            '90d' => '90 Day(s)',
            'lifetime' => 'Life Time',
            default => $duration
        };

        // Determine App Name & Game Name matching screenshot
        $appName = match((int)$product->id) {
            1 => 'Aim Carrom King',
            2, 75, 80 => 'Snake',
            3, 76 => 'KOS',
            4 => 'Aim Ai',
            78 => 'bitAIM+',
            default => trim(str_replace(' Engine', '', preg_replace('/\(.*\)/', '', $product->name)))
        };
        $gameName = $subOption;

        if (!$gameName) {
            if (str_contains(strtolower($appName), 'carrom') || str_contains(strtolower($appName), 'bitaim') || str_contains(strtolower($appName), 'aim ai')) {
                $gameName = 'Carrom Pool';
            } elseif (str_contains(strtolower($appName), 'snake')) {
                $gameName = '8 Ball Pool';
            } elseif (str_contains(strtolower($appName), 'kos')) {
                $gameName = 'Carrom Pool';
            } else {
                $gameName = $product->game_name ?? 'Carrom Pool';
            }
        } elseif (strtolower($gameName) === 'normal' || strtolower($gameName) === 'queue') {
            $gameName = 'Carrom Pool (' . ucfirst($gameName) . ')';
        }

        $displayName = $appName;
        if ($subOption && !str_contains(strtolower($gameName), strtolower($subOption))) {
            $displayName .= ' ' . ucfirst($subOption);
        }

        Cache::put("tg_user_{$chatId}_pending", [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'display_name' => $displayName,
            'game_name' => $gameName,
            'duration' => $duration,
            'duration_label' => $durationLabel,
            'price' => $price,
        ], 1800);

        $priceDisplay = number_format($price, 0);
        $userBalance = number_format($user->balance, 0, '.', '');

        $isBitAim = str_contains(strtolower($appName), 'bitaim');

        if ($isBitAim) {
            $confirmText = "🚀 **Confirm bitAIM+ Purchase**\n" .
                           "━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                           "• **Plan :** {$durationLabel}\n" .
                           "• **Price :** `₹{$priceDisplay}`\n" .
                           "• **Your Balance :** `₹{$userBalance}`\n" .
                           "━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                           "👉 **Click Confirm below:**";
        } else {
            $confirmText = "⚡ **Confirm Your Order**\n" .
                           "────────────────────────\n" .
                           "• **App :** {$appName}\n" .
                           "• **Duration :** {$durationLabel}\n" .
                           "• **Price :** `₹{$priceDisplay}`\n\n" .
                           "💵 **Your Balance :** `₹{$userBalance}`\n" .
                           "────────────────────────\n" .
                           "👉 **Click Confirm to get instant key:**";
        }

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $confirmText,
            'reply_markup' => [
                'keyboard' => [
                    [ ['text' => '✅ Yes'], ['text' => '❌ No'] ]
                ],
                'resize_keyboard' => true
            ]
        ]);
    }

    /**
     * Get exact price matching master price list table with VIP custom discount override.
     */
    protected function getExactProductPrice($productName, $duration, $subOption = null, $userId = null)
    {
        $product = Product::where('name', $productName)->first();
        if (!$product) {
            $nameLower = strtolower($productName);
            $product = Product::whereRaw('LOWER(name) LIKE ?', ["%{$nameLower}%"])->first();
        }

        if ($product) {
            $durKey = $duration;
            if (strtolower($subOption ?? '') === 'queue' && !str_contains($duration, 'queue')) {
                $durKey = $duration . '_queue';
            }
            return (float) $product->getEffectivePriceForTelegramUser($userId, $durKey);
        }

        return 100.00;
    }

    /**
     * Execute pending key purchase after user clicks Yes.
     */
    protected function executePendingCheckout($chatId, TelegramUser $user, array $pending)
    {
        $product = Product::find($pending['product_id']);
        $duration = $pending['duration'];
        $price = (float) $pending['price'];
        $displayName = $pending['display_name'];
        $durationLabel = $pending['duration_label'];

        if (!$product) {
            $this->sendWelcomeMenu($chatId, $user, "❌ Product not found.");
            return;
        }

        $isBitAim = str_contains(strtolower($pending['product_name'] ?? ''), 'bitaim') || str_contains(strtolower($displayName), 'bitaim');

        if ($isBitAim) {
            if ($user->balance < $price) {
                $replyText = "❌ **Insufficient Balance!**\n\nProduct: **{$displayName} ({$durationLabel})**\nPrice: **₹" . number_format($price, 0) . "**\nYour Balance: **₹" . number_format($user->balance, 0) . "**\n\nPlease contact admin to top up your wallet.";
                $this->sendWelcomeMenu($chatId, $user, $replyText);
                return;
            }

            // Deduct balance and record transaction
            DB::transaction(function () use ($user, $price, $displayName, $durationLabel) {
                $user->balance -= $price;
                $user->save();

                Transaction::create([
                    'telegram_user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $price,
                    'description' => "Purchased bitAIM+ subscription ({$durationLabel})",
                ]);
            });

            Cache::put("tg_user_{$chatId}_awaiting_email", true, 1800);

            $amtDeducted = number_format($price, 0);
            $remBalance = number_format($user->balance, 0);

            $msg1 = "✅ Request received! ✅\n\n💰 Amount Deducted: ₹{$amtDeducted}\n💵 Remaining Balance: ₹{$remBalance}\n\nPlease check your email for further instructions.";

            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => $msg1
            ]);

            $msg2 = "📧 Please send your Email Address:\n\nWe will send your bitAIM+ Carrom subscription credentials to this email.\n\n(Simply type your email address)";

            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => $msg2
            ]);

            return;
        }

        if ($user->balance < $price) {
            $replyText = "❌ **Insufficient Balance!**\n\nProduct: **{$displayName} ({$durationLabel})**\nPrice: **₹" . number_format($price, 0) . "**\nYour Balance: **₹" . number_format($user->balance, 0) . "**\n\nPlease contact admin to top up your wallet.";
        } else {
            $replyText = DB::transaction(function () use ($user, $product, $price, $durationLabel, $displayName, $duration, $pending) {
                $key = Key::where('product_id', $product->id)
                    ->where('duration', $duration)
                    ->where('is_sold', false)
                    ->lockForUpdate()
                    ->first();

                if (!$key) {
                    return [
                        'error' => 'out_of_stock',
                        'product_id' => $product->id,
                        'product_name' => $displayName,
                        'duration' => $duration,
                        'duration_label' => $durationLabel,
                    ];
                }

                $user->balance -= $price;
                $user->save();

                $expiresAt = Key::calculateExpiryFromDuration($duration, now());

                $key->update([
                    'is_sold' => true,
                    'telegram_user_id' => $user->id,
                    'sold_at' => now(),
                    'expires_at' => $expiresAt,
                    'renewal_alert_sent_at' => null,
                ]);

                Transaction::create([
                    'telegram_user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $price,
                    'description' => "Purchased key for {$displayName} ({$durationLabel})",
                ]);

                $durTitle = match($duration) {
                    '1d' => '1 Days',
                    '3d' => '3 Days',
                    '7d' => '7 Days',
                    '10d' => '10 Days',
                    '15d' => '15 Days',
                    '30d' => '30 Days',
                    '90d' => '90 Days',
                    default => $durationLabel
                };

                // Send instant Admin Email Alert
                \App\Services\OrderAlertService::sendSaleAlert([
                    'type' => 'NEW KEY PURCHASE',
                    'product_name' => $displayName,
                    'duration' => $durTitle,
                    'license_key' => $key->license_key,
                    'price' => $price,
                    'cost' => method_exists($product, 'getCostForDuration') ? $product->getCostForDuration($duration) : 0,
                    'user_name' => $user->first_name ?: ($user->username ?: 'Customer'),
                    'user_username' => $user->username,
                    'user_telegram_id' => $user->telegram_id,
                    'user_balance' => $user->balance,
                ]);

                $gameTitle = $pending['game_name'] ?? $product->name;
                $cleanGameTitle = str_contains($gameTitle, '(') ? trim(explode('(', $gameTitle)[0]) : $gameTitle;

                // Check if user was referred and award 1-time 5% cashback to referrer on first key purchase
                if ($user->referrer_id) {
                    $pendingRef = Referral::where('referred_user_id', $user->id)
                        ->where('is_rewarded', false)
                        ->first();

                    if ($pendingRef) {
                        $referrer = TelegramUser::find($user->referrer_id);
                        if ($referrer) {
                            $rewardAmt = max(5.00, round($price * 0.05, 2));
                            $pendingRef->update([
                                'is_rewarded' => true,
                                'reward_amount' => $rewardAmt,
                                'rewarded_at' => now(),
                            ]);

                            $referrer->balance += $rewardAmt;
                            $referrer->referral_earnings += $rewardAmt;
                            $referrer->save();

                            Transaction::create([
                                'telegram_user_id' => $referrer->id,
                                'type' => 'credit',
                                'amount' => $rewardAmt,
                                'description' => "1-Time 5% Referral Cashback for inviting {$user->first_name} (Purchase: ₹{$price})",
                            ]);

                            $refName = $user->first_name ?: 'Friend';
                            $refBalStr = number_format($referrer->balance, 2);
                            $refEarnStr = number_format($referrer->referral_earnings, 2);

                            $refMsg = "🎉 **1-TIME 5% REFERRAL CASHBACK EARNED!** 💸\n\n" .
                                      "Aapke friend **{$refName}** ne apni first key (₹" . number_format($price, 0) . ") purchase kar li hai!\n" .
                                      "💰 **+₹" . number_format($rewardAmt, 2) . "** (5% Cashback) aapke wallet me add kar diye gaye hain!\n\n" .
                                      "💵 **New Balance:** ₹{$refBalStr}\n" .
                                      "🏆 **Total Referral Earnings:** ₹{$refEarnStr}\n\n" .
                                      "Aur dosto ko invite karein aur har naye dost ke first purchase par 5% kamayein! 🚀";

                            $this->sendTelegramApi('sendMessage', [
                                'chat_id' => $referrer->telegram_id,
                                'text' => $refMsg,
                                'parse_mode' => 'Markdown',
                                'reply_markup' => [
                                    'inline_keyboard' => [
                                        [ ['text' => '👥 Invite More Friends', 'callback_data' => 'refer_dashboard'] ],
                                    ]
                                ]
                            ]);
                        }
                    }
                }

                $remBal = number_format($user->balance, 0, '.', '');
                $expFormatted = $expiresAt->format('d M Y, h:i A');

                return "✅ {$durTitle} {$cleanGameTitle} KEY ✅\n\n" .
                       "🔑 `{$key->license_key}`\n\n" .
                       "⏳ **Valid Until:** {$expFormatted}\n" .
                       "💰 Remaining Balance: ₹{$remBal}\n\n" .
                       "Thank you for your purchase!";
            });
        }

        if (is_array($replyText) && isset($replyText['error']) && $replyText['error'] === 'out_of_stock') {
            // Send instant Out-of-Stock email alert to Admin
            try {
                $throttleKey = "oos_alert_{$user->id}_{$replyText['product_id']}_{$replyText['duration']}";
                if (!Cache::has($throttleKey)) {
                    Cache::put($throttleKey, true, 120);
                    \App\Services\OrderAlertService::sendOutOfStockAlert([
                        'product_name' => $replyText['product_name'],
                        'duration' => $replyText['duration_label'] ?? $replyText['duration'],
                        'price' => $price ?? 0,
                        'user_name' => $user->first_name ?: 'Customer',
                        'user_username' => $user->username,
                        'user_balance' => $user->balance,
                    ]);
                }
            } catch (\Throwable $e) {}

            $outMsg = "❌ **PRODUCT CURRENTLY OUT OF STOCK!** ⚠️\n\n" .
                      "🎮 **Product:** {$replyText['product_name']} ({$replyText['duration_label']})\n\n" .
                      "Maaf kijiye, is item ka stock abhi khatam ho gaya hai.\n\n" .
                      "🔔 **Zero Lost Sales:**\n" .
                      "Neeche **\"Notify Me\"** button par click karein. Jaise hi Admin naya stock add karega, bot sabse pehle aapko private DM me alert bhejega! 🚀";

            $keyboard = [
                'inline_keyboard' => [
                    [ ['text' => '🔔 Notify Me When Back In Stock', 'callback_data' => "notify_stock_{$replyText['product_id']}_{$replyText['duration']}"] ],
                    [ ['text' => '🛍️ Browse Available Products', 'callback_data' => 'dl_back_menu'] ],
                    [ ['text' => '🏠 Back to Menu', 'callback_data' => 'dl_back_menu'] ],
                ]
            ];

            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => $outMsg,
                'parse_mode' => 'Markdown',
                'reply_markup' => $keyboard
            ]);
            return;
        }

        $this->sendWelcomeMenu($chatId, $user, $replyText);
    }

    /**
     * Handle actions for when an inline button is clicked.
     */
    protected function handleCallback($chatId, $data, $callbackQueryId, TelegramUser $user)
    {
        if ($callbackQueryId && $data !== 'verify_payment') {
            $this->sendTelegramApi('answerCallbackQuery', [
                'callback_query_id' => $callbackQueryId
            ]);
        }

        if ($data === 'verify_payment') {
            $this->verifyPaytmPaymentCallback($chatId, $user, $callbackQueryId);
            return;
        }

        if (str_starts_with($data, 'activate_bitaim_')) {
            $targetUserId = (int) str_replace('activate_bitaim_', '', $data);
            $targetUser = TelegramUser::find($targetUserId);

            if (!$targetUser) {
                if ($callbackQueryId) {
                    $this->sendTelegramApi('answerCallbackQuery', [
                        'callback_query_id' => $callbackQueryId,
                        'text' => '❌ Customer not found in database!',
                        'show_alert' => true
                    ]);
                }
                return;
            }

            $custEmail = $targetUser->email ?: 'your account email';

            $activationMsg = "✅ **Subscription Activated!**\n\n" .
                             "Your bitAIM+ access on `{$custEmail}` is now **ACTIVE**. Happy Gaming! 🚀";

            $custButtons = [
                'inline_keyboard' => [
                    [
                        ['text' => '📥 Download bitAIM+', 'callback_data' => 'dl_bitaim'],
                        ['text' => '🏠 Main Menu', 'callback_data' => 'main_menu']
                    ]
                ]
            ];

            // 1. Send activation notification to Customer on Telegram
            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $targetUser->telegram_id,
                'text' => $activationMsg,
                'parse_mode' => 'Markdown',
                'reply_markup' => $custButtons
            ]);

            // 2. Alert Admin popup
            if ($callbackQueryId) {
                $this->sendTelegramApi('answerCallbackQuery', [
                    'callback_query_id' => $callbackQueryId,
                    'text' => "🎉 Activated! Notification sent to {$targetUser->first_name}!",
                    'show_alert' => true
                ]);
            }

            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => "✅ **bitAIM+ ACTIVATED!**\nCustomer {$targetUser->first_name} (`{$custEmail}`) ko confirmation bhej diya gaya hai.",
                'parse_mode' => 'Markdown'
            ]);

            return;
        }

        if ($data === 'main_menu' || $data === 'proof_back_menu' || $data === 'dl_back_menu') {
            $this->sendWelcomeMenu($chatId, $user);
            return;
        }

        if (str_starts_with($data, 'open_tool_')) {
            $toolKey = str_replace('open_tool_', '', $data);
            $this->sendProductInlineCard($chatId, $user, $toolKey);
            return;
        }

        if (str_starts_with($data, 'carrom_dur_')) {
            $dur = str_replace('carrom_dur_', '', $data);
            $this->sendCarromModSelection($chatId, $user, $dur);
            return;
        }

        if (str_starts_with($data, 'snake_dur_')) {
            $dur = str_replace('snake_dur_', '', $data);
            $this->sendSnakeGameSelection($chatId, $user, $dur);
            return;
        }

        if (str_starts_with($data, 'kos_dur_')) {
            $dur = str_replace('kos_dur_', '', $data);
            $this->sendKosGameSelection($chatId, $user, $dur);
            return;
        }

        if (str_starts_with($data, 'buy_item_')) {
            $parts = explode('_', $data);
            $productId = (int)($parts[2] ?? 0);
            $dur = $parts[3] ?? '3d';
            $sub = null;
            if (isset($parts[4]) && $parts[4] === 'queue') {
                $dur .= '_queue';
                if (isset($parts[5])) {
                    $sub = $parts[5];
                }
            } elseif (isset($parts[4])) {
                $sub = $parts[4];
            }
            $this->promptInlineCheckout($chatId, $user, $productId, $dur, $sub);
            return;
        }

        if (str_starts_with($data, 'exec_buy_')) {
            $parts = explode('_', $data);
            $productId = (int)($parts[2] ?? 0);
            $dur = $parts[3] ?? '3d';
            $sub = null;
            if (isset($parts[4]) && $parts[4] === 'queue') {
                $dur .= '_queue';
                if (isset($parts[5])) {
                    $sub = $parts[5];
                }
            } elseif (isset($parts[4])) {
                $sub = $parts[4];
            }
            $this->executeInlineCheckout($chatId, $user, $productId, $dur, $sub, $callbackQueryId);
            return;
        }

        if ($data === 'check_balance_user' || $data === 'check_balance') {
            $this->sendUserBalanceCard($chatId, $user);
            return;
        }

        if ($data === 'check_stock_menu') {
            $this->sendCheckStockMenu($chatId, $user);
            return;
        }

        if (str_starts_with($data, 'stock_')) {
            $toolKey = str_replace('stock_', '', $data);
            $this->sendGameStockDetail($chatId, $user, $toolKey);
            return;
        }

        if ($data === 'add_balance_qr') {
            $this->sendDirectAddBalanceQR($chatId, $user);
            return;
        }

        if ($data === 'proof_reviews') {
            $this->sendProofInfo($chatId, $user);
            return;
        }

        if ($data === 'trigger_spin') {
            $this->executeSpinWheel($chatId, $user, $callbackQueryId);
            return;
        }

        if ($data === 'spin_dashboard') {
            $this->sendSpinWheelMenu($chatId, $user);
            return;
        }

        if ($data === 'refer_dashboard') {
            $this->sendReferralDashboard($chatId, $user);
            return;
        }

        if (str_starts_with($data, 'renew_key_')) {
            $keyId = (int)str_replace('renew_key_', '', $data);
            $this->handle1ClickRenewal($chatId, $user, $keyId);
            return;
        }

        if (str_starts_with($data, 'notify_stock_')) {
            $clean = str_replace('notify_stock_', '', $data);
            $parts = explode('_', $clean);
            $productId = (int)($parts[0] ?? 0);
            $duration = $parts[1] ?? '7d';
            $this->handleStockNotificationSubscribe($chatId, $user, $productId, $duration);
            return;
        }

        if (str_starts_with($data, 'quick_buy_')) {
            $clean = str_replace('quick_buy_', '', $data);
            $parts = explode('_', $clean);
            $productId = (int)($parts[0] ?? 0);
            $duration = $parts[1] ?? '7d';
            $this->handleQuickBuyStock($chatId, $user, $productId, $duration);
            return;
        }

        if ($data === 'my_keys_dashboard') {
            $this->sendMyKeysDashboard($chatId, $user);
            return;
        }

        if (str_starts_with($data, 'dl_')) {
            $this->handleDownloadCallback($chatId, $data, $user);
            return;
        }

        if (str_starts_with($data, 'bind_apk_')) {
            $this->handleBindApkCallback($chatId, $data, $user);
            return;
        }

        if ($data === 'ingame_menu') {
            $this->sendInGameItemsMenu($chatId, $user);
            return;
        }

        if ($data === 'proof_back_menu') {
            $this->sendWelcomeMenu($chatId, $user);
            return;
        }

        if (str_starts_with($data, 'ingame_')) {
            $this->handleInGameItemCallback($chatId, $data, $user);
            return;
        }

        if (str_starts_with($data, 'bind_photo_')) {
            $this->handleBindPhotoCallback($chatId, $data, $user);
            return;
        }

        $replyText = '';
        $keyboard = null;

        // Parse actions
        if ($data === 'purchase_product') {
            // Show list of products
            $products = Product::all();
            $replyText = "🛍️ **Select a Product to Purchase:**";
            $buttons = [];
            foreach ($products as $product) {
                $buttons[] = [
                    ['text' => '🎮 ' . $product->name, 'callback_data' => 'buy_p_' . $product->id]
                ];
            }
            $buttons[] = [['text' => '🔙 Back to Main Menu', 'callback_data' => 'main_menu']];
            $keyboard = ['inline_keyboard' => $buttons];

        } elseif (str_starts_with($data, 'buy_p_')) {
            $identifier = str_replace('buy_p_', '', $data);
            if (is_numeric($identifier)) {
                $product = Product::find((int)$identifier);
            } else {
                $map = [
                    'aim_carrom_king' => 'Aim Carrom King',
                    'snake_engine' => 'Snake Engine',
                    'kos' => 'KOS',
                    'aim_ai_engine' => 'Aim Ai Engine',
                    'bitaim_plus' => 'bitAIM+',
                ];
                $name = $map[$identifier] ?? null;
                $product = $name ? Product::where('name', $name)->first() : null;
            }

            if (!$product) {
                $replyText = "❌ **Product not found in system!**";
            } else {
                $isAimCarrom = str_contains(strtolower($product->name), 'carrom');
                $isSnake = str_contains(strtolower($product->name), 'snake');
                $prefix = $isAimCarrom ? 'mod_select_' : 'checkout_';

                if ($isSnake) {
                    $durationList = [
                        ['text' => '3 Days', 'code' => '3d'],
                        ['text' => '10 Days', 'code' => '10d'],
                        ['text' => '30 Days', 'code' => '30d'],
                        ['text' => '90 Days', 'code' => '90d'],
                    ];
                } else {
                    $durationList = [
                        ['text' => '3 Days', 'code' => '3d'],
                        ['text' => '7 Days', 'code' => '7d'],
                        ['text' => '30 Days', 'code' => '30d'],
                        ['text' => '90 Days', 'code' => '90d'],
                    ];
                }

                $buttons = [];
                foreach ($durationList as $d) {
                    $buttons[] = [
                        ['text' => $d['text'], 'callback_data' => "{$prefix}{$product->id}_{$d['code']}"]
                    ];
                }
                $buttons[] = [['text' => '🔙 Back to Main Menu', 'callback_data' => 'main_menu']];

                $replyText = "🛍️ **{$product->name}**\nSelect duration:";
                $keyboard = [
                    'inline_keyboard' => $buttons
                ];
            }

        } elseif (str_starts_with($data, 'mod_select_')) {
            // Format: mod_select_{productId}_{duration}
            $parts = explode('_', $data);
            $productId = (int) $parts[2];
            $duration = $parts[3];

            $product = Product::findOrFail($productId);
            $replyText = "Choose Game Mod:";
            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => 'Normal Mod', 'callback_data' => "checkout_{$productId}_{$duration}_normal"],
                        ['text' => 'Queue Mod', 'callback_data' => "checkout_{$productId}_{$duration}_queue"]
                    ],
                    [
                        ['text' => '🔙 Back to Main Menu', 'callback_data' => 'main_menu']
                    ]
                ]
            ];


        } elseif (str_starts_with($data, 'checkout_')) {
            // Format: checkout_{productId}_{duration} or checkout_{productId}_{duration}_{modType}
            $parts = explode('_', $data);
            $productId = (int) $parts[1];
            $duration = $parts[2];
            $modType = $parts[3] ?? null;

            $product = Product::findOrFail($productId);
            
            // Get price based on duration
            $priceField = 'price_' . $duration;
            $price = (float) ($product->$priceField ?? 0);

            $modLabel = $modType ? (' (' . ucfirst($modType) . ' Mod)') : '';
            $displayName = $product->name . $modLabel;

            $checkoutRes = DB::transaction(function () use ($user, $product, $price, $duration, $displayName) {
                $lockedUser = TelegramUser::where('id', $user->id)->lockForUpdate()->first();
                if ($lockedUser->balance < $price) {
                    return ['status' => 'insufficient_balance'];
                }

                $key = Key::where('product_id', $product->id)
                    ->where('duration', $duration)
                    ->where('is_sold', false)
                    ->lockForUpdate()
                    ->first();

                if (!$key) {
                    return ['status' => 'out_of_stock'];
                }

                // Deduct balance
                $lockedUser->balance -= $price;
                $lockedUser->save();

                // Mark key as sold
                $key->update([
                    'is_sold' => true,
                    'telegram_user_id' => $lockedUser->id,
                    'sold_at' => now(),
                    'expires_at' => Key::calculateExpiryFromDuration($duration, now()),
                    'renewal_alert_sent_at' => null,
                ]);

                // Create transaction log
                Transaction::create([
                    'telegram_user_id' => $lockedUser->id,
                    'type' => 'debit',
                    'amount' => $price,
                    'description' => "Purchased key for {$displayName} ({$duration})",
                ]);

                return [
                    'status' => 'success',
                    'key' => $key,
                    'new_balance' => $lockedUser->balance
                ];
            });

            if ($checkoutRes['status'] === 'insufficient_balance') {
                $replyText = "❌ **Insufficient Balance!**\n\nProduct: **{$displayName} ({$duration})**\nPrice: **₹" . number_format($price, 2) . "**\nYour Balance: **₹" . number_format($user->balance, 2) . "**\n\nPlease contact admin to top up your wallet.";
            } elseif ($checkoutRes['status'] === 'out_of_stock') {
                $replyText = "❌ Out of stock! Admin notified.";
                try {
                    $throttleKey = "oos_alert_{$user->id}_{$product->id}_{$duration}";
                    if (!Cache::has($throttleKey)) {
                        Cache::put($throttleKey, true, 120);
                        \App\Services\OrderAlertService::sendOutOfStockAlert([
                            'product_name' => $displayName,
                            'duration' => $duration,
                            'price' => $price,
                            'user_name' => $user->first_name ?: 'Customer',
                            'user_username' => $user->username,
                            'user_balance' => $user->balance,
                        ]);
                    }
                } catch (\Throwable $e) {}
            } else {
                $user->refresh();
                $key = $checkoutRes['key'];
                // Send instant Admin Email Alert
                \App\Services\OrderAlertService::sendSaleAlert([
                    'type' => 'NEW KEY PURCHASE',
                    'product_name' => $displayName,
                    'duration' => $duration,
                    'license_key' => $key->license_key,
                    'price' => $price,
                    'cost' => method_exists($product, 'getCostForDuration') ? $product->getCostForDuration($duration) : 0,
                    'user_name' => $user->first_name ?: ($user->username ?: 'Customer'),
                    'user_username' => $user->username,
                    'user_telegram_id' => $user->telegram_id,
                    'user_balance' => $user->balance,
                ]);

                $replyText = "🎉 **Purchase Successful!**\n\n📦 **Product:** {$displayName}\n📅 **Duration:** {$duration}\n💰 **Price:** ₹" . number_format($price, 2) . "\n\n🔑 **Your License Key:**\n`" . $key->license_key . "`\n\nThank you for choosing Khelo Store! 🎮";
            }

        } elseif ($data === 'main_menu') {
            $this->sendWelcomeMenu($chatId, $user);
            return;

        } elseif ($data === 'check_balance') {
            $replyText = "💰 **Check Balance**\n\nYour current reseller wallet balance is: **$" . number_format($user->balance, 2) . "**";
            $keyboard = [
                'inline_keyboard' => [
                    [['text' => '🔙 Back to Main Menu', 'callback_data' => 'main_menu']]
                ]
            ];

        } elseif ($data === 'verify_payment' || $data === 'verify_payment_qr') {
            $addedAmount = Cache::get("tg_user_{$chatId}_deposit_amount", 1);
            $user->balance += $addedAmount;
            $user->save();

            Transaction::create([
                'telegram_user_id' => $user->id,
                'type' => 'credit',
                'amount' => $addedAmount,
                'description' => 'UPI Payment Verified'
            ]);

            $timeStr = now()->format('Y-m-d H:i:s');
            $balStr = number_format($user->balance, 0, '.', '');

            $replyText = "✅ PAYMENT SUCCESSFUL! ✅\n\n💰 Amount Added: ₹{$addedAmount}\n💵 New Balance: ₹{$balStr}\n\n🕒 Time: {$timeStr}\n\nThank you for your payment! 🎉";
            $keyboard = null;

        } elseif ($data === 'add_balance') {
            $replyText = "➕ **Add Balance**\n\nTo top up your reseller wallet, please contact the administrator at @AdminUsername and state your Telegram ID: `{$user->telegram_id}`.\n\nOnce payment is confirmed, the admin will credit your wallet.";
            $keyboard = [
                'inline_keyboard' => [
                    [['text' => '🔙 Back to Main Menu', 'callback_data' => 'main_menu']]
                ]
            ];

        } elseif ($data === 'check_stock') {
            $products = Product::all();
            $replyText = "📦 **STOCK AVAILABILITY**\n_____________________\n\n";
            foreach ($products as $p) {
                $c1 = Key::where('product_id', $p->id)->where('duration', '1d')->where('is_sold', false)->count();
                $c7 = Key::where('product_id', $p->id)->where('duration', '7d')->where('is_sold', false)->count();
                $c15 = Key::where('product_id', $p->id)->where('duration', '15d')->where('is_sold', false)->count();
                $c30 = Key::where('product_id', $p->id)->where('duration', '30d')->where('is_sold', false)->count();
                $replyText .= "📌 **{$p->name}**\n• 1d: {$c1} keys | 7d: {$c7} keys\n• 15d: {$c15} keys | 30d: {$c30} keys\n\n";
            }
            $keyboard = [
                'inline_keyboard' => [
                    [['text' => '🔙 Back to Main Menu', 'callback_data' => 'main_menu']]
                ]
            ];

        } elseif (str_starts_with($data, 'stock_')) {
            $productMap = [
                'stock_kos' => 'KOS',
                'stock_snake' => 'Snake',
                'stock_aim_carrom' => 'Aim Carrom',
                'stock_aim_ai' => 'Aim AI',
            ];
            
            $productName = $productMap[$data] ?? null;
            
            if ($productName) {
                $product = Product::where('name', $productName)->first();
                if ($product) {
                    $c1 = Key::where('product_id', $product->id)->where('duration', '1d')->where('is_sold', false)->count();
                    $c7 = Key::where('product_id', $product->id)->where('duration', '7d')->where('is_sold', false)->count();
                    $c15 = Key::where('product_id', $product->id)->where('duration', '15d')->where('is_sold', false)->count();
                    $c30 = Key::where('product_id', $product->id)->where('duration', '30d')->where('is_sold', false)->count();
                    
                    $replyText = "📦 **" . strtoupper($productName) . " STOCK**\n" .
                                 "_____________________\n\n" .
                                 "📌 **{$product->name} Premium**\n" .
                                 "• 1 Day: {$c1} keys\n" .
                                 "• 7 Days: {$c7} keys\n" .
                                 "• 15 Days: {$c15} keys\n" .
                                 "• 30 Days: {$c30} keys";
                } else {
                    $replyText = "📦 **" . strtoupper($productName) . " STOCK**\n\nProduct not found in database.";
                }
            } else {
                $replyText = "Unknown stock requested.";
            }

            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => '🔙 Back to Stock Menu', 'callback_data' => 'check_stock']
                    ]
                ]
            ];

        } elseif ($data === 'purchase_history') {
            // Find recent 5 sold keys for this user
            $soldKeys = Key::with('product')
                ->where('telegram_user_id', $user->id)
                ->where('is_sold', true)
                ->latest('sold_at')
                ->take(5)
                ->get();

            if ($soldKeys->isEmpty()) {
                $replyText = "📜 **Purchase History**\n\nYou have not made any purchases yet. Your history is clean!";
            } else {
                $replyText = "📜 **Your Recent Purchases (Last 5):**\n\n";
                foreach ($soldKeys as $key) {
                    $replyText .= "🎮 **{$key->product->name} ({$key->duration})**\n🔑 Key: `{$key->license_key}`\n📅 Date: " . $key->sold_at->format('Y-m-d H:i') . "\n\n";
                }
            }

        } elseif ($data === 'reseller_profile') {
            // Calculate total purchases
            $totalPurchases = Transaction::where('telegram_user_id', $user->id)
                ->where('type', 'debit')
                ->sum('amount');
            $keysCount = Key::where('telegram_user_id', $user->id)->count();

            // Determine reseller rank
            $rank = 'Bronze';
            if ($totalPurchases >= 200) {
                $rank = 'Gold';
            } elseif ($totalPurchases >= 500) {
                $rank = 'Diamond';
            } elseif ($totalPurchases >= 50) {
                $rank = 'Silver';
            }

            $replyText = "📊 **My Reseller Profile**\n" .
                         "_____________________\n\n" .
                         "👤 **Name:** {$user->first_name} {$user->last_name}\n" .
                         "🆔 **Telegram ID:** `{$user->telegram_id}`\n" .
                         "💰 **Wallet Balance:** $" . number_format($user->balance, 2) . "\n" .
                         "📈 **Reseller Rank:** {$rank}\n" .
                         "🔑 **Keys Purchased:** {$keysCount} keys\n" .
                         "💸 **Total Lifetime Spend:** $" . number_format($totalPurchases, 2);

        } else {
            $replyText = "Unknown action requested.";
        }

        // Send response
        $payload = [
            'chat_id' => $chatId,
            'text' => $replyText,
            'parse_mode' => 'Markdown'
        ];

        if ($keyboard) {
            $payload['reply_markup'] = $keyboard;
        }

        $this->sendTelegramApi('sendMessage', $payload);
    }

    /**
     * Handle download button clicks from interactive download menu.
     */
    protected function getStoredApkFileId($key)
    {
        $file = storage_path('app/apk_storage.json');
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?: [];
            if (!empty($data[$key])) {
                return $data[$key];
            }
        }
        return Cache::get("{$key}_file_id");
    }

    protected function setStoredApkFileId($key, $fileId, $fileName = null)
    {
        Cache::forever("{$key}_file_id", $fileId);
        if ($fileName) {
            Cache::forever("{$key}_file_name", $fileName);
        }

        $file = storage_path('app/apk_storage.json');
        $data = file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];
        $data[$key] = $fileId;
        if ($fileName) {
            $data["{$key}_name"] = $fileName;
        }
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    protected function handleDownloadCallback($chatId, $data, TelegramUser $user)
    {
        if ($data === 'dl_back_menu') {
            $this->sendWelcomeMenu($chatId, $user);
            return;
        }

        $appMap = [
            'dl_aim_carrom' => ['key' => 'apk_aim_carrom', 'name' => 'Aim Carrom King', 'caption' => '📥 Download Aim Carrom King App', 'url' => 'https://khelostore.com/download/aim_carrom.apk'],
            'dl_snake' => ['key' => 'apk_snake', 'name' => 'Snake Engine', 'caption' => '📥 Download Snake Engine App', 'url' => 'https://khelostore.com/download/snake.apk'],
            'dl_kos' => ['key' => 'apk_kos', 'name' => 'KOS', 'caption' => '📥 Download KOS App', 'url' => 'https://khelostore.com/download/loader.apk'],
            'dl_aim_ai' => ['key' => 'apk_aim_ai', 'name' => 'Aim Ai Engine', 'caption' => '📥 Download Aim Ai Engine App', 'url' => 'https://khelostore.com/download/aim_ai.apk'],
            'dl_bitaim' => ['key' => 'apk_bitaim', 'name' => 'bitAIM+', 'caption' => '📥 Download bitAIM+ App', 'url' => 'https://khelostore.com/download/bitaim.apk'],
        ];

        if (!isset($appMap[$data])) {
            $this->sendWelcomeMenu($chatId, $user);
            return;
        }

        $app = $appMap[$data];
        $fileId = $this->getStoredApkFileId($app['key']);

        if ($fileId) {
            $this->sendTelegramApi('sendDocument', [
                'chat_id' => $chatId,
                'document' => $fileId,
                'caption' => $app['caption'],
            ]);
        } else {
            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => "📥 **{$app['name']} APK Download**\n\n" .
                          "Direct APK download karne ke liye hamare official Telegram channel ko join karein:\n" .
                          "👉 https://t.me/shababgaming",
                'parse_mode' => 'Markdown',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => '📢 Join Channel & Download APK', 'url' => 'https://t.me/shababgaming']
                        ]
                    ]
                ]
            ]);
        }
    }

    /**
     * Handle binding newly uploaded APK to a specific game button.
     */
    protected function handleBindApkCallback($chatId, $data, TelegramUser $user)
    {
        $tempDoc = Cache::get("temp_uploaded_doc_{$chatId}");
        if (!$tempDoc) {
            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => "⚠️ Upload session expired. Please send the APK file again to link it."
            ]);
            return;
        }

        $appKeyMap = [
            'bind_apk_aim_carrom' => ['key' => 'apk_aim_carrom', 'name' => 'Aim Carrom King'],
            'bind_apk_snake' => ['key' => 'apk_snake', 'name' => 'Snake Engine'],
            'bind_apk_kos' => ['key' => 'apk_kos', 'name' => 'KOS'],
            'bind_apk_aim_ai' => ['key' => 'apk_aim_ai', 'name' => 'Aim Ai Engine'],
            'bind_apk_bitaim' => ['key' => 'apk_bitaim', 'name' => 'bitAIM+'],
        ];

        if (isset($appKeyMap[$data])) {
            $target = $appKeyMap[$data];
            $this->setStoredApkFileId($target['key'], $tempDoc['file_id'], $tempDoc['file_name']);

            $msg = "✅ **Successfully Linked to {$target['name']}!** 🎉\n\n" .
                   "📦 **File:** `{$tempDoc['file_name']}`\n" .
                   "⚖️ **Size:** {$tempDoc['size']} MB\n" .
                   "🔑 **File ID:** `{$tempDoc['file_id']}`\n\n" .
                   "Ab jab bhi koi user Download menu me **✅ {$target['name']}** dabayega, Telegram direct yahi APK send karega! 🚀";

            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => $msg,
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    /**
     * Get In-Game Items from persistent JSON storage with default fallback.
     */
    protected function getInGameItems()
    {
        return \App\Http\Controllers\Admin\AdminInGameItemController::getItems();
    }

    /**
     * Save In-Game Items to persistent JSON storage.
     */
    protected function saveInGameItems(array $items)
    {
        \App\Http\Controllers\Admin\AdminInGameItemController::saveItems($items);
    }

    /**
     * Display Daily Spin Wheel Menu with Interactive WebApp Button.
     */
    protected function sendSpinWheelMenu($chatId, TelegramUser $user)
    {
        $canSpin = $user->canSpinToday();
        $appUrl = env('APP_URL', 'http://192.168.3.128:8000');
        if (!str_contains($appUrl, '192.168') && !str_starts_with($appUrl, 'https://') && !str_contains($appUrl, 'localhost')) {
            $appUrl = 'http://192.168.3.128:8000';
        }
        $webAppUrl = rtrim($appUrl, '/') . '/telegram/spin-wheel?telegram_id=' . $chatId;
        $isHttps = str_starts_with($webAppUrl, 'https://');

        if ($canSpin) {
            $text = "🎰 **DAILY LUCKY WHEEL OF FORTUNE** 🎡\n" .
                    "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
                    "Aapka rozana ka **1 FREE SPIN** ready hai! 🎁\n" .
                    "Aap jeet sakte hain **₹2 se ₹50** tak ka direct Wallet Cash!\n\n" .
                    "💰 **Wheel Rewards:**\n" .
                    "💎 ₹50 *(Mega Jackpot)*\n" .
                    "🚀 ₹25 *(Grand Prize)*\n" .
                    "👑 ₹15 *(VIP Reward)*\n" .
                    "🧿 ₹10 *(Mega Prize)*\n" .
                    "🔥 ₹7 *(Super Win)*\n" .
                    "⚡ ₹5 *(Power Prize)*\n" .
                    "✨ ₹3 *(Lucky Bonus)*\n" .
                    "🎁 ₹2 *(Starter Luck)*\n\n" .
                    "👇 **Neeche diye button par click karke LIVE 3D Wheel ghumayein:**";

            $inlineButtons = [];
            if ($isHttps) {
                $inlineButtons[] = [ ['text' => '🎡 OPEN LUCKY WHEEL (3D SPIN) 🎡', 'web_app' => ['url' => $webAppUrl]] ];
            } else {
                $inlineButtons[] = [ ['text' => '🎡 OPEN LUCKY WHEEL (3D SPIN) 🎡', 'url' => $webAppUrl] ];
            }
            $inlineButtons[] = [ ['text' => '🏠 Back to Menu', 'callback_data' => 'dl_back_menu'] ];

            $keyboard = ['inline_keyboard' => $inlineButtons];
        } else {
            $timeLeft = $user->formattedTimeUntilNextSpin();
            $text = "⏳ **DAILY SPIN ALREADY USED!** 🎡\n" .
                    "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
                    "Aapne aaj ka free spin use kar liya hai.\n\n" .
                    "⏰ **Next Free Spin in:** `{$timeLeft}`\n\n" .
                    "Rozana aakar free wallet balance win karein!";

            $inlineButtons = [];
            if ($isHttps) {
                $inlineButtons[] = [ ['text' => '🎡 View 3D Wheel & Timer', 'web_app' => ['url' => $webAppUrl]] ];
            } else {
                $inlineButtons[] = [ ['text' => '🎡 View 3D Wheel & Timer', 'url' => $webAppUrl] ];
            }
            $inlineButtons[] = [ ['text' => '🏠 Back to Menu', 'callback_data' => 'dl_back_menu'] ];

            $keyboard = ['inline_keyboard' => $inlineButtons];
        }

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    /**
     * Send rich dynamic interactive inline product card right in chat.
     */
    protected function sendProductInlineCard($chatId, TelegramUser $user, string $toolKey)
    {
        $toolKey = strtolower(trim($toolKey));
        $prod = null;
        $title = "";
        $gameName = "Carrom Pool";
        $desc = "";
        $planButtons = [];

        if (str_contains($toolKey, 'carrom')) {
            $prod = Product::find(1) ?: Product::where('name', 'like', '%Carrom%')->first();
            $title = "🎯 **AIM CARROM KING** 🎯\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━";

            if ($prod) {
                $planButtons = [
                    [ ['text' => "3 Days", 'callback_data' => "carrom_dur_3d"] ],
                    [ ['text' => "7 Days", 'callback_data' => "carrom_dur_7d"] ],
                    [ ['text' => "30 Days", 'callback_data' => "carrom_dur_30d"] ],
                    [ ['text' => "90 Days", 'callback_data' => "carrom_dur_90d"] ]
                ];
            }
        } elseif (str_contains($toolKey, 'snake')) {
            $prod = Product::find(2) ?: Product::where('name', 'like', '%Snake%')->first();
            $title = "🐍 **SNAKE ENGINE VIP** 🐍\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━";

            if ($prod) {
                $planButtons = [
                    [ ['text' => "3 Days", 'callback_data' => "snake_dur_3d"] ],
                    [ ['text' => "10 Days", 'callback_data' => "snake_dur_10d"] ],
                    [ ['text' => "30 Days", 'callback_data' => "snake_dur_30d"] ],
                    [ ['text' => "90 Days", 'callback_data' => "snake_dur_90d"] ]
                ];
            }
        } elseif (str_contains($toolKey, 'kos')) {
            $prod = Product::find(3) ?: Product::where('name', 'like', '%KOS%')->first();
            $title = "🎮 **KOS ENGINE VIP** 🎮\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━";

            if ($prod) {
                $planButtons = [
                    [ ['text' => "1 Day", 'callback_data' => "kos_dur_1d"] ],
                    [ ['text' => "7 Days", 'callback_data' => "kos_dur_7d"] ],
                    [ ['text' => "15 Days", 'callback_data' => "kos_dur_15d"] ],
                    [ ['text' => "30 Days", 'callback_data' => "kos_dur_30d"] ]
                ];
            }
        } elseif (str_contains($toolKey, 'aim_ai') || str_contains($toolKey, 'aim ai')) {
            $prod = Product::find(4) ?: Product::where('name', 'like', '%Aim Ai%')->first();
            $title = "🤖 **AIM AI ENGINE** 🤖\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━";

            if ($prod) {
                $planButtons = [
                    [ ['text' => "1 Day", 'callback_data' => "buy_item_{$prod->id}_1d"] ],
                    [ ['text' => "3 Days", 'callback_data' => "buy_item_{$prod->id}_3d"] ],
                    [ ['text' => "7 Days", 'callback_data' => "buy_item_{$prod->id}_7d"] ],
                    [ ['text' => "15 Days", 'callback_data' => "buy_item_{$prod->id}_15d"] ],
                    [ ['text' => "30 Days", 'callback_data' => "buy_item_{$prod->id}_30d"] ],
                    [ ['text' => "90 Days", 'callback_data' => "buy_item_{$prod->id}_90d"] ]
                ];
            }
        } elseif (str_contains($toolKey, 'bitaim')) {
            $prod = Product::find(78) ?: Product::where('name', 'like', '%bitAIM%')->first();
            $title = "🚀 **bitAIM+ PREMIUM** 🚀\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━";

            if ($prod) {
                $planButtons = [
                    [ ['text' => "1 Week", 'callback_data' => "buy_item_{$prod->id}_7d"] ],
                    [ ['text' => "1 Month", 'callback_data' => "buy_item_{$prod->id}_30d"] ],
                    [ ['text' => "3 Months", 'callback_data' => "buy_item_{$prod->id}_90d"] ],
                    [ ['text' => "💎 Lifetime", 'callback_data' => "buy_item_{$prod->id}_lifetime"] ]
                ];
            }
        }

        $planButtons[] = [
            ['text' => '📥 Download APK', 'callback_data' => "dl_{$toolKey}"],
            ['text' => '🏠 Back to Menu', 'callback_data' => 'main_menu']
        ];

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $title,
            'parse_mode' => 'Markdown',
            'reply_markup' => ['inline_keyboard' => $planButtons]
        ]);
    }

    /**
     * Send Aim Carrom King Mod Selection (Normal Mod / Queue Mod)
     */
    protected function sendCarromModSelection($chatId, TelegramUser $user, string $dur)
    {
        $prod = Product::find(1) ?: Product::where('name', 'like', '%Carrom%')->first();
        $prodId = $prod ? $prod->id : 1;

        $keyboard = [
            'inline_keyboard' => [
                [ ['text' => 'Normal Mod', 'callback_data' => "buy_item_{$prodId}_{$dur}"] ],
                [ ['text' => 'Queue Mod', 'callback_data' => "buy_item_{$prodId}_{$dur}_queue"] ],
                [ ['text' => '🔙 Back to Plans', 'callback_data' => 'open_tool_aim_carrom'] ]
            ]
        ];

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => "🎯 **AIM CARROM KING** 🎯\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nChoose Game Mod:",
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    /**
     * Send Snake Engine Game Selection (8 Ball Pool / Carrom Pool / Soccer Star)
     */
    protected function sendSnakeGameSelection($chatId, TelegramUser $user, string $dur)
    {
        $prodCarrom = Product::find(2) ?: Product::where('name', 'like', '%Snake%')->first();
        $prod8Ball = Product::find(75) ?: Product::where('name', 'like', '%Snake%8%')->first();
        $prodSoccer = Product::find(80) ?: Product::where('name', 'like', '%Snake%Soccer%')->orWhere('game_name', 'like', '%Soccer%')->first();
        $carromId = $prodCarrom ? $prodCarrom->id : 2;
        $eightBallId = $prod8Ball ? $prod8Ball->id : 75;
        $soccerId = $prodSoccer ? $prodSoccer->id : 80;

        $keyboard = [
            'inline_keyboard' => [
                [ ['text' => '8 Ball Pool', 'callback_data' => "buy_item_{$eightBallId}_{$dur}_8ball"] ],
                [ ['text' => 'Carrom Pool', 'callback_data' => "buy_item_{$carromId}_{$dur}_carrom"] ],
                [ ['text' => 'Soccer Star', 'callback_data' => "buy_item_{$soccerId}_{$dur}_soccer"] ],
                [ ['text' => '🔙 Back to Plans', 'callback_data' => 'open_tool_snake'] ]
            ]
        ];

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => "🐍 **SNAKE ENGINE VIP** 🐍\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nChoose Game:",
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    /**
     * Send KOS Game Selection (Carrom Pool / 8 Ball Pool)
     */
    protected function sendKosGameSelection($chatId, TelegramUser $user, string $dur)
    {
        $prodCarrom = Product::find(3) ?: Product::where('name', 'like', '%KOS%')->first();
        $prod8Ball = Product::find(76) ?: Product::where('name', 'like', '%KOS%8%')->first();
        $carromId = $prodCarrom ? $prodCarrom->id : 3;
        $eightBallId = $prod8Ball ? $prod8Ball->id : 76;

        $keyboard = [
            'inline_keyboard' => [
                [ ['text' => 'Carrom Pool', 'callback_data' => "buy_item_{$carromId}_{$dur}_carrom"] ],
                [ ['text' => '8 Ball Pool', 'callback_data' => "buy_item_{$eightBallId}_{$dur}_8ball"] ],
                [ ['text' => '🔙 Back to Plans', 'callback_data' => 'open_tool_kos'] ]
            ]
        ];

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => "🎮 **KOS ENGINE VIP** 🎮\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nChoose Game:",
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    /**
     * Prompt 1-Click Order Confirmation in Chat.
     */
    protected function promptInlineCheckout($chatId, TelegramUser $user, int $productId, string $duration, $subOption = null)
    {
        $product = Product::find($productId);
        if (!$product) {
            $this->sendWelcomeMenu($chatId, $user, "❌ Product not found.");
            return;
        }

        $price = (float) $product->getEffectivePriceForTelegramUser($user, $duration);
        $balStr = number_format($user->balance, 2);
        $priceStr = number_format($price, 2);

        $durLabel = match($duration) {
            '1d' => '1 Day',
            '3d' => '3 Days',
            '7d' => '7 Days (1 Week)',
            '10d' => '10 Days',
            '15d' => '15 Days',
            '30d' => '30 Days (1 Month)',
            '90d' => '90 Days (3 Months)',
            '3d_queue' => '3 Days (Auto Queue Mod)',
            '7d_queue' => '7 Days (Auto Queue Mod)',
            '30d_queue' => '30 Days (Auto Queue Mod)',
            '90d_queue' => '90 Days (Auto Queue Mod)',
            'lifetime' => '💎 Lifetime Access',
            default => $duration
        };

        $appName = match((int)$product->id) {
            1 => 'Aim Carrom King',
            2, 75, 80 => 'Snake',
            3, 76 => 'KOS',
            4 => 'Aim Ai',
            78 => 'bitAIM+',
            default => trim(str_replace(' Engine', '', preg_replace('/\(.*\)/', '', $product->name)))
        };

        $gameName = match($subOption) {
            '8ball' => '8 Ball Pool',
            'carrom' => 'Carrom Pool',
            'soccer' => 'Soccer Star',
            default => ($product->game_name ?: 'Carrom Pool')
        };
        if (str_contains($duration, 'queue')) {
            $gameName .= ' (Queue Mod)';
        }

        $durClean = match(str_replace('_queue', '', $duration)) {
            '1d' => '1 Day(s)',
            '3d' => '3 Day(s)',
            '7d' => '7 Day(s)',
            '10d' => '10 Day(s)',
            '15d' => '15 Day(s)',
            '30d' => '30 Day(s)',
            '90d' => '90 Day(s)',
            'lifetime' => 'Lifetime',
            default => $duration
        };

        $execCallback = "exec_buy_{$product->id}_{$duration}" . ($subOption ? "_{$subOption}" : "");

        $balClean = ($user->balance == (int)$user->balance) ? (int)$user->balance : number_format($user->balance, 2);
        $priceClean = ($price == (int)$price) ? (int)$price : number_format($price, 2);

        // Step 1: Pre-check wallet balance first!
        if ($user->balance < $price) {
            $shortfall = $price - $user->balance;
            $shortfallClean = ($shortfall == (int)$shortfall) ? (int)$shortfall : number_format($shortfall, 2);

            $lowBalText = "⚠️ **Insufficient Wallet Balance!**\n" .
                          "────────────────────────\n" .
                          "• **App :** {$appName}\n" .
                          "• **Duration :** {$durClean}\n" .
                          "• **Price :** `₹{$priceClean}`\n\n" .
                          "💵 **Your Balance :** `₹{$balClean}`\n" .
                          "❌ **Need :** `₹{$shortfallClean}` more\n" .
                          "────────────────────────\n" .
                          "👉 **Please add balance to your wallet to buy:**";

            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => $lowBalText,
                'parse_mode' => 'Markdown',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => '💳 Add Balance (QR / UPI)', 'callback_data' => 'add_balance_qr']
                        ],
                        [
                            ['text' => '🏠 Main Menu', 'callback_data' => 'main_menu']
                        ]
                    ]
                ]
            ]);
            return;
        }

        // Step 2: Pre-check stock availability (only for users with sufficient balance)
        $isBitAim = str_contains(strtolower($product->name), 'bitaim');
        if (!$isBitAim) {
            $cleanDuration = str_replace('_queue', '', $duration);
            $hasStock = Key::where('product_id', $product->id)
                ->where(function($q) use ($duration, $cleanDuration) {
                    $q->where('duration', $duration)->orWhere('duration', $cleanDuration);
                })
                ->where('is_sold', false)
                ->exists();

            if (!$hasStock) {
                $this->sendTelegramApi('sendMessage', [
                    'chat_id' => $chatId,
                    'text' => "❌ Out of stock! Admin notified.",
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [ ['text' => '🏠 Main Menu', 'callback_data' => 'main_menu'] ]
                        ]
                    ]
                ]);
                $sound = \App\Http\Controllers\Admin\AdminSoundController::getEventSound('out_of_stock');
                if ($sound) {
                    $this->sendMemeVoiceReaction($chatId, $sound);
                }

                // Send instant Out-of-Stock email alert to Admin
                try {
                    $throttleKey = "oos_alert_{$user->id}_{$product->id}_{$duration}";
                    if (!Cache::has($throttleKey)) {
                        Cache::put($throttleKey, true, 120);
                        \App\Services\OrderAlertService::sendOutOfStockAlert([
                            'product_name' => $appName . ($gameName ? " - {$gameName}" : ""),
                            'duration' => $durClean,
                            'price' => $price,
                            'user_name' => $user->first_name ?: 'Customer',
                            'user_username' => $user->username,
                            'user_balance' => $user->balance,
                        ]);
                    }
                } catch (\Throwable $e) {}

                // Auto register user for back-in-stock alert
                try {
                    \App\Models\StockSubscription::updateOrCreate(
                        [
                            'telegram_user_id' => $user->id,
                            'product_id' => $product->id,
                            'duration' => strtolower(str_replace('_queue', '', $duration)),
                        ],
                        [
                            'is_notified' => false,
                            'notified_at' => null,
                        ]
                    );
                } catch (\Throwable $e) {}

                return;
            }
        }

        $balClean = ($user->balance == (int)$user->balance) ? (int)$user->balance : number_format($user->balance, 2);
        $priceClean = ($price == (int)$price) ? (int)$price : number_format($price, 2);

        $isBitAim = str_contains(strtolower($appName), 'bitaim');

        if ($isBitAim) {
            $confirmText = "🚀 **Confirm bitAIM+ Purchase**\n" .
                           "━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                           "• **Plan :** {$durClean}\n" .
                           "• **Price :** `₹{$priceClean}`\n" .
                           "• **Your Balance :** `₹{$balClean}`\n" .
                           "━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                           "👉 **Click Confirm below:**";
        } else {
            $confirmText = "⚡ **Confirm Your Order**\n" .
                           "────────────────────────\n" .
                           "• **App :** {$appName}\n" .
                           "• **Duration :** {$durClean}\n" .
                           "• **Price :** `₹{$priceClean}`\n\n" .
                           "💵 **Your Balance :** `₹{$balClean}`\n" .
                           "────────────────────────\n" .
                           "👉 **Click Confirm to get instant key:**";
        }

        $buttons = [
            'inline_keyboard' => [
                [
                    ['text' => "⚡ Confirm & Buy Now (₹{$priceClean})", 'callback_data' => $execCallback]
                ],
                [
                    ['text' => "❌ Cancel / Back to Menu", 'callback_data' => 'main_menu']
                ]
            ]
        ];

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $confirmText,
            'parse_mode' => 'Markdown',
            'reply_markup' => $buttons
        ]);
    }

    /**
     * Execute In-Chat Instant Key Purchase.
     */
    protected function executeInlineCheckout($chatId, TelegramUser $user, int $productId, string $duration, $subOption = null, $callbackQueryId = null)
    {
        $product = Product::find($productId);
        if (!$product) {
            $this->sendWelcomeMenu($chatId, $user, "❌ Product not found.");
            return;
        }

        $price = (float) $product->getEffectivePriceForTelegramUser($user, $duration);

        if ($user->balance < $price) {
            $reqAmt = $price - $user->balance;
            $reqClean = ($reqAmt == (int)$reqAmt) ? (int)$reqAmt : number_format($reqAmt, 2);
            $balClean = ($user->balance == (int)$user->balance) ? (int)$user->balance : number_format($user->balance, 2);
            $priceClean = ($price == (int)$price) ? (int)$price : number_format($price, 2);

            $insufficientText = "❌ **INSUFFICIENT BALANCE!** ⚠️\n" .
                                "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                                "💰 **Required:** `₹{$priceClean}`\n" .
                                "💵 **Your Balance:** `₹{$balClean}`\n" .
                                "⚠️ **Need to Add:** `₹{$reqClean}`\n\n" .
                                "👇 Click below to add funds instantly via UPI QR:";
            
            $buttons = [
                'inline_keyboard' => [
                    [ ['text' => '💳 Add Balance via QR', 'callback_data' => 'add_balance_qr'] ],
                    [ ['text' => '🏠 Back to Menu', 'callback_data' => 'main_menu'] ]
                ]
            ];

            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => $insufficientText,
                'parse_mode' => 'Markdown',
                'reply_markup' => $buttons
            ]);
            return;
        }

        $isBitAim = str_contains(strtolower($product->name), 'bitaim');

        if ($isBitAim) {
            // Deduct balance and record transaction inside lock
            $bitaimRes = DB::transaction(function () use ($user, $price, $product, $duration) {
                $lockedUser = TelegramUser::where('id', $user->id)->lockForUpdate()->first();
                if ($lockedUser->balance < $price) {
                    return ['status' => 'insufficient_balance'];
                }

                $lockedUser->balance -= $price;
                $lockedUser->save();

                Transaction::create([
                    'telegram_user_id' => $lockedUser->id,
                    'type' => 'debit',
                    'amount' => $price,
                    'description' => "Purchased bitAIM+ ({$duration})",
                ]);

                return ['status' => 'success', 'balance' => $lockedUser->balance];
            });

            if ($bitaimRes['status'] === 'insufficient_balance') {
                $this->sendTelegramApi('sendMessage', [
                    'chat_id' => $chatId,
                    'text' => "❌ Insufficient balance! Please top up your wallet."
                ]);
                return;
            }

            $user->refresh();

            Cache::put("tg_user_{$chatId}_awaiting_email", true, 1800);
            Cache::put("tg_user_{$chatId}_bitaim_purchase", [
                'duration' => $duration,
                'price' => $price
            ], 1800);

            $amtDeducted = number_format($price, 0);
            $remBalance = number_format($user->balance, 0);

            $msg1 = "✅ Request received! ✅\n\n💰 Amount Deducted: ₹{$amtDeducted}\n💵 Remaining Balance: ₹{$remBalance}\n\nPlease check your email for further instructions.";

            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => $msg1
            ]);

            $msg2 = "📧 Please send your Email Address:\n\nWe will send your bitAIM+ Carrom subscription credentials to this email.\n\n(Simply type your email address)";

            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => $msg2
            ]);

            return;
        }

        // Atomically lock user & key inside DB Transaction
        $cleanDuration = str_replace('_queue', '', $duration);

        $checkoutResult = DB::transaction(function () use ($user, $price, $product, $duration, $cleanDuration) {
            $lockedUser = TelegramUser::where('id', $user->id)->lockForUpdate()->first();

            if ($lockedUser->balance < $price) {
                return ['status' => 'insufficient_balance'];
            }

            $key = Key::where('product_id', $product->id)
                ->where(function($q) use ($duration, $cleanDuration) {
                    $q->where('duration', $duration)->orWhere('duration', $cleanDuration);
                })
                ->where('is_sold', false)
                ->lockForUpdate()
                ->first();

            if (!$key) {
                return ['status' => 'out_of_stock'];
            }

            // Deduct balance
            $lockedUser->balance -= $price;
            $lockedUser->save();

            // Assign key
            $key->is_sold = true;
            $key->telegram_user_id = $lockedUser->id;
            $key->sold_at = now();
            $key->expires_at = Key::calculateExpiryFromDuration($duration, now());
            $key->renewal_alert_sent_at = null;
            $key->save();

            Transaction::create([
                'telegram_user_id' => $lockedUser->id,
                'type' => 'debit',
                'amount' => $price,
                'description' => "Purchased {$product->name} key ({$duration})",
            ]);

            return [
                'status' => 'success',
                'key' => $key,
                'new_balance' => $lockedUser->balance
            ];
        });

        if ($checkoutResult['status'] === 'insufficient_balance') {
            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => "❌ Insufficient balance! Please top up your wallet."
            ]);
            return;
        }

        if ($checkoutResult['status'] === 'out_of_stock') {
            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => "❌ Out of stock! Admin notified."
            ]);
            $sound = \App\Http\Controllers\Admin\AdminSoundController::getEventSound('out_of_stock');
            if ($sound) {
                $this->sendMemeVoiceReaction($chatId, $sound);
            }

            // Send instant Out-of-Stock email alert to Admin
            try {
                $throttleKey = "oos_alert_{$user->id}_{$product->id}_{$duration}";
                if (!Cache::has($throttleKey)) {
                    Cache::put($throttleKey, true, 120);
                    \App\Services\OrderAlertService::sendOutOfStockAlert([
                        'product_name' => $product->name,
                        'duration' => $duration,
                        'price' => $price,
                        'user_name' => $user->first_name ?: 'Customer',
                        'user_username' => $user->username,
                        'user_balance' => $user->balance,
                    ]);
                }
            } catch (\Throwable $e) {}

            // Auto register user for back-in-stock alert
            try {
                \App\Models\StockSubscription::updateOrCreate(
                    [
                        'telegram_user_id' => $user->id,
                        'product_id' => $product->id,
                        'duration' => strtolower(str_replace('_queue', '', $duration)),
                    ],
                    [
                        'is_notified' => false,
                        'notified_at' => null,
                    ]
                );
            } catch (\Throwable $e) {}

            return;
        }

        $user->refresh();
        $key = $checkoutResult['key'];

        $keyCode = $key->license_key;
        $balClean = ($user->balance == (int)$user->balance) ? (int)$user->balance : number_format($user->balance, 2);
        $priceClean = ($price == (int)$price) ? (int)$price : number_format($price, 2);

        $subLabel = '';
        if ($subOption) {
            $subLabel = match($subOption) {
                '8ball' => ' (8 Ball Pool)',
                'carrom' => ' (Carrom Pool)',
                'soccer' => ' (Soccer Star)',
                default => " ({$subOption})"
            };
        }
        $durClean = match(strtolower($duration)) {
            '1d', '1' => '1 Days',
            '3d', '3' => '3 Days',
            '7d', '7' => '7 Days',
            '10d', '10' => '10 Days',
            '15d', '15' => '15 Days',
            '30d', '30' => '30 Days',
            '60d', '60' => '60 Days',
            default => strtoupper($duration)
        };

        $prodDisplayName = $product->name . $subLabel;

        $successCard = "✅ **{$durClean} {$prodDisplayName} KEY** ✅\n\n" .
                       "🔑 `{$keyCode}`\n\n" .
                       "💰 **Remaining Balance:** ₹{$balClean}\n\n" .
                       "Thank you for your purchase!";

        $dlCallback = match((int)$product->id) {
            1 => 'dl_aim_carrom',
            2, 75, 80 => 'dl_snake',
            3, 76 => 'dl_kos',
            4 => 'dl_aim_ai',
            default => 'dl_aim_carrom'
        };

        $buttons = [
            'inline_keyboard' => [
                [
                    ['text' => '📥 Download APK', 'callback_data' => $dlCallback],
                    ['text' => '🔑 My Active Keys', 'callback_data' => 'my_keys_dashboard']
                ],
                [
                    ['text' => '🏠 Main Menu', 'callback_data' => 'main_menu']
                ]
            ]
        ];

        // 1. Deliver key to user instantly
        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $successCard,
            'parse_mode' => 'Markdown',
            'reply_markup' => $buttons
        ]);
        $sound = \App\Http\Controllers\Admin\AdminSoundController::getEventSound('key_purchase');
        if ($sound) {
            $this->sendMemeVoiceReaction($chatId, $sound);
        }

        // 2. Send sale alert without blocking user
        try {
            \App\Services\OrderAlertService::sendSaleAlert([
                'type' => 'NEW SALE (BOT)',
                'product_name' => $product->name,
                'duration' => $duration,
                'license_key' => $keyCode,
                'price' => $price,
                'cost' => (float)($product->cost_price ?? 0),
                'user_name' => $user->first_name,
                'user_username' => $user->username
            ]);
        } catch (\Throwable $e) {}
    }

    /**
     * Send User Balance Card (Exact Name + Balance format).
     */
    protected function sendUserBalanceCard($chatId, TelegramUser $user)
    {
        $userName = trim($user->first_name . ' ' . ($user->last_name ?? ''));
        if (empty($userName)) {
            $userName = $user->username ?: 'Customer';
        }
        $balClean = ($user->balance == (int)$user->balance) ? (int)$user->balance : number_format($user->balance, 2);

        $text = "👤 **{$userName}**\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n💰 **Your balance:** `₹{$balClean}`";

        $keyboard = [
            'inline_keyboard' => [
                [ ['text' => '💳 Add Balance', 'callback_data' => 'add_balance_qr'] ],
                [ ['text' => '🏠 Main Menu', 'callback_data' => 'main_menu'] ]
            ]
        ];

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    /**
     * Send Check Stock Game Selection Menu.
     */
    protected function sendCheckStockMenu($chatId, TelegramUser $user)
    {
        $text = "📦 **CHECK STOCK** 📦\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nSelect game:";

        $keyboard = [
            'inline_keyboard' => [
                [ ['text' => '🤖 Aim Ai Engine', 'callback_data' => 'stock_aim_ai'] ],
                [ ['text' => '🎮 KOS', 'callback_data' => 'stock_kos'] ],
                [ ['text' => '🐍 Snake Engine', 'callback_data' => 'stock_snake'] ],
                [ ['text' => '🎯 Aim Carrom King', 'callback_data' => 'stock_aim_carrom'] ],
                [ ['text' => '🏠 Main Menu', 'callback_data' => 'main_menu'] ]
            ]
        ];

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    /**
     * Send Detailed Stock Report for Selected Game.
     */
    protected function sendGameStockDetail($chatId, TelegramUser $user, string $toolKey)
    {
        $toolKey = strtolower(trim($toolKey));
        $text = "";

        if (str_contains($toolKey, 'carrom')) {
            $prod = Product::find(1) ?: Product::where('name', 'like', '%Carrom%')->first();
            $pid = $prod ? $prod->id : 1;

            $n3 = Key::where('product_id', $pid)->where('duration', '3d')->where('is_sold', false)->count();
            $n7 = Key::where('product_id', $pid)->where('duration', '7d')->where('is_sold', false)->count();
            $n30 = Key::where('product_id', $pid)->where('duration', '30d')->where('is_sold', false)->count();
            $n90 = Key::where('product_id', $pid)->where('duration', '90d')->where('is_sold', false)->count();

            $q3 = Key::where('product_id', $pid)->where('duration', '3d_queue')->where('is_sold', false)->count();
            $q7 = Key::where('product_id', $pid)->where('duration', '7d_queue')->where('is_sold', false)->count();
            $q30 = Key::where('product_id', $pid)->where('duration', '30d_queue')->where('is_sold', false)->count();
            $q90 = Key::where('product_id', $pid)->where('duration', '90d_queue')->where('is_sold', false)->count();

            $text = "🎯 **AIM CARROM KING STOCK** 🎯\n" .
                    "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
                    "**Normal:**\n" .
                    "  3 days: `{$n3} keys`\n" .
                    "  7 days: `{$n7} keys`\n" .
                    "  30 days: `{$n30} keys`\n" .
                    "  90 days: `{$n90} keys`\n\n" .
                    "**Queue:**\n" .
                    "  3 days: `{$q3} keys`\n" .
                    "  7 days: `{$q7} keys`\n" .
                    "  30 days: `{$q30} keys`\n" .
                    "  90 days: `{$q90} keys`";

        } elseif (str_contains($toolKey, 'snake')) {
            $prodCarrom = Product::find(2) ?: Product::where('name', 'like', '%Snake%')->first();
            $prod8Ball = Product::find(75) ?: Product::where('name', 'like', '%Snake%8%')->first();
            $prodSoccer = Product::find(80) ?: Product::where('name', 'like', '%Snake%Soccer%')->orWhere('game_name', 'like', '%Soccer%')->first();
            $cid = $prodCarrom ? $prodCarrom->id : 2;
            $eid = $prod8Ball ? $prod8Ball->id : 75;
            $sid = $prodSoccer ? $prodSoccer->id : 80;

            $c3 = Key::where('product_id', $cid)->where('duration', '3d')->where('is_sold', false)->count();
            $c10 = Key::where('product_id', $cid)->where('duration', '10d')->where('is_sold', false)->count();
            $c30 = Key::where('product_id', $cid)->where('duration', '30d')->where('is_sold', false)->count();
            $c90 = Key::where('product_id', $cid)->where('duration', '90d')->where('is_sold', false)->count();

            $e3 = Key::where('product_id', $eid)->where('duration', '3d')->where('is_sold', false)->count();
            $e10 = Key::where('product_id', $eid)->where('duration', '10d')->where('is_sold', false)->count();
            $e30 = Key::where('product_id', $eid)->where('duration', '30d')->where('is_sold', false)->count();
            $e90 = Key::where('product_id', $eid)->where('duration', '90d')->where('is_sold', false)->count();

            $s3 = $sid ? Key::where('product_id', $sid)->where('duration', '3d')->where('is_sold', false)->count() : 0;
            $s10 = $sid ? Key::where('product_id', $sid)->where('duration', '10d')->where('is_sold', false)->count() : 0;
            $s30 = $sid ? Key::where('product_id', $sid)->where('duration', '30d')->where('is_sold', false)->count() : 0;

            $text = "🐍 **SNAKE ENGINE STOCK** 🐍\n" .
                    "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
                    "**Carrom Pool:**\n" .
                    "  3 days: `{$c3} keys`\n" .
                    "  10 days: `{$c10} keys`\n" .
                    "  30 days: `{$c30} keys`\n" .
                    "  90 days: `{$c90} keys`\n\n" .
                    "**8 Ball Pool:**\n" .
                    "  3 days: `{$e3} keys`\n" .
                    "  10 days: `{$e10} keys`\n" .
                    "  30 days: `{$e30} keys`\n" .
                    "  90 days: `{$e90} keys`\n\n" .
                    "**Soccer Star:**\n" .
                    "  3 days: `{$s3} keys`\n" .
                    "  10 days: `{$s10} keys`\n" .
                    "  30 days: `{$s30} keys`";

        } elseif (str_contains($toolKey, 'kos')) {
            $prodCarrom = Product::find(3) ?: Product::where('name', 'like', '%KOS%')->first();
            $prod8Ball = Product::find(76) ?: Product::where('name', 'like', '%KOS%8%')->first();
            $cid = $prodCarrom ? $prodCarrom->id : 3;
            $eid = $prod8Ball ? $prod8Ball->id : 76;

            $c1 = Key::where('product_id', $cid)->where('duration', '1d')->where('is_sold', false)->count();
            $c7 = Key::where('product_id', $cid)->where('duration', '7d')->where('is_sold', false)->count();
            $c15 = Key::where('product_id', $cid)->where('duration', '15d')->where('is_sold', false)->count();
            $c30 = Key::where('product_id', $cid)->where('duration', '30d')->where('is_sold', false)->count();

            $e1 = Key::where('product_id', $eid)->where('duration', '1d')->where('is_sold', false)->count();
            $e7 = Key::where('product_id', $eid)->where('duration', '7d')->where('is_sold', false)->count();
            $e15 = Key::where('product_id', $eid)->where('duration', '15d')->where('is_sold', false)->count();
            $e30 = Key::where('product_id', $eid)->where('duration', '30d')->where('is_sold', false)->count();

            $text = "🎮 **KOS ENGINE STOCK** 🎮\n" .
                    "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
                    "**Carrom Pool:**\n" .
                    "  1 day: `{$c1} keys`\n" .
                    "  7 days: `{$c7} keys`\n" .
                    "  15 days: `{$c15} keys`\n" .
                    "  30 days: `{$c30} keys`\n\n" .
                    "**8 Ball Pool:**\n" .
                    "  1 day: `{$e1} keys`\n" .
                    "  7 days: `{$e7} keys`\n" .
                    "  15 days: `{$e15} keys`\n" .
                    "  30 days: `{$e30} keys`";

        } elseif (str_contains($toolKey, 'aim_ai') || str_contains($toolKey, 'aim ai')) {
            $prod = Product::find(4) ?: Product::where('name', 'like', '%Aim Ai%')->first();
            $pid = $prod ? $prod->id : 4;

            $a1 = Key::where('product_id', $pid)->where('duration', '1d')->where('is_sold', false)->count();
            $a3 = Key::where('product_id', $pid)->where('duration', '3d')->where('is_sold', false)->count();
            $a7 = Key::where('product_id', $pid)->where('duration', '7d')->where('is_sold', false)->count();
            $a15 = Key::where('product_id', $pid)->where('duration', '15d')->where('is_sold', false)->count();
            $a30 = Key::where('product_id', $pid)->where('duration', '30d')->where('is_sold', false)->count();
            $a90 = Key::where('product_id', $pid)->where('duration', '90d')->where('is_sold', false)->count();

            $text = "🤖 **AIM AI ENGINE STOCK** 🤖\n" .
                    "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
                    "  1 day: `{$a1} keys`\n" .
                    "  3 days: `{$a3} keys`\n" .
                    "  7 days: `{$a7} keys`\n" .
                    "  15 days: `{$a15} keys`\n" .
                    "  30 days: `{$a30} keys`\n" .
                    "  90 days: `{$a90} keys`";
        }

        $keyboard = [
            'inline_keyboard' => [
                [ ['text' => '🔙 Back to Stock Menu', 'callback_data' => 'check_stock_menu'] ],
                [ ['text' => '🏠 Main Menu', 'callback_data' => 'main_menu'] ]
            ]
        ];

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    /**
     * Send Clean Mobile-Optimized Price List Card.
     */
    protected function sendPriceListCard($chatId, TelegramUser $user)
    {
        $priceListText = "💎 **𝗞𝗛𝗘𝗟𝗢 𝗦𝗧𝗢𝗥𝗘 — 𝗣𝗥𝗜𝗖𝗜𝗡𝗚 𝗠𝗘𝗡𝗨** 💎\n" .
            "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
            "🎯 **Aim Carrom King**\n" .
            "🔹 *Normal:*\n" .
            "• 3 Days: `₹250`  • 7 Days: `₹450`\n" .
            "• 30 Days: `₹1050` • 90 Days: `₹2600`\n" .
            "⚡ *Queue Mod:*\n" .
            "• 3 Days: `₹330`  • 7 Days: `₹560`\n" .
            "• 30 Days: `₹1350` • 90 Days: `₹3100`\n\n" .
            "🐍 **Snake**\n" .
            "🎱 *8 Ball Pool:*\n" .
            "• 3 Days: `₹380`  • 10 Days: `₹950`\n" .
            "• 30 Days: `₹1900` • 90 Days: `₹4600`\n" .
            "🎯 *Carrom Pool:*\n" .
            "• 3 Days: `₹200`  • 10 Days: `₹480`\n" .
            "• 30 Days: `₹1050` • 90 Days: `₹2800`\n" .
            "⚽ *Soccer Star:*\n" .
            "• 3 Days: `₹160`  • 10 Days: `₹350` • 30 Days: `₹900`\n\n" .
            "🎮 **KOS**\n" .
            "🎯 *Carrom Pool:*\n" .
            "• 1 Day: `₹120`   • 7 Days: `₹280`\n" .
            "• 15 Days: `₹480` • 30 Days: `₹850`\n" .
            "🎱 *8 Ball Pool:*\n" .
            "• 1 Day: `₹100`   • 7 Days: `₹270`\n" .
            "• 15 Days: `₹550` • 30 Days: `₹950`\n\n" .
            "🤖 **Aim Ai (Carrom Pool)**\n" .
            "• 1 Day: `₹100`   • 3 Days: `₹200`\n" .
            "• 7 Days: `₹300`  • 15 Days: `₹500`\n" .
            "• 30 Days: `₹850` • 90 Days: `₹2200`\n\n" .
            "🚀 **bitAIM+ (Carrom Pool)**\n" .
            "• 7 Days: `₹59`   • 30 Days: `₹149`\n" .
            "• 90 Days: `₹359` • 💎 Lifetime: `₹1999`\n\n" .
            "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
            "💡 *Neeche button se direct balance add karein ya key buy karein:*";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '💳 Add Balance (QR)', 'callback_data' => 'add_balance_qr'],
                    ['text' => '🛍️ Buy Game Keys', 'callback_data' => 'main_menu']
                ],
                [
                    ['text' => '🏠 Main Menu', 'callback_data' => 'main_menu']
                ]
            ]
        ];

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $priceListText,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    /**
     * Send direct Merchant Dynamic UPI QR code for adding wallet balance (Open custom amount / 1-Click Verify).
     */
    protected function sendDirectAddBalanceQR($chatId, TelegramUser $user, $amount = 0)
    {
        $paytmUpiId = env('PAYTM_UPI_ID', 'paytmqr5oc96r@ptys');
        $qrApiUrl = env('PAYTM_QR_API', 'https://paytm.anujbots.xyz/qr.php');

        $orderId = null;
        $qrCodeUrl = null;

        try {
            $params = ['upi' => $paytmUpiId];
            if ($amount > 0) {
                $params['amount'] = $amount;
            } else {
                $params['amount'] = 0;
            }

            $response = \Illuminate\Support\Facades\Http::timeout(10)->get($qrApiUrl, $params);
            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['success']) && !empty($data['order_id']) && !empty($data['qr_url'])) {
                    $orderId = $data['order_id'];
                    $qrCodeUrl = $data['qr_url'];
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("QR Generation API Error: " . $e->getMessage());
        }

        // Robust fallback if external QR generation API is slow/unavailable
        if (!$orderId || !$qrCodeUrl) {
            $orderId = 'ORD_' . time() . '_' . rand(1000, 9999);
            $amtParam = ($amount > 0) ? "&am={$amount}" : "";
            $upiString = "upi://pay?pa={$paytmUpiId}&pn=KheloStore{$amtParam}&cu=INR&tr={$orderId}";
            $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=450x450&data=" . urlencode($upiString);
        }

        // Store active order ID in cache for frictionless 1-click verification
        Cache::put("tg_user_{$chatId}_paytm_order_id", $orderId, 3600);
        Cache::put("tg_user_{$chatId}_awaiting_utr", true, 3600);

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ Verify Payment', 'callback_data' => 'verify_payment']
                ]
            ]
        ];

        $this->sendTelegramApi('sendPhoto', [
            'chat_id' => $chatId,
            'photo' => $qrCodeUrl,
            'caption' => "👇👇👇👇👇👇👇",
            'reply_markup' => $keyboard
        ]);
    }

    /**
     * 1-Click Frictionless Payment Verification (Dynamic Order ID auto-verified with Paytm Gateway).
     */
    protected function verifyPaytmPaymentCallback($chatId, TelegramUser $user, $callbackQueryId = null)
    {
        $merchantId = env('PAYTM_MERCHANT_ID', '');
        $merchantKey = env('PAYTM_MERCHANT_KEY', '');
        $verifyApiUrl = env('PAYTM_VERIFY_API', 'https://anujbots.xyz/paytm/verify.php');

        $orderId = Cache::get("tg_user_{$chatId}_paytm_order_id");

        if (!$orderId) {
            // Check if there was a recent deposit in the last 15 minutes for this user
            $recentTx = Transaction::where('telegram_user_id', $user->id)
                ->where('type', 'credit')
                ->where('created_at', '>=', now()->subMinutes(15))
                ->latest()
                ->first();

            if ($recentTx) {
                if ($callbackQueryId) {
                    $this->sendTelegramApi('answerCallbackQuery', [
                        'callback_query_id' => $callbackQueryId,
                        'text' => "✅ Payment of ₹" . number_format($recentTx->amount, 0) . " is already added to your wallet!",
                        'show_alert' => true
                    ]);
                }
                return;
            }

            if ($callbackQueryId) {
                $this->sendTelegramApi('answerCallbackQuery', [
                    'callback_query_id' => $callbackQueryId,
                    'text' => "⚠️ No active payment found!\n\nPehle QR Code generate karein, UPI se pay karein aur phir Verify dabayein.",
                    'show_alert' => true
                ]);
            }
            return;
        }

        // Call Paytm Gateway API
        $verified = false;
        $paidAmount = 0;
        $bankTxnId = null;

        try {
            $res = \Illuminate\Support\Facades\Http::timeout(10)->get($verifyApiUrl, [
                'merchantid' => $merchantId,
                'merchantkey' => $merchantKey,
                'orderid' => $orderId,
            ]);

            if ($res->successful()) {
                $vData = $res->json();
                if (!empty($vData['success']) && ($vData['status'] ?? '') === 'TXN_SUCCESS' && !empty($vData['amount'])) {
                    $paidAmount = (float)$vData['amount'];
                    if ($paidAmount > 0) {
                        $verified = true;
                        $bankTxnId = $vData['bank_txn_id'] ?? ($vData['transaction_id'] ?? $orderId);
                    }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Payment verify exception for order {$orderId}: " . $e->getMessage());
        }

        if (!$verified || $paidAmount <= 0) {
            if ($callbackQueryId) {
                $this->sendTelegramApi('answerCallbackQuery', [
                    'callback_query_id' => $callbackQueryId,
                    'text' => "⏳ Payment Not Detected Yet!\n\nAgar aapne UPI app se payment kar di hai toh 5-10 second wait karke dobara Verify dabayein.",
                    'show_alert' => true
                ]);
            }
            return;
        }

        // Anti-duplicate protection: Check if this order / transaction was already credited
        $alreadyUsed = Transaction::where(function($q) use ($orderId, $bankTxnId) {
            $q->where('description', 'LIKE', "%{$orderId}%");
            if ($bankTxnId) {
                $q->orWhere('description', 'LIKE', "%{$bankTxnId}%");
            }
        })->exists();

        if ($alreadyUsed) {
            Cache::forget("tg_user_{$chatId}_paytm_order_id");
            Cache::forget("tg_user_{$chatId}_awaiting_utr");

            if ($callbackQueryId) {
                $this->sendTelegramApi('answerCallbackQuery', [
                    'callback_query_id' => $callbackQueryId,
                    'text' => "✅ This transaction was already credited to your wallet!",
                    'show_alert' => true
                ]);
            }
            return;
        }

        // Clear cache state immediately
        Cache::forget("tg_user_{$chatId}_paytm_order_id");
        Cache::forget("tg_user_{$chatId}_awaiting_utr");

        $credited = false;

        // Atomic DB credit inside transaction lock with inside-transaction duplicate verification
        DB::transaction(function () use ($user, $paidAmount, $orderId, $bankTxnId, &$credited) {
            $lockedUser = TelegramUser::where('id', $user->id)->lockForUpdate()->first();

            $insideCheck = Transaction::where(function($q) use ($orderId, $bankTxnId) {
                $q->where('description', 'LIKE', "%{$orderId}%");
                if ($bankTxnId) {
                    $q->orWhere('description', 'LIKE', "%{$bankTxnId}%");
                }
            })->lockForUpdate()->exists();

            if ($insideCheck) {
                return;
            }

            $lockedUser->balance += $paidAmount;
            $lockedUser->save();

            Transaction::create([
                'telegram_user_id' => $lockedUser->id,
                'type' => 'credit',
                'amount' => $paidAmount,
                'description' => "UPI Deposit (Verified: {$orderId}" . ($bankTxnId ? " / {$bankTxnId}" : "") . ")"
            ]);

            $credited = true;
        });

        if (!$credited) {
            return;
        }

        $user->refresh();

        // Referral reward (5% cashback on 1st deposit) safely wrapped
        try {
            \App\Models\Referral::checkAndRewardDeposit($user, (float)$paidAmount);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Referral reward error: " . $e->getMessage());
        }

        $balClean = ($user->balance == (int)$user->balance) ? (int)$user->balance : number_format($user->balance, 2);
        $amtClean = ($paidAmount == (int)$paidAmount) ? (int)$paidAmount : number_format($paidAmount, 2);

        $successMsg = "🎉 **PAYMENT VERIFIED & CREDITED!** 💸\n" .
                      "━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                      "💰 **Amount Added:** `+₹{$amtClean}`\n" .
                      "💳 **New Wallet Balance:** `₹{$balClean}`\n" .
                      "🆔 **Order ID:** `{$orderId}`\n\n" .
                      "Aapka balance instant add ho gaya hai! Ab aap koi bhi gaming key khareed sakte hain. 🎮🚀";

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $successMsg,
            'parse_mode' => 'Markdown',
            'reply_markup' => [
                'inline_keyboard' => [
                    [ ['text' => '🛍️ Buy Game Keys', 'callback_data' => 'main_menu'] ],
                    [ ['text' => '🏠 Main Menu', 'callback_data' => 'main_menu'] ]
                ]
            ]
        ]);

        // Send admin deposit alert email
        try {
            \App\Services\OrderAlertService::sendSaleAlert([
                'type' => 'UPI DEPOSIT (DYNAMIC QR 1-CLICK VERIFIED)',
                'product_name' => "Wallet Top-Up (Order: {$orderId})",
                'duration' => 'Deposit',
                'license_key' => "Order: {$orderId}" . ($bankTxnId ? " | Txn: {$bankTxnId}" : ""),
                'price' => $paidAmount,
                'cost' => 0,
                'user_name' => $user->first_name,
                'user_username' => $user->username
            ]);
        } catch (\Throwable $e) {}
    }

    /**
     * Execute Daily Spin Wheel action and award real balance.
     */
    protected function executeSpinWheel($chatId, TelegramUser $user, $callbackQueryId = null)
    {
        $spinResult = DB::transaction(function () use ($user) {
            $lockedUser = TelegramUser::where('id', $user->id)->lockForUpdate()->first();

            if (!$lockedUser->canSpinToday()) {
                return [
                    'status' => 'already_used',
                    'time_left' => $lockedUser->formattedTimeUntilNextSpin()
                ];
            }

            // Weighted Probability: ₹2 (35%), ₹5 (30%), ₹10 (20%), ₹20 (10%), ₹50 (5%)
            $rand = rand(1, 100);
            if ($rand <= 35) {
                $won = 2.00;
                $emoji = "🎁";
                $tier = "Lucky Reward";
            } elseif ($rand <= 65) {
                $won = 5.00;
                $emoji = "✨";
                $tier = "Super Bonus";
            } elseif ($rand <= 85) {
                $won = 10.00;
                $emoji = "⚡";
                $tier = "Mega Prize";
            } elseif ($rand <= 95) {
                $won = 20.00;
                $emoji = "🔥";
                $tier = "Ultra Prize";
            } else {
                $won = 50.00;
                $emoji = "💎";
                $tier = "JACKPOT WINNER!";
            }

            $lockedUser->balance += $won;
            $lockedUser->last_spin_at = now();
            $lockedUser->total_spins = ($lockedUser->total_spins ?? 0) + 1;
            $lockedUser->total_spin_won = ($lockedUser->total_spin_won ?? 0) + $won;
            $lockedUser->save();

            Spin::create([
                'telegram_user_id' => $lockedUser->id,
                'amount_won' => $won,
                'reward_label' => "{$emoji} {$tier} (₹{$won})"
            ]);

            Transaction::create([
                'telegram_user_id' => $lockedUser->id,
                'type' => 'credit',
                'amount' => $won,
                'description' => "Daily Lucky Wheel Reward: +₹{$won}"
            ]);

            return [
                'status' => 'success',
                'won' => $won,
                'emoji' => $emoji,
                'tier' => $tier,
                'balance' => $lockedUser->balance,
            ];
        });

        if ($spinResult['status'] === 'already_used') {
            if ($callbackQueryId) {
                $this->sendTelegramApi('answerCallbackQuery', [
                    'callback_query_id' => $callbackQueryId,
                    'text' => "⏳ Next spin in: " . $spinResult['time_left'],
                    'show_alert' => true
                ]);
            }
            $this->sendSpinWheelMenu($chatId, $user);
            return;
        }

        $user->refresh();
        $won = $spinResult['won'];
        $emoji = $spinResult['emoji'];
        $tier = $spinResult['tier'];
        $balClean = ($user->balance == (int)$user->balance) ? (int)$user->balance : number_format($user->balance, 2);
        $wonClean = ($won == (int)$won) ? (int)$won : number_format($won, 2);

        $spinResult = "🎰 **SPINNING THE WHEEL...** 🎡\n" .
                      "`[ 🍒 | 💎 | 💰 | 7️⃣ | 🍀 ]`\n" .
                      "━━━━━━━━━━━━━━━━━━━━\n" .
                      "🎉 **CONGRATULATIONS, {$user->first_name}!** 🥳\n\n" .
                      "{$emoji} **{$tier}**\n" .
                      "💰 **You Won:** `+₹{$wonClean}` Cash!\n\n" .
                      "💵 **Your Balance:** ₹{$balClean}\n" .
                      "⏰ **Next Spin Available:** In 24 Hours\n\n" .
                      "Aap is balance se koi bhi gaming key discount ke sath khareed sakte hain! 🚀";

        $keyboard = [
            'inline_keyboard' => [
                [ ['text' => '🛍️ Browse Game Keys', 'callback_data' => 'dl_back_menu'] ],
                [ ['text' => '🏠 Back to Menu', 'callback_data' => 'dl_back_menu'] ],
            ]
        ];

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $spinResult,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
        $sound = \App\Http\Controllers\Admin\AdminSoundController::getEventSound('spin_win');
        if ($sound) {
            $this->sendMemeVoiceReaction($chatId, $sound);
        }
    }

    /**
     * Send Referral Dashboard.
     */
    protected function sendReferralDashboard($chatId, TelegramUser $user)
    {
        $botUsername = env('TELEGRAM_BOT_USERNAME', 'YourBot');
        $refLink = "https://t.me/{$botUsername}?start=ref_{$user->telegram_id}";

        $shareText = "🎮 Join Khelo Store Bot for best VIP prices on Aim Carrom, Snake & KOS keys! Instant delivery & 24/7 support. Join here: {$refLink}";
        $waShareUrl = "https://api.whatsapp.com/send?text=" . urlencode($shareText);
        $tgShareUrl = "https://t.me/share/url?url=" . urlencode($refLink) . "&text=" . urlencode("🎮 Get instant gaming keys on Khelo Store Bot at best VIP prices!");

        $count = TelegramUser::where('referrer_id', $user->id)->count();
        if ($user->referral_count !== $count) {
            $user->referral_count = $count;
            $user->save();
        }
        $earn = (float) ($user->referral_earnings ?? 0);
        $earnStr = number_format($earn, 2);

        $msg = "👥 **REFER & EARN 5% CASHBACK!** 💸\n\n" .
               "Apne dosto aur groups me apna unique referral link share karein. Jab bhi aapka koi naya dost join karke apna **First Wallet Deposit (Add Money)** karega, aapko milega **5% Direct Wallet Cashback (One-Time)**! 💰\n\n" .
               "🔗 **Your Unique Referral Link:**\n" .
               "`{$refLink}`\n" .
               "*(Tap on link to copy)*\n\n" .
               "📊 **Your Referral Statistics:**\n" .
               "👥 **Total Friends Joined:** `{$count}`\n" .
               "💰 **Total 5% Cashback Earned:** `₹{$earnStr}`\n\n" .
               "👇 **1-Click Share to WhatsApp or Telegram:**";

        $inlineButtons = [
            [ ['text' => '📲 Share on WhatsApp', 'url' => $waShareUrl] ],
            [ ['text' => '🚀 Share on Telegram', 'url' => $tgShareUrl] ],
            [ ['text' => '🏠 Back to Menu', 'callback_data' => 'dl_back_menu'] ],
        ];

        $keyboard = ['inline_keyboard' => $inlineButtons];

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $msg,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    /**
     * Send Proof Channel info (WhatsApp).
     */
    protected function sendProofInfo($chatId, TelegramUser $user)
    {
        $proofText = "🧾 **DAILY DEALS & PROOF CHANNEL** 🌟\n" .
                     "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
                     "Aap hamare daily deals, payment proofs, instant key deliveries aur customer feedbacks live check kar sakte hain:\n\n" .
                     "🟢 **WhatsApp Proof Channel:**\n" .
                     "👉 https://whatsapp.com/channel/0029Vb8I5rE59PwQ8NFBo63S\n\n" .
                     "👇 **Neeche diye button se directly channel join karein:**";

        $proofKeyboard = [
            'inline_keyboard' => [
                [ ['text' => '🟢 Follow WhatsApp Proof Channel', 'url' => 'https://whatsapp.com/channel/0029Vb8I5rE59PwQ8NFBo63S'] ],
                [ ['text' => '🏠 Back to Menu', 'callback_data' => 'proof_back_menu'] ],
            ]
        ];

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $proofText,
            'parse_mode' => 'Markdown',
            'reply_markup' => $proofKeyboard
        ]);
    }

    /**
     * Send In-Game Items category menu.
     */
    protected function sendInGameItemsMenu($chatId, TelegramUser $user)
    {
        $text = "🛍️ **CARROM POOL IN-GAME ITEMS** 🛍️\n" .
                "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
                "👇 Select an item category from below to view showcase and buy:";

        $keyboard = [
            'inline_keyboard' => [
                [ ['text' => '💎 GEMS PRICE', 'callback_data' => 'ingame_gems'] ],
                [ ['text' => '🟡 COIN PRICE', 'callback_data' => 'ingame_coins'] ],
                [ ['text' => '🧿 STRIKER PRICE', 'callback_data' => 'ingame_striker'] ],
                [ ['text' => '🔥 TRAIL PRICE', 'callback_data' => 'ingame_trail'] ],
                [ ['text' => '🏠 Back to Menu', 'callback_data' => 'ingame_back_menu'] ],
            ]
        ];

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    /**
     * Handle user selection of In-Game items (sends photo/caption + WhatsApp button).
     */
    protected function handleInGameItemCallback($chatId, $data, TelegramUser $user)
    {
        if ($data === 'ingame_back_menu') {
            $this->sendWelcomeMenu($chatId, $user);
            return;
        }

        $key = str_replace('ingame_', '', $data);
        $items = $this->getInGameItems();

        if (!isset($items[$key])) {
            $this->sendWelcomeMenu($chatId, $user);
            return;
        }

        $item = $items[$key];
        $waNum = preg_replace('/[^0-9]/', '', (string)($item['whatsapp_number'] ?? ''));
        if (strlen($waNum) === 10) {
            $waNum = '91' . $waNum;
        }
        $waUrl = "https://wa.me/{$waNum}?text=" . urlencode("Hello, I want to buy {$item['name']}");

        $caption = $item['caption'] ?? "{$item['title']}\n\nDM TO BUY on WhatsApp:\n{$waUrl}";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '💬 Buy on WhatsApp', 'url' => $waUrl]
                ],
                [
                    ['text' => '🔙 Back to Items', 'callback_data' => 'ingame_menu']
                ]
            ]
        ];

        $photo = !empty($item['photo_file_id']) ? $item['photo_file_id'] : (!empty($item['photo_url']) ? $item['photo_url'] : null);

        if ($photo) {
            $res = $this->sendTelegramApi('sendPhoto', [
                'chat_id' => $chatId,
                'photo' => $photo,
                'caption' => $caption,
                'reply_markup' => $keyboard
            ]);

            if ($res && $res->successful()) {
                $photoData = $res->json()['result']['photo'] ?? null;
                if (!empty($photoData) && is_array($photoData)) {
                    $bestPhoto = end($photoData);
                    $newFileId = $bestPhoto['file_id'] ?? null;
                    if ($newFileId && empty($items[$key]['photo_file_id'])) {
                        $items[$key]['photo_file_id'] = $newFileId;
                        $this->saveInGameItems($items);
                    }
                }
            }
        } else {
            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => $caption,
                'reply_markup' => $keyboard
            ]);
        }
    }

    /**
     * Handle binding newly uploaded photo to an in-game item category.
     */
    protected function handleBindPhotoCallback($chatId, $data, TelegramUser $user)
    {
        $photoFileId = Cache::get("temp_uploaded_photo_{$chatId}");
        if (!$photoFileId) {
            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => "⚠️ Photo session expired. Please send the photo again to link it."
            ]);
            return;
        }

        $key = str_replace('bind_photo_', '', $data);
        $items = $this->getInGameItems();

        if (isset($items[$key])) {
            $items[$key]['photo_file_id'] = $photoFileId;
            $this->saveInGameItems($items);

            $msg = "✅ **Successfully Linked Photo to {$items[$key]['name']}!** 🎉\n\n" .
                   "🔑 **File ID:** `{$photoFileId}`\n\n" .
                   "Ab jab bhi koi user **{$items[$key]['button_text']}** dabayega, Telegram direct yahi photo showcase bhejega! 🚀";

            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => $msg,
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    /**
     * Send Meme Voice Reaction (Audio Note with waveform).
     */
    public function sendMemeVoiceReaction($chatId, string $type)
    {
        $fileMap = [
            'jalwa' => 'jalwa_hai_hamara.mp3',
            'kgf' => 'kgf_theme.mp3',
            'paisa' => 'paisa_hi_paisa.mp3',
            'maza' => 'maza_aaya.mp3',
            'waah_bete' => 'waah_bete.mp3',
            'jethalal' => 'jethalal_wah.mp3',
            'dhoom' => 'dhoom_machale.mp3',
            'khatam' => 'khatam_tata_bye_bye.mp3',
            'aa_gaya' => 'aa_gaya.mp3',
        ];

        $filename = $fileMap[$type] ?? null;
        if (!$filename) return;

        $path = public_path('audio/' . $filename);
        if (!file_exists($path)) return;

        try {
            $this->sendTelegramApi('sendVoice', [
                'chat_id' => $chatId,
                'voice' => $path
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to send meme voice reaction: " . $e->getMessage());
        }
    }

    /**
     * Send request to Telegram API (or intercept for simulation).
     */
    public function sendTelegramApi($method, $payload)
    {
        if (request()->header('X-Telegram-Simulate') === 'true' || request()->input('simulate') === 'true') {
            $simResponses = session()->get('sim_responses', []);
            $simResponses[] = [
                'method' => $method,
                'payload' => $payload
            ];
            session()->put('sim_responses', $simResponses);
            return response()->json(['status' => 'simulated']);
        }

        $token = config('services.telegram.token');

        // Handle sendPhoto with local image files (e.g. uploaded from Admin Panel)
        if ($method === 'sendPhoto' && !empty($payload['photo'])) {
            $photoValue = $payload['photo'];
            $localFilePath = null;

            if (file_exists($photoValue)) {
                $localFilePath = $photoValue;
            } elseif (filter_var($photoValue, FILTER_VALIDATE_URL)) {
                $urlPath = parse_url($photoValue, PHP_URL_PATH);
                $candidatePath = public_path(ltrim($urlPath, '/'));
                if (file_exists($candidatePath)) {
                    $localFilePath = $candidatePath;
                }
            }

            if ($localFilePath && file_exists($localFilePath)) {
                $httpRequest = Http::asMultipart()->attach(
                    'photo',
                    fopen($localFilePath, 'r'),
                    basename($localFilePath)
                );

                $multipartData = [];
                foreach ($payload as $key => $value) {
                    if ($key === 'photo') continue;
                    if (is_array($value)) {
                        $multipartData[$key] = json_encode($value);
                    } else {
                        $multipartData[$key] = (string) $value;
                    }
                }

                $res = $httpRequest->post("https://api.telegram.org/bot{$token}/sendPhoto", $multipartData);
                Log::info("Telegram API sendPhoto (Multipart) response [{$res->status()}]: " . $res->body());
                return $res;
            }
        }

        // Handle sendDocument with local files
        if ($method === 'sendDocument' && !empty($payload['document'])) {
            $docValue = $payload['document'];
            $localFilePath = null;

            if (file_exists($docValue)) {
                $localFilePath = $docValue;
            } elseif (filter_var($docValue, FILTER_VALIDATE_URL)) {
                $urlPath = parse_url($docValue, PHP_URL_PATH);
                $candidatePath = public_path(ltrim($urlPath, '/'));
                if (file_exists($candidatePath)) {
                    $localFilePath = $candidatePath;
                }
            }

            if ($localFilePath && file_exists($localFilePath)) {
                $httpRequest = Http::asMultipart()->attach(
                    'document',
                    fopen($localFilePath, 'r'),
                    basename($localFilePath)
                );

                $multipartData = [];
                foreach ($payload as $key => $value) {
                    if ($key === 'document') continue;
                    if (is_array($value)) {
                        $multipartData[$key] = json_encode($value);
                    } else {
                        $multipartData[$key] = (string) $value;
                    }
                }

                $res = $httpRequest->post("https://api.telegram.org/bot{$token}/sendDocument", $multipartData);
                Log::info("Telegram API sendDocument (Multipart) response [{$res->status()}]: " . $res->body());
                return $res;
            }
        }

        // Handle sendVoice with local audio files
        if ($method === 'sendVoice' && !empty($payload['voice'])) {
            $voiceValue = $payload['voice'];
            $localFilePath = null;

            if (file_exists($voiceValue)) {
                $localFilePath = $voiceValue;
            } elseif (file_exists(public_path('audio/' . basename($voiceValue)))) {
                $localFilePath = public_path('audio/' . basename($voiceValue));
            }

            if ($localFilePath && file_exists($localFilePath)) {
                $httpRequest = Http::asMultipart()->attach(
                    'voice',
                    fopen($localFilePath, 'r'),
                    basename($localFilePath)
                );

                $multipartData = [];
                foreach ($payload as $key => $value) {
                    if ($key === 'voice') continue;
                    if (is_array($value)) {
                        $multipartData[$key] = json_encode($value);
                    } else {
                        $multipartData[$key] = (string) $value;
                    }
                }

                $res = $httpRequest->post("https://api.telegram.org/bot{$token}/sendVoice", $multipartData);
                Log::info("Telegram API sendVoice (Multipart) response [{$res->status()}]: " . $res->body());
                return $res;
            }
        }

        // Handle sendVideo with local video files
        if ($method === 'sendVideo' && !empty($payload['video'])) {
            $videoValue = $payload['video'];
            $localFilePath = null;

            if (file_exists($videoValue)) {
                $localFilePath = $videoValue;
            } elseif (file_exists(public_path('videos/' . basename($videoValue)))) {
                $localFilePath = public_path('videos/' . basename($videoValue));
            } elseif (file_exists(public_path('audio/' . basename($videoValue)))) {
                $localFilePath = public_path('audio/' . basename($videoValue));
            }

            if ($localFilePath && file_exists($localFilePath)) {
                $httpRequest = Http::asMultipart()->attach(
                    'video',
                    fopen($localFilePath, 'r'),
                    basename($localFilePath)
                );

                $multipartData = [];
                foreach ($payload as $key => $value) {
                    if ($key === 'video') continue;
                    if (is_array($value)) {
                        $multipartData[$key] = json_encode($value);
                    } else {
                        $multipartData[$key] = (string) $value;
                    }
                }

                $res = $httpRequest->post("https://api.telegram.org/bot{$token}/sendVideo", $multipartData);
                Log::info("Telegram API sendVideo (Multipart) response [{$res->status()}]: " . $res->body());
                return $res;
            }
        }

        $res = Http::post("https://api.telegram.org/bot{$token}/{$method}", $payload);
        Log::info("Telegram API {$method} response [{$res->status()}]: " . $res->body());
        return $res;
    }

    /**
     * Send formatted game stock information matching user screenshots.
     */
    protected function sendGameStockInfo($chatId, TelegramUser $user, $gameName)
    {
        $product = Product::where('name', 'like', "%{$gameName}%")->first();
        $pId = $product?->id;

        if (str_contains(strtolower($gameName), 'carrom king')) {
            $n3 = Key::where('product_id', $pId)->where('duration', '3d')->where('is_sold', false)->count();
            $n7 = Key::where('product_id', $pId)->where('duration', '7d')->where('is_sold', false)->count();
            $n30 = Key::where('product_id', $pId)->where('duration', '30d')->where('is_sold', false)->count();
            $n90 = Key::where('product_id', $pId)->where('duration', '90d')->where('is_sold', false)->count();

            $q3 = Key::where('product_id', $pId)->where('duration', '3d_queue')->where('is_sold', false)->count();
            $q7 = Key::where('product_id', $pId)->where('duration', '7d_queue')->where('is_sold', false)->count();
            $q30 = Key::where('product_id', $pId)->where('duration', '30d_queue')->where('is_sold', false)->count();
            $q90 = Key::where('product_id', $pId)->where('duration', '90d_queue')->where('is_sold', false)->count();

            $stockText = "AIM CARROM KING STOCK\n\n" .
                "Normal:\n" .
                " 3 days: {$n3} keys\n" .
                " 7 days: {$n7} keys\n" .
                " 30 days: {$n30} keys\n" .
                " 90 days: {$n90} keys\n\n" .
                "Queue:\n" .
                " 3 days: {$q3} keys\n" .
                " 7 days: {$q7} keys\n" .
                " 30 days: {$q30} keys\n" .
                " 90 days: {$q90} keys";

        } elseif (str_contains(strtolower($gameName), 'snake')) {
            $s3 = Key::where('product_id', $pId)->where('duration', '3d')->where('is_sold', false)->count();
            $s10 = Key::where('product_id', $pId)->where('duration', '10d')->where('is_sold', false)->count();
            $s30 = Key::where('product_id', $pId)->where('duration', '30d')->where('is_sold', false)->count();
            $s90 = Key::where('product_id', $pId)->where('duration', '90d')->where('is_sold', false)->count();

            $stockText = "SNAKE ENGINE STOCK\n\n" .
                "8 Ball Pool:\n" .
                " 3 days: {$s3} keys\n" .
                " 10 days: {$s10} keys\n" .
                " 30 days: {$s30} keys\n" .
                " 90 days: {$s90} keys\n\n" .
                "Carrom Pool:\n" .
                " 3 days: {$s3} keys\n" .
                " 10 days: {$s10} keys\n" .
                " 30 days: {$s30} keys\n" .
                " 90 days: {$s90} keys";

        } elseif (str_contains(strtolower($gameName), 'kos')) {
            $k1 = Key::where('product_id', $pId)->where('duration', '1d')->where('is_sold', false)->count();
            $k7 = Key::where('product_id', $pId)->where('duration', '7d')->where('is_sold', false)->count();
            $k15 = Key::where('product_id', $pId)->where('duration', '15d')->where('is_sold', false)->count();
            $k30 = Key::where('product_id', $pId)->where('duration', '30d')->where('is_sold', false)->count();

            $stockText = "KOS STOCK\n\n" .
                "Carrom Pool:\n" .
                " 1 days: {$k1} keys\n" .
                " 7 days: {$k7} keys\n" .
                " 15 days: {$k15} keys\n" .
                " 30 days: {$k30} keys\n\n" .
                "8 Ball Pool:\n" .
                " 1 days: {$k1} keys\n" .
                " 7 days: {$k7} keys\n" .
                " 15 days: {$k15} keys\n" .
                " 30 days: {$k30} keys";

        } else {
            // Aim Ai Engine
            $a1 = Key::where('product_id', $pId)->where('duration', '1d')->where('is_sold', false)->count();
            $a3 = Key::where('product_id', $pId)->where('duration', '3d')->where('is_sold', false)->count();
            $a7 = Key::where('product_id', $pId)->where('duration', '7d')->where('is_sold', false)->count();
            $a15 = Key::where('product_id', $pId)->where('duration', '15d')->where('is_sold', false)->count();
            $a30 = Key::where('product_id', $pId)->where('duration', '30d')->where('is_sold', false)->count();
            $a90 = Key::where('product_id', $pId)->where('duration', '90d')->where('is_sold', false)->count();

            $stockText = "AIM AI ENGINE STOCK\n\n" .
                "Carrom Pool:\n" .
                " 1 days: {$a1} keys\n" .
                " 3 days: {$a3} keys\n" .
                " 7 days: {$a7} keys\n" .
                " 15 days: {$a15} keys\n" .
                " 30 days: {$a30} keys\n" .
                " 90 days: {$a90} keys";
        }

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $stockText
        ]);
    }

    /**
     * Display user's active purchased keys with real-time validity and 1-Click renew buttons.
     */
    protected function sendMyKeysDashboard($chatId, TelegramUser $user)
    {
        $keys = Key::with('product')
            ->where('telegram_user_id', $user->id)
            ->where('is_sold', true)
            ->orderBy('expires_at', 'desc')
            ->take(8)
            ->get();

        if ($keys->isEmpty()) {
            $text = "🔑 **MY ACTIVE KEYS** 🔑\n" .
                    "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
                    "Aapne abhi tak koi gaming key purchase nahi ki hai.\n\n" .
                    "👇 Main Menu se apni pasandida VIP game key select karke instant purchase karein!";
            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
                'reply_markup' => ['inline_keyboard' => [[['text' => '🏠 Back to Menu', 'callback_data' => 'dl_back_menu']]]]
            ]);
            return;
        }

        $balClean = ($user->balance == (int)$user->balance) ? (int)$user->balance : number_format($user->balance, 2);
        $text = "🔑 **MY PURCHASED KEYS & VALIDITY** 🔑\n" .
                "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
                "👤 **User:** {$user->first_name}\n" .
                "💰 **Your Balance:** ₹{$balClean}\n\n" .
                "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

        foreach ($keys as $k) {
            $pName = $k->product ? $k->product->name : 'Gaming Key';
            $durTitle = match($k->duration) {
                '1d' => '1 Day',
                '3d' => '3 Days',
                '7d' => '7 Days',
                '10d' => '10 Days',
                '15d' => '15 Days',
                '30d' => '30 Days',
                '90d' => '90 Days',
                default => $k->duration
            };

            $statusStr = $k->isExpired() ? '❌ Expired' : '🟢 Active (' . $k->formattedTimeRemaining() . ')';
            $expStr = $k->expires_at ? $k->expires_at->format('d M Y, h:i A') : 'Active';

            $text .= "🎮 **{$pName} ({$durTitle})**\n" .
                     "🔑 `{$k->license_key}`\n" .
                     "⏳ **Status:** {$statusStr}\n" .
                     "⏰ **Expiry:** {$expStr}\n" .
                     "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        }

        $keyboard = [
            'inline_keyboard' => [
                [ ['text' => '🛍️ Buy More Keys', 'callback_data' => 'dl_back_menu'] ],
                [ ['text' => '🏠 Back to Menu', 'callback_data' => 'dl_back_menu'] ],
            ]
        ];

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    /**
     * Handle 1-Click Key Renewal action.
     */
    protected function handle1ClickRenewal($chatId, TelegramUser $user, $keyId)
    {
        $oldKey = Key::with('product')->find($keyId);
        if (!$oldKey || !$oldKey->product) {
            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => "❌ Key record nahi mila ya expire ho chuka hai.",
                'reply_markup' => ['inline_keyboard' => [[['text' => '🏠 Back to Menu', 'callback_data' => 'dl_back_menu']]]]
            ]);
            return;
        }

        $product = $oldKey->product;
        $duration = $oldKey->duration;
        $gameName = $product->name;
        $price = $this->getExactProductPrice($gameName, $duration, 'normal', $user->id);

        if ($price <= 0) {
            $price = 250;
        }

        $durTitle = match($duration) {
            '1d' => '1 Day',
            '3d' => '3 Days',
            '7d' => '7 Days',
            '10d' => '10 Days',
            '15d' => '15 Days',
            '30d' => '30 Days',
            '90d' => '90 Days',
            default => $duration
        };

        $renewalResult = DB::transaction(function () use ($user, $price, $product, $duration, $durTitle) {
            $lockedUser = TelegramUser::where('id', $user->id)->lockForUpdate()->first();

            if ($lockedUser->balance < $price) {
                return ['status' => 'insufficient_balance'];
            }

            // Find fresh key with exclusive row lock
            $newKey = Key::where('product_id', $product->id)
                ->where('duration', $duration)
                ->where('is_sold', false)
                ->lockForUpdate()
                ->first();

            if (!$newKey) {
                return ['status' => 'out_of_stock'];
            }

            // Deduct balance and assign key
            $lockedUser->balance -= $price;
            $lockedUser->save();

            $newExpiresAt = Key::calculateExpiryFromDuration($duration, now());

            $newKey->update([
                'is_sold' => true,
                'telegram_user_id' => $lockedUser->id,
                'sold_at' => now(),
                'expires_at' => $newExpiresAt,
                'renewal_alert_sent_at' => null,
            ]);

            Transaction::create([
                'telegram_user_id' => $lockedUser->id,
                'type' => 'debit',
                'amount' => $price,
                'description' => "1-Click Auto-Renewal for {$product->name} ({$durTitle})",
            ]);

            return [
                'status' => 'success',
                'key' => $newKey,
                'expires_at' => $newExpiresAt,
                'balance' => $lockedUser->balance
            ];
        });

        if ($renewalResult['status'] === 'insufficient_balance') {
            $needed = number_format($price - $user->balance, 2);
            $msg = "⚠️ **INSUFFICIENT BALANCE FOR 1-CLICK RENEWAL**\n\n" .
                   "🎮 **Product:** {$product->name} ({$durTitle})\n" .
                   "💵 **Renewal Price:** ₹" . number_format($price, 2) . "\n" .
                   "💰 **Your Balance:** ₹" . number_format($user->balance, 2) . "\n\n" .
                   "Aapko 1-Click renewal ke liye **₹{$needed}** aur add karna hoga.\nNeeche click karke turant wallet topup karein:";

            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => $msg,
                'parse_mode' => 'Markdown',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [ ['text' => '💳 Add Balance Now', 'callback_data' => 'bind_photo_coins'] ],
                        [ ['text' => '🏠 Back to Menu', 'callback_data' => 'dl_back_menu'] ],
                    ]
                ]
            ]);
            return;
        }

        if ($renewalResult['status'] === 'out_of_stock') {
            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => "❌ **Out of Stock!**\n\nIs product ({$product->name} {$durTitle}) ki fresh keys abhi stock me nahi hain. Admin ko alert bhej diya gaya hai!",
                'parse_mode' => 'Markdown',
                'reply_markup' => ['inline_keyboard' => [[['text' => '🏠 Back to Menu', 'callback_data' => 'dl_back_menu']]]]
            ]);

            // Send instant Out-of-Stock email alert to Admin
            try {
                $throttleKey = "oos_alert_{$user->id}_{$product->id}_{$duration}";
                if (!Cache::has($throttleKey)) {
                    Cache::put($throttleKey, true, 120);
                    \App\Services\OrderAlertService::sendOutOfStockAlert([
                        'product_name' => $product->name,
                        'duration' => $durTitle,
                        'price' => $price,
                        'user_name' => $user->first_name ?: 'Customer',
                        'user_username' => $user->username,
                        'user_balance' => $user->balance,
                    ]);
                }
            } catch (\Throwable $e) {}

            return;
        }

        $user->refresh();
        $newKey = $renewalResult['key'];
        $newExpiresAt = $renewalResult['expires_at'];

        // Send instant Admin Email Alert
        \App\Services\OrderAlertService::sendSaleAlert([
            'type' => '1-CLICK KEY RENEWAL',
            'product_name' => $product->name,
            'duration' => $durTitle,
            'license_key' => $newKey->license_key,
            'price' => $price,
            'cost' => method_exists($product, 'getCostForDuration') ? $product->getCostForDuration($duration) : 0,
            'user_name' => $user->first_name ?: ($user->username ?: 'Customer'),
            'user_username' => $user->username,
            'user_telegram_id' => $user->telegram_id,
            'user_balance' => $user->balance,
        ]);

        $expFormatted = $newExpiresAt->format('d M Y, h:i A');
        $remBal = number_format($user->balance, 2);

        $successMsg = "🎉 **1-CLICK KEY RENEWAL SUCCESSFUL!** ⚡\n\n" .
                      "Aapki **{$product->name} ({$durTitle})** key successfully renew ho gayi hai!\n\n" .
                      "🔑 **New License Key:**\n" .
                      "`{$newKey->license_key}`\n\n" .
                      "⏳ **Valid Until:** `{$expFormatted}`\n" .
                      "💰 **Remaining Balance:** `₹{$remBal}`\n\n" .
                      "🎮 Gaming uninterrupted enjoy karein! 🚀";

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $successMsg,
            'parse_mode' => 'Markdown',
            'reply_markup' => [
                'inline_keyboard' => [
                    [ ['text' => '🔑 View My Keys', 'callback_data' => 'my_keys_dashboard'] ],
                    [ ['text' => '🏠 Back to Menu', 'callback_data' => 'dl_back_menu'] ],
                ]
            ]
        ]);
    }

    /**
     * Subscribe user for out-of-stock back in stock alerts.
     */
    protected function handleStockNotificationSubscribe($chatId, TelegramUser $user, $productId, $duration)
    {
        $product = Product::find($productId);
        if (!$product) {
            $this->sendTelegramApi('sendMessage', [
                'chat_id' => $chatId,
                'text' => "❌ Product not found.",
                'reply_markup' => ['inline_keyboard' => [[['text' => '🏠 Back to Menu', 'callback_data' => 'dl_back_menu']]]]
            ]);
            return;
        }

        \App\Models\StockSubscription::updateOrCreate(
            [
                'telegram_user_id' => $user->id,
                'product_id' => $product->id,
                'duration' => strtolower($duration),
            ],
            [
                'is_notified' => false,
                'notified_at' => null,
            ]
        );

        $durTitle = match(strtolower($duration)) {
            '1d' => '1 Day',
            '3d' => '3 Days',
            '7d' => '7 Days',
            '10d' => '10 Days',
            '15d' => '15 Days',
            '30d' => '30 Days',
            '90d' => '90 Days',
            default => $duration
        };

        $msg = "🔔 **STOCK ALERT ACTIVATED!** ✅\n\n" .
               "Hey **{$user->first_name}**! 👋\n" .
               "Aapka alert **{$product->name} ({$durTitle})** ke liye successfully set ho chuka hai! 🎯\n\n" .
               "⚡ Jaise hi Admin panel me naya stock add hoga, bot turant sabse pehle **aapko personal DM** bhejega taaki aap sabse pehle apni key khareed sakein!";

        $this->sendTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text' => $msg,
            'parse_mode' => 'Markdown',
            'reply_markup' => [
                'inline_keyboard' => [
                    [ ['text' => '🛍️ Browse Available Games', 'callback_data' => 'dl_back_menu'] ],
                    [ ['text' => '🏠 Back to Menu', 'callback_data' => 'dl_back_menu'] ],
                ]
            ]
        ]);
    }

    /**
     * Trigger quick buy when user clicks stock alert notification.
     */
    protected function handleQuickBuyStock($chatId, TelegramUser $user, $productId, $duration)
    {
        $product = Product::find($productId);
        if (!$product) {
            $this->sendWelcomeMenu($chatId, $user, "❌ Product not found.");
            return;
        }

        $durTitle = match(strtolower($duration)) {
            '1d' => '1 Day',
            '3d' => '3 Days',
            '7d' => '7 Days',
            '10d' => '10 Days',
            '15d' => '15 Days',
            '30d' => '30 Days',
            '90d' => '90 Days',
            default => $duration
        };

        $this->askConfirmationQuestion($chatId, $user, $product->name, $durTitle);
    }
}
