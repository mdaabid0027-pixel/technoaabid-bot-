<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TelegramController;
use App\Models\TelegramUser;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TelegramSimulatorController extends Controller
{
    /**
     * Display the Telegram bot simulator interface.
     */
    public function index()
    {
        $users = TelegramUser::orderBy('id', 'desc')->get();
        return view('admin.telegram_simulator', compact('users'));
    }

    /**
     * Send simulated payload to Telegram webhook and capture response.
     */
    public function send(Request $request)
    {
        // Clear previous simulation responses from session
        session()->put('sim_responses', []);

        // Pass the request to the TelegramController's handle method
        $telegramController = app(TelegramController::class);
        $telegramController->handle($request);

        // Retrieve responses captured in session
        $responses = session()->get('sim_responses', []);

        return response()->json([
            'status' => 'success',
            'responses' => $responses
        ]);
    }

    /**
     * Get details of a specific TelegramUser.
     */
    public function getUserDetails($id)
    {
        $user = TelegramUser::withCount('keys')->findOrFail($id);
        
        $totalSpend = Transaction::where('telegram_user_id', $user->id)
            ->where('type', 'debit')
            ->sum('amount');

        // Rank determination logic
        $rank = 'Bronze';
        if ($totalSpend >= 200) {
            $rank = 'Gold';
        } elseif ($totalSpend >= 500) {
            $rank = 'Diamond';
        } elseif ($totalSpend >= 50) {
            $rank = 'Silver';
        }

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'telegram_id' => $user->telegram_id,
                'username' => $user->username ?: 'N/A',
                'first_name' => $user->first_name ?: 'N/A',
                'last_name' => $user->last_name ?: '',
                'balance' => number_format((float)$user->balance, 2),
                'is_blocked' => $user->is_blocked,
                'keys_count' => $user->keys_count,
                'total_spend' => number_format((float)$totalSpend, 2),
                'rank' => $rank
            ]
        ]);
    }
}
