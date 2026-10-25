<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Log;

class StockSubscription extends Model
{
    protected $table = 'stock_subscriptions';

    protected $fillable = [
        'telegram_user_id',
        'product_id',
        'duration',
        'is_notified',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_notified' => 'boolean',
            'notified_at' => 'datetime',
        ];
    }

    public function telegramUser(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class, 'telegram_user_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Notify pending subscribers for a specific product and duration.
     */
    public static function notifySubscribers(int $productId, string $duration): int
    {
        $cleanDuration = strtolower(str_replace('_queue', '', $duration));

        $subscriptions = static::with(['telegramUser', 'product'])
            ->where('product_id', $productId)
            ->where(function ($q) use ($duration, $cleanDuration) {
                $q->where('duration', $duration)
                  ->orWhere('duration', $cleanDuration)
                  ->orWhere('duration', $cleanDuration . '_queue');
            })
            ->where('is_notified', false)
            ->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $controller = app(TelegramController::class);
        $notifiedCount = 0;

        foreach ($subscriptions as $sub) {
            $user = $sub->telegramUser;
            $product = $sub->product;

            if (!$user || !$user->telegram_id || !$product) {
                continue;
            }

            $productName = $product->name;

            $durTitle = match ($cleanDuration) {
                '1d' => '1 Days',
                '3d' => '3 Days',
                '7d' => '7 Days',
                '10d' => '10 Days',
                '15d' => '15 Days',
                '30d' => '30 Days',
                '90d' => '90 Days',
                'lifetime' => 'Lifetime',
                default => $duration
            };

            $balClean = ($user->balance == (int)$user->balance) ? (int)$user->balance : number_format($user->balance, 2);

            $message = "✅ **Good News!** ✅\n\n" .
                       "🎮 {$productName}\n" .
                       "📅 {$durTitle} keys are now **BACK IN STOCK!**\n\n" .
                       "💰 Your current balance: ₹{$balClean}\n\n" .
                       "👉 Please go to main menu and try purchasing again.";

            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => "⚡ Buy {$productName} ({$durTitle})", 'callback_data' => "quick_buy_{$product->id}_{$cleanDuration}"]
                    ],
                    [
                        ['text' => "🏠 Main Menu", 'callback_data' => "dl_back_menu"]
                    ]
                ]
            ];

            $controller->sendTelegramApi('sendMessage', [
                'chat_id' => $user->telegram_id,
                'text' => $message,
                'parse_mode' => 'Markdown',
                'reply_markup' => $keyboard
            ]);

            // Send active event voice note
            try {
                $sound = \App\Http\Controllers\Admin\AdminSoundController::getEventSound('back_in_stock');
                if ($sound) {
                    $controller->sendMemeVoiceReaction($user->telegram_id, $sound);
                }
            } catch (\Throwable $e) {
                Log::error("Failed to send back_in_stock voice note: " . $e->getMessage());
            }

            $sub->update([
                'is_notified' => true,
                'notified_at' => now(),
            ]);

            $notifiedCount++;
            Log::info("Stock alert sent to user {$user->telegram_id} for product {$productName} ({$duration})");
        }

        return $notifiedCount;
    }
}
