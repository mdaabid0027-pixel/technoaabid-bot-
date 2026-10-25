<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Log;

class Referral extends Model
{
    protected $table = 'referrals';

    protected $fillable = [
        'referrer_id',
        'referred_user_id',
        'is_rewarded',
        'reward_amount',
        'rewarded_at',
    ];

    protected function casts(): array
    {
        return [
            'is_rewarded' => 'boolean',
            'reward_amount' => 'decimal:2',
            'rewarded_at' => 'datetime',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class, 'referrer_id');
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class, 'referred_user_id');
    }

    /**
     * Trigger 1-Time 5% Referral Cashback when a referred friend makes their first wallet deposit.
     */
    public static function checkAndRewardDeposit(TelegramUser $user, float $depositAmount): bool
    {
        return static::rewardOnFirstDeposit($user, $depositAmount);
    }

    public static function rewardOnFirstDeposit(TelegramUser $user, float $depositAmount): bool
    {
        if (!$user->referrer_id || $depositAmount <= 0) {
            return false;
        }

        $pendingRef = static::where('referred_user_id', $user->id)
            ->where('is_rewarded', false)
            ->first();

        if (!$pendingRef) {
            return false;
        }

        $referrer = TelegramUser::find($user->referrer_id);
        if (!$referrer) {
            return false;
        }

        // 5% Cashback on 1st deposit (minimum ₹5.00)
        $rewardAmt = max(5.00, round($depositAmount * 0.05, 2));

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
            'description' => "1-Time 5% Referral Cashback for {$user->first_name}'s 1st Deposit (Deposit: ₹" . number_format($depositAmount, 2) . ")",
        ]);

        // Send celebration DM to Referrer
        try {
            $controller = app(TelegramController::class);
            $refName = $user->first_name ?: 'Friend';
            $refBalStr = number_format($referrer->balance, 2);
            $refEarnStr = number_format($referrer->referral_earnings, 2);

            $refMsg = "🎉 **1-TIME 5% REFERRAL CASHBACK EARNED!** 💸\n\n" .
                      "Aapke friend **{$refName}** ne wallet me pehli baar **₹" . number_format($depositAmount, 0) . "** add kiye hain!\n" .
                      "💰 **+₹" . number_format($rewardAmt, 2) . "** (5% Cashback) aapke wallet me add kar diye gaye hain! 🎁\n\n" .
                      "💵 **New Balance:** ₹{$refBalStr}\n" .
                      "🏆 **Total Referral Earnings:** ₹{$refEarnStr}\n\n" .
                      "Aur dosto ko invite karein aur har naye dost ke 1st deposit par 5% kamayein! 🚀";

            $controller->sendTelegramApi('sendMessage', [
                'chat_id' => $referrer->telegram_id,
                'text' => $refMsg,
                'parse_mode' => 'Markdown',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [ ['text' => '👥 Invite More Friends', 'callback_data' => 'refer_dashboard'] ],
                    ]
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed sending referral deposit reward DM: " . $e->getMessage());
        }

        return true;
    }
}
