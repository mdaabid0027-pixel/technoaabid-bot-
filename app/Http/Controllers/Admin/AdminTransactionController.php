<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TelegramUser;
use App\Models\Key;
use Illuminate\Http\Request;

class AdminTransactionController extends Controller
{
    /**
     * Delete a single transaction.
     */
    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();

        return back()->with('success', 'Transaction log deleted successfully.');
    }

    /**
     * Bulk delete selected transactions.
     */
    public function bulkDelete(Request $request)
    {
        $txIds = $request->input('tx_ids', []);

        if (empty($txIds)) {
            return back()->with('error', 'No transactions selected for deletion.');
        }

        $count = Transaction::whereIn('id', $txIds)->delete();

        return back()->with('success', "Successfully deleted {$count} transaction record(s).");
    }

    /**
     * Get rich user history (purchased keys & activity) as JSON for the instant info modal.
     */
    public function userHistory($userId)
    {
        $user = TelegramUser::findOrFail($userId);

        $keys = Key::with('product')
            ->where('telegram_user_id', $user->id)
            ->latest('sold_at')
            ->take(20)
            ->get()
            ->map(function ($key) {
                return [
                    'id' => $key->id,
                    'product' => $key->product ? $key->product->name : 'N/A',
                    'duration' => strtoupper($key->duration),
                    'license_key' => $key->license_key,
                    'sold_at' => $key->sold_at ? $key->sold_at->format('d M Y, h:i A') : 'N/A',
                    'expires_at' => $key->expires_at ? $key->expires_at->format('d M Y, h:i A') : 'Active',
                    'is_expired' => $key->isExpired(),
                    'time_remaining' => $key->formattedTimeRemaining(),
                ];
            });

        $transactions = Transaction::where('telegram_user_id', $user->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($tx) {
                return [
                    'type' => strtoupper($tx->type),
                    'amount' => number_format($tx->amount, 2),
                    'description' => $tx->description,
                    'date' => $tx->created_at->format('d M Y, h:i A'),
                ];
            });

        $totalSpent = (float) Transaction::where('telegram_user_id', $user->id)->where('type', 'debit')->sum('amount');
        $totalKeys = Key::where('telegram_user_id', $user->id)->count();

        // Spin Stats
        $totalSpins = (int) (\App\Models\Spin::where('telegram_user_id', $user->id)->count() ?: ($user->total_spins ?? 0));
        $spinEarnings = (float) (\App\Models\Spin::where('telegram_user_id', $user->id)->sum('amount_won') ?: ($user->total_spin_won ?? 0));

        // Referral / Invite Stats
        $inviteCount = (int) \App\Models\Referral::where('referrer_id', $user->id)->count();
        if ($inviteCount === 0) {
            $inviteCount = (int) \App\Models\TelegramUser::where('referrer_id', $user->id)->count();
        }
        if ($inviteCount === 0 && !empty($user->referral_count)) {
            $inviteCount = (int) $user->referral_count;
        }

        $inviteEarnings = (float) \App\Models\Referral::where('referrer_id', $user->id)->where('is_rewarded', true)->sum('reward_amount');
        if ($inviteEarnings <= 0) {
            $inviteEarnings = (float) Transaction::where('telegram_user_id', $user->id)->where('description', 'like', '%Referral%')->sum('amount');
        }
        if ($inviteEarnings <= 0 && !empty($user->referral_earnings)) {
            $inviteEarnings = (float) $user->referral_earnings;
        }

        $customPrices = \App\Models\UserProductPrice::with('product')
            ->where('telegram_user_id', $user->id)
            ->get()
            ->map(function ($cp) {
                $basePrice = $cp->product ? $cp->product->getPriceForDuration($cp->duration) : 0;
                $discount = $basePrice > 0 ? max(0, $basePrice - $cp->price) : 0;
                $discountPercent = $basePrice > 0 ? round(($discount / $basePrice) * 100) : 0;
                return [
                    'id' => $cp->id,
                    'product_name' => $cp->product ? $cp->product->name : 'N/A',
                    'duration' => strtoupper($cp->duration),
                    'base_price' => number_format($basePrice, 2),
                    'custom_price' => number_format($cp->price, 2),
                    'discount' => number_format($discount, 2),
                    'discount_percent' => $discountPercent,
                ];
            });

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->first_name . ($user->last_name ? ' ' . $user->last_name : ''),
                'username' => $user->username ? '@' . $user->username : 'N/A',
                'telegram_id' => $user->telegram_id,
                'balance' => number_format($user->balance, 2),
                'total_spent' => number_format($totalSpent, 2),
                'total_keys' => $totalKeys,
                'total_spins' => $totalSpins,
                'spin_earnings' => number_format($spinEarnings, 2),
                'invite_count' => $inviteCount,
                'invite_earnings' => number_format($inviteEarnings, 2),
                'profile_url' => route('admin.users.show', $user->id),
            ],
            'keys' => $keys,
            'transactions' => $transactions,
            'custom_prices' => $customPrices,
        ]);
    }
}
