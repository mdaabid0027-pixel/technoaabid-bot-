<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TelegramUser;
use App\Models\Transaction;
use App\Models\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AdminUserController extends Controller
{
    /**
     * Display VIP Resellers (users who have custom prices configured).
     */
    public function resellers(Request $request)
    {
        $search = $request->input('search');

        $users = TelegramUser::query()
            ->where(function($q) {
                $q->where('is_reseller', true)
                  ->orWhereHas('customPrices');
            })
            ->with(['customPrices.product'])
            ->when($search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                      ->orWhere('telegram_id', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%");
                });
            })
            ->withCount(['keys as total_keys_bought' => function ($q) {
                $q->where('is_sold', true);
            }])
            ->withSum(['transactions as total_spent' => function ($q) {
                $q->where('type', 'debit');
            }], 'amount')
            ->latest()
            ->paginate(15);

        $viewType = 'resellers';
        $allUsers = TelegramUser::orderBy('first_name')->get(['id', 'telegram_id', 'first_name', 'username', 'balance']);
        return view('admin.users.index', compact('users', 'search', 'viewType', 'allUsers'));
    }

    /**
     * Display Normal Customers (users without custom prices).
     */
    public function customers(Request $request)
    {
        $search = $request->input('search');

        $users = TelegramUser::query()
            ->where('is_reseller', false)
            ->whereDoesntHave('customPrices')
            ->when($search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                      ->orWhere('telegram_id', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%");
                });
            })
            ->withCount(['keys as total_keys_bought' => function ($q) {
                $q->where('is_sold', true);
            }])
            ->withSum(['transactions as total_spent' => function ($q) {
                $q->where('type', 'debit');
            }], 'amount')
            ->latest()
            ->paginate(15);

        $viewType = 'customers';
        $allUsers = TelegramUser::orderBy('first_name')->get(['id', 'telegram_id', 'first_name', 'username', 'balance']);
        return view('admin.users.index', compact('users', 'search', 'viewType', 'allUsers'));
    }

    /**
     * Display all users with search and filtering.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = TelegramUser::query()
            ->when($search, function ($query, $search) {
                $query->where('username', 'like', "%{$search}%")
                    ->orWhere('telegram_id', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%");
            })
            ->withCount(['keys as total_keys_bought' => function ($q) {
                $q->where('is_sold', true);
            }])
            ->withSum(['transactions as total_spent' => function ($q) {
                $q->where('type', 'debit');
            }], 'amount')
            ->latest()
            ->paginate(15);

        $viewType = 'all';
        $allUsers = TelegramUser::orderBy('first_name')->get(['id', 'telegram_id', 'first_name', 'username', 'balance']);
        return view('admin.users.index', compact('users', 'search', 'viewType', 'allUsers'));
    }

    /**
     * Display the specified Telegram user details, full key purchase history, and transaction ledger.
     */
    public function show(Request $request, $id)
    {
        $user = TelegramUser::findOrFail($id);

        // 1. Purchased Keys History
        $purchasedKeys = Key::where('telegram_user_id', $user->id)
            ->with('product')
            ->latest('sold_at')
            ->paginate(15, ['*'], 'keys_page');

        // 2. Financial Transaction Ledger
        $transactions = Transaction::where('telegram_user_id', $user->id)
            ->latest()
            ->paginate(15, ['*'], 'tx_page');

        // 3. User Statistics
        $totalKeysBought = Key::where('telegram_user_id', $user->id)->count();
        $totalSpent = (float) Transaction::where('telegram_user_id', $user->id)->where('type', 'debit')->sum('amount');
        $totalDeposited = (float) Transaction::where('telegram_user_id', $user->id)->where('type', 'credit')->sum('amount');

        // 4. Spin & Referral Statistics
        $totalSpins = (int) (\App\Models\Spin::where('telegram_user_id', $user->id)->count() ?: ($user->total_spins ?? 0));
        $spinEarnings = (float) (\App\Models\Spin::where('telegram_user_id', $user->id)->sum('amount_won') ?: ($user->total_spin_won ?? 0));

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

        // 5. Custom Assigned Rates
        $customPrices = \App\Models\UserProductPrice::where('telegram_user_id', $user->id)->with('product')->get();

        return view('admin.users.show', compact(
            'user',
            'purchasedKeys',
            'transactions',
            'totalKeysBought',
            'totalSpent',
            'totalDeposited',
            'totalSpins',
            'spinEarnings',
            'inviteCount',
            'inviteEarnings',
            'customPrices'
        ));
    }

    /**
     * Adjust user balance.
     */
    public function adjustBalance(Request $request, $id = null)
    {
        $userId = ($id && $id !== '0' && $id !== 0) ? $id : $request->input('telegram_user_id');

        if (!$userId && $request->filled('user_query')) {
            $q = trim($request->input('user_query'));
            $found = TelegramUser::where('username', 'LIKE', "%{$q}%")
                ->orWhere('first_name', 'LIKE', "%{$q}%")
                ->orWhere('telegram_id', $q)
                ->first();
            if ($found) {
                $userId = $found->id;
            }
        }

        $user = TelegramUser::findOrFail($userId);

        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'type' => ['required', 'in:credit,debit'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $amount = (float) $request->input('amount');
        $type = $request->input('type');
        $description = $request->input('description') ?: ($type === 'credit' ? 'Manual balance credit by admin' : 'Manual balance debit by admin');

        if ($type === 'debit' && ($user->balance - $amount) < 0) {
            return back()->with('error', 'User balance cannot drop below ₹0.00.');
        }

        DB::transaction(function () use ($user, $amount, $type, $description) {
            if ($type === 'credit') {
                $user->balance += $amount;
            } else {
                $user->balance -= $amount;
            }
            $user->save();

            Transaction::create([
                'telegram_user_id' => $user->id,
                'type' => $type,
                'amount' => $amount,
                'description' => $description,
            ]);
        });

        if ($type === 'credit') {
            \App\Models\Referral::rewardOnFirstDeposit($user, $amount);
        }

        // Send instant notification to user on Telegram
        try {
            $controller = app(\App\Http\Controllers\TelegramController::class);
            $balClean = ($user->balance == (int)$user->balance) ? (int)$user->balance : number_format($user->balance, 2);
            $amtClean = ($amount == (int)$amount) ? (int)$amount : number_format($amount, 2);

            if ($type === 'credit') {
                $msg = "💰 **WALLET TOP-UP SUCCESSFUL!** 🎉\n\n" .
                       "Hey **{$user->first_name}**! 👋\n" .
                       "Admin ne aapke wallet me **+₹{$amtClean}** add kar diye hain!\n\n" .
                       "💵 **Current Wallet Balance:** `₹{$balClean}`\n" .
                       "📝 **Note:** {$description}\n\n" .
                       "Aap is balance se koi bhi Game Key ya Tool instant purchase kar sakte hain! 🚀";

                $keyboard = [
                    'inline_keyboard' => [
                        [ ['text' => '🛍️ Browse Game Keys', 'callback_data' => 'dl_back_menu'] ],
                        [ ['text' => '🏠 Main Menu', 'callback_data' => 'dl_back_menu'] ],
                    ]
                ];

                $controller->sendTelegramApi('sendMessage', [
                    'chat_id' => $user->telegram_id,
                    'text' => $msg,
                    'parse_mode' => 'Markdown',
                    'reply_markup' => $keyboard
                ]);
            } else {
                $msg = "⚠️ **WALLET ADJUSTMENT**\n\n" .
                       "Admin ne aapke wallet se **-₹{$amtClean}** deduct kiye hain.\n\n" .
                       "💵 **Remaining Wallet Balance:** `₹{$balClean}`\n" .
                       "📝 **Reason:** {$description}";

                $controller->sendTelegramApi('sendMessage', [
                    'chat_id' => $user->telegram_id,
                    'text' => $msg,
                    'parse_mode' => 'Markdown'
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send balance adjustment notification: " . $e->getMessage());
        }

        $userName = $user->first_name ?: ($user->username ? '@' . $user->username : 'User #' . $user->id);
        return back()->with('success', "✅ Successfully {$type}ed ₹" . number_format($amount, 2) . " for {$userName}! (New Balance: ₹" . number_format($user->balance, 2) . ")");
    }

    /**
     * Toggle Block/Unblock status for a reseller.
     */
    public function toggleBlock($id)
    {
        $user = TelegramUser::findOrFail($id);
        $user->is_blocked = !$user->is_blocked;
        $user->save();

        $status = $user->is_blocked ? 'blocked' : 'unblocked';
        return back()->with('success', "User {$user->first_name} is now {$status}.");
    }

    /**
     * Promote / Demote user to Reseller role.
     */
    public function toggleReseller($id)
    {
        $user = TelegramUser::findOrFail($id);
        $user->is_reseller = !$user->is_reseller;
        $user->save();

        $role = $user->is_reseller ? 'VIP Reseller' : 'Standard Customer';
        return back()->with('success', "{$user->first_name} is now assigned as {$role}.");
    }

    /**
     * Add a user to Resellers by Telegram ID, Username, or Selection.
     */
    public function addReseller(Request $request)
    {
        $input = trim($request->input('telegram_identifier', ''));
        $cleanInput = ltrim($input, '@');

        $user = TelegramUser::where('telegram_id', $cleanInput)
            ->orWhere('username', $cleanInput)
            ->orWhere('id', $cleanInput)
            ->first();

        if (!$user) {
            $botHandle = env('TELEGRAM_BOT_USERNAME') ? '@' . env('TELEGRAM_BOT_USERNAME') : 'the bot';
            return back()->with('error', "No user found matching '{$input}'. Please ask user to start {$botHandle} first.");
        }

        $user->is_reseller = true;
        $user->save();

        $name = $user->first_name ?: ($user->username ?: 'User #' . $user->id);
        return back()->with('success', "🎉 {$name} is now active as a Reseller! They will automatically receive all standard reseller discounted prices.");
    }

    /**
     * Delete a single Telegram user/reseller.
     */
    public function destroy($id)
    {
        $user = TelegramUser::findOrFail($id);
        $name = $user->first_name ?: ($user->username ?: 'User #' . $user->id);

        DB::transaction(function () use ($user) {
            \App\Models\UserProductPrice::where('telegram_user_id', $user->id)->delete();
            Transaction::where('telegram_user_id', $user->id)->delete();
            \App\Models\Referral::where('referrer_id', $user->id)->orWhere('referred_user_id', $user->id)->delete();
            \App\Models\Spin::where('telegram_user_id', $user->id)->delete();
            \App\Models\StockSubscription::where('telegram_user_id', $user->id)->delete();
            TelegramUser::where('referrer_id', $user->id)->update(['referrer_id' => null]);
            Key::where('telegram_user_id', $user->id)->update(['telegram_user_id' => null]);
            $user->delete();
        });

        return back()->with('success', "User {$name} deleted successfully.");
    }

    /**
     * Bulk delete selected Telegram users/resellers.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'Please select at least one user to delete.');
        }

        $count = 0;
        DB::transaction(function () use ($ids, &$count) {
            \App\Models\UserProductPrice::whereIn('telegram_user_id', $ids)->delete();
            Transaction::whereIn('telegram_user_id', $ids)->delete();
            \App\Models\Referral::whereIn('referrer_id', $ids)->orWhereIn('referred_user_id', $ids)->delete();
            \App\Models\Spin::whereIn('telegram_user_id', $ids)->delete();
            \App\Models\StockSubscription::whereIn('telegram_user_id', $ids)->delete();
            TelegramUser::whereIn('referrer_id', $ids)->update(['referrer_id' => null]);
            Key::whereIn('telegram_user_id', $ids)->update(['telegram_user_id' => null]);
            $count = TelegramUser::whereIn('id', $ids)->delete();
        });

        return back()->with('success', "Successfully deleted {$count} selected user(s).");
    }

    /**
     * Send 1-Click bitAIM+ Activation Notification to customer via Telegram bot.
     */
    public function notifyBitaimActivated(Request $request, $id)
     {
         $user = TelegramUser::findOrFail($id);
         $email = $user->email ?: 'your registered account email';

         $activationMsg = "✅ **Subscription Activated!**\n\n" .
                          "Your bitAIM+ access on `{$email}` is now **ACTIVE**. Happy Gaming! 🚀";

         $buttons = [
             'inline_keyboard' => [
                 [
                     ['text' => '📥 Download bitAIM+', 'callback_data' => 'dl_bitaim'],
                     ['text' => '🏠 Main Menu', 'callback_data' => 'main_menu']
                 ]
             ]
         ];

         $botToken = env('TELEGRAM_BOT_TOKEN');
         if ($botToken && $user->telegram_id) {
             try {
                 Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                     'chat_id' => $user->telegram_id,
                     'text' => $activationMsg,
                     'parse_mode' => 'Markdown',
                     'reply_markup' => json_encode($buttons)
                 ]);
             } catch (\Throwable $e) {}
         }

         $name = $user->first_name ?: ($user->username ? '@' . $user->username : 'Customer #' . $user->id);
         return back()->with('success', "bitAIM+ activation notification sent to {$name} on Telegram!");
     }
}
