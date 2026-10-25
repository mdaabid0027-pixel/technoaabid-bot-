<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Key;
use App\Models\TelegramUser;
use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Log;

class CheckKeyExpiryAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keys:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan sold keys and send automated 6-hour prior renewal warning alerts to Telegram users';

    /**
     * Execute the console command.
     */
    public function handle(TelegramController $controller)
    {
        $this->info('Scanning database for keys expiring in the next 6 hours...');

        // Find sold keys expiring within next 6 hours that have not received an alert yet
        $expiringKeys = Key::with(['product', 'telegramUser'])
            ->where('is_sold', true)
            ->whereNotNull('telegram_user_id')
            ->whereNotNull('expires_at')
            ->whereNull('renewal_alert_sent_at')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addHours(6))
            ->get();

        $count = $expiringKeys->count();
        $this->info("Found {$count} expiring key(s) requiring notification.");

        foreach ($expiringKeys as $key) {
            $user = $key->telegramUser;
            $product = $key->product;

            if (!$user || !$user->telegram_id || !$product) {
                continue;
            }

            $userName = $user->first_name ?: 'Valued Customer';
            $productName = $product->name;
            $duration = $key->duration;

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

            $remainingTime = $key->formattedTimeRemaining();
            $expiryFormatted = $key->expires_at->format('d M Y, h:i A');

            $message = "⏳ **KEY EXPIRING SOON!** ⚠️\n\n" .
                       "Hey **{$userName}**! 👋\n" .
                       "Aapki **{$productName} ({$durTitle})** key expire hone wali hai!\n\n" .
                       "🔑 **License Key:** `{$key->license_key}`\n" .
                       "⏰ **Expiry Time:** `{$expiryFormatted}`\n" .
                       "⏳ **Time Left:** `{$remainingTime}`\n\n" .
                       "🎮 **Game beech me na ruke!**\n" .
                       "Neeche diye **1-Click** button par click karke turant renew karein: 👇";

            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => "⚡ 1-Click Renew Key", 'callback_data' => "renew_key_{$key->id}"]
                    ],
                    [
                        ['text' => "🛍️ Browse Store", 'callback_data' => "dl_back_menu"]
                    ]
                ]
            ];

            // Send private DM via TelegramController
            $controller->sendTelegramApi('sendMessage', [
                'chat_id' => $user->telegram_id,
                'text' => $message,
                'parse_mode' => 'Markdown',
                'reply_markup' => $keyboard
            ]);

            // Mark alert sent
            $key->update([
                'renewal_alert_sent_at' => now()
            ]);

            $this->info("Alert sent to User ID: {$user->telegram_id} for Key ID: {$key->id} ({$productName})");
            Log::info("Key Expiry Alert sent to {$user->telegram_id} for Key: {$key->license_key}");
        }

        $this->info('Key expiry scan completed successfully.');
        return 0;
    }
}
